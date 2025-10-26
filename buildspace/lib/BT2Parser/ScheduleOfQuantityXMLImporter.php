<?php

/**
 * @property string tableName
 * @property string tblClass
 * @property mixed  itemList
 * @property mixed  currentHeadId
 * @property mixed  defaultTradeId
 * @property mixed  units
 * @property mixed  dimensions
 * @property int    currentTradeId
 * @property mixed  itemPriority
 * @property mixed  tradeLevelPriority
 */
class ScheduleOfQuantityXMLImporter {

    /**
     * @var array
     */
    protected $savedTradeIds = array();

    /**
     * @var array
     */
    protected $insertedItemIds = array();

    /**
     * @var array
     */
    protected $hierachyStructure = array();

    /**
     * @var array
     */
    protected $affectedItemIds = array();

    private $parser;

    /**
     * @var ScheduleOfQuantity
     */
    private $scheduleOfQuantity;

    /**
     * @var PDO
     */
    private $pdo;

    private $units = array();

    private $dimensions = array();

    /**
     * Build Up Item Constant
     */
    const BUILD_UP_PROPERTY_COUNT = 14;
    const BUILD_UP_SOURCE_ITEM_ID = 0;
    const BUILD_UP_DESCRIPTION    = 1;
    const BUILD_UP_DEPTH          = 2;
    const BUILD_UP_DEPTH_FORMULA  = 3;
    const BUILD_UP_FACTOR         = 4;
    const BUILD_UP_FACTOR_FORMULA = 5;
    const BUILD_UP_LENGTH         = 6;
    const BUILD_UP_LENGTH_FORMULA = 7;
    const BUILD_UP_QUANTITY       = 8;
    const BUILD_UP_TOTAL          = 9;
    const BUILD_UP_WIDTH          = 10;
    const BUILD_UP_WIDTH_FORMULA  = 11;
    const BUILD_UP_PRIORITY       = 12;
    const BUILD_UP_SIGN           = 13;

    /**
     * @param Doctrine_Connection          $conn
     * @param ScheduleOfQuantity           $scheduleOfQuantity
     * @param ScheduleOfQuantityUnitGetter $unitGetter
     * @param SOQParser                    $parser
     * @param string                       $type
     */
    public function __construct(Doctrine_Connection $conn, ScheduleOfQuantity $scheduleOfQuantity, ScheduleOfQuantityUnitGetter $unitGetter, SOQParser $parser, $type = 'CUBIT')
    {
        $this->parser             = $parser;
        $this->scheduleOfQuantity = $scheduleOfQuantity;
        $this->conn               = $conn;
        $this->pdo                = $conn->getDbh();
        $this->importType         = $this->determineImportType($type);
        $this->unitGetter         = $unitGetter;
    }

    /**
     * Import Data into Database
     */
    public function importDataIntoDb()
    {
        $tradeIds = $this->importScheduleOfQuantityTrades();

        $this->dimensions = $this->unitGetter->getDimensions();

        $this->importMissingUnitOfMeasurement();

        $this->units = $this->unitGetter->getAvailableUnitOfMeasurements();

        $itemIds = $this->importScheduleOfQuantityItems($tradeIds);

        $this->importQuantityByItemIds($itemIds);
    }

    private function importQuantityByItemIds(Array $itemIds)
    {
        $updatedRecordIds = array();

        if(!empty($itemIds))
        {
            $itemsFromImportFile = array();

            foreach($this->parser->getItemList() as $key => $item)
            {
                if(array_key_exists($key, $itemIds))
                {
                    $itemsFromImportFile[$itemIds[$key]] = $item;
                }
            }

            if(!empty($itemsFromImportFile))
            {
                $stmt = $this->pdo->prepare("SELECT i.third_party_identifier, i.description FROM ".ScheduleOfQuantityItemTable::getInstance()->getTableName()." AS i
                WHERE i.id IN(".implode(', ', array_keys($itemsFromImportFile)).")
                AND i.type = ".ScheduleOfQuantityItem::TYPE_WORK_ITEM." AND i.deleted_at IS NULL
                AND NOT EXISTS
                ( SELECT fc.id
                    FROM ".ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getTableName()." AS fc
                    WHERE fc.relation_id = i.id AND fc.deleted_at IS NULL
                )");

                $stmt->execute();

                $newItemIds = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $rowsSQL = array();
                $toBind = array();

                $c = 0;

                $itemsQtyToUpdate = array();

                foreach($itemsFromImportFile as $item)
                {
                    if(array_key_exists($item['sourceId'], $newItemIds))
                    {
                        $params = array();

                        $params[] = ":relation_id".$c;
                        $toBind[":relation_id".$c] = $itemIds[$item['sourceId']];

                        $params[] = ":column_name".$c;
                        $toBind[":column_name".$c] = ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY;

                        $params[] = ":has_build_up".$c;
                        $toBind[":has_build_up".$c] = array_key_exists($item['sourceId'], $this->parser->getBuildUpItemList()) ? 'TRUE' : 'FALSE';

                        $params[] = ":value".$c;
                        $toBind[":value".$c] = (isset($item['qty']) && is_numeric($item['qty'])) ? number_format($item['qty'], 5, '.', '') : 0;

                        $params[] = ":final_value".$c;
                        $toBind[":final_value".$c] = (isset($item['qty']) && is_numeric($item['qty'])) ? number_format($item['qty'], 5, '.', '') : 0;

                        $params[] = ":created_at".$c;
                        $toBind[":created_at".$c] = 'NOW()';

                        $params[] = ":updated_at".$c;
                        $toBind[":updated_at".$c] = 'NOW()';

                        $rowsSQL[] = "(" . implode(", ", $params) . ")";

                        $c++;
                    }
                    else if(isset($item['isItem']))
                    {
                        $itemsQtyToUpdate[$item['sourceId']] = $item;
                    }
                }

                if(!empty($rowsSQL))
                {
                    $stmt = $this->pdo->prepare('INSERT INTO ' . ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getTableName() . '
                    (relation_id, column_name, has_build_up, value, final_value, created_at, updated_at)
                    VALUES ' . implode(', ', $rowsSQL));

                    foreach($toBind as $param => $val)
                    {
                        $stmt->bindValue($param, $val);
                    }

                    $stmt->execute();
                }

                $updatedRecordIds = $this->updateScheduleOfQuantityItems($itemsQtyToUpdate, $itemIds);

                $updatedRecordIds += $this->insertBuildUpQuantityItems($itemIds);

                $updatedRecordIds += $this->updateScheduleOfQuantityItemFormulatedColumnLinkedValues($updatedRecordIds);
            }
        }

        //delete any old build up items that are linked to SOQ items with no build up (updated)
        $stmt = $this->pdo->prepare("DELETE FROM " . ScheduleOfQuantityBuildUpItemTable::getInstance()->getTableName() . " AS bi
        USING " . ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getTableName() . " AS fc
        WHERE bi.schedule_of_quantity_item_id = fc.relation_id AND fc.column_name = '".ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY."'
        AND fc.has_build_up IS FALSE");

        $stmt->execute();


        if(!empty($updatedRecordIds))
        {
            $stmt = $this->pdo->prepare("SELECT relation_id FROM ".ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getTableName()."
            WHERE id IN (".implode(',', array_keys($updatedRecordIds)).") AND deleted_at IS NULL");

            $stmt->execute();

            $updatedScheduleOfQuantityIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach($updatedScheduleOfQuantityIds as $updatedScheduleOfQuantityId)
            {
                ScheduleOfQuantityItemTable::updateLinkedValues($updatedScheduleOfQuantityId, $this->pdo);
            }
        }
    }

    private function updateScheduleOfQuantityItemFormulatedColumnLinkedValues(Array $records)
    {
        if(empty($records))
            return array();

        $stmt = $this->pdo->prepare("WITH RECURSIVE transitive_closure(node_to, node_from, column_name, distance, path_string) AS
            ( SELECT node_to, node_from, column_name, 1 AS distance, node_to || '.' || node_from || '.' AS path_string FROM ".ScheduleOfQuantityItemEdgeTable::getInstance()->getTableName()."
            WHERE node_to IN (".implode(',', array_keys($records)).") AND column_name = '".ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY."' AND deleted_at IS NULL
            UNION ALL
            SELECT tc.node_to, e.node_from, tc.column_name, tc.distance + 1, tc.path_string || e.node_from || '.' AS path_string
            FROM ".ScheduleOfQuantityItemEdgeTable::getInstance()->getTableName()." AS e JOIN transitive_closure AS tc ON e.node_to = tc.node_from
            WHERE tc.path_string NOT LIKE '%' || e.node_from || '.%' AND e.deleted_at IS NULL)

            SELECT * FROM transitive_closure
            ORDER BY node_to, node_from, distance;");

        $stmt->execute();

        $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formulatedColumnsToUpdate = array();

        foreach($nodes as $node)
        {
            $formulatedColumnsToUpdate[] = $node['node_from'];
        }

        $returnedIds = array();

        if(!empty($formulatedColumnsToUpdate))
        {
            $stmt = $this->pdo->prepare("SELECT relation_id, value
                    FROM ".ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getTableName()."
                    WHERE id IN (".implode(',', $formulatedColumnsToUpdate).") AND deleted_at IS NULL");

            $stmt->execute();

            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $pattern = '/r[\d{1,}]+/i';

            $referencedItemIds = array();

            foreach($records as $record)
            {
                $match = preg_match_all($pattern, $record['value'], $matches, PREG_PATTERN_ORDER);

                if($match && is_array($matches[0]))
                {
                    foreach($matches[0] as $k => $reference)
                    {
                        $itemId = str_ireplace('r', '', $reference);

                        $referencedItemIds[] = $itemId;
                    }
                }
            }

            if(!empty($referencedItemIds))
            {
                $evaluator = new EvalMath(true, true);
                $evaluator->suppress_errors = true;

                foreach($records as $record)
                {
                    $mathExp = $record['value'];

                    $match = preg_match_all($pattern, $record['value'], $matches, PREG_PATTERN_ORDER);

                    if($match && is_array($matches[0]))
                    {
                        foreach($matches[0] as $k => $reference)
                        {
                            $itemId = str_ireplace('r', '', $reference);

                            $stmt = $this->pdo->prepare("SELECT COALESCE(final_value, 0)
                            FROM ".ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getTableName()."
                            WHERE relation_id = ".$itemId." AND column_name = '".ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY."'
                            AND deleted_at IS NULL");

                            $stmt->execute();
                            $finalValue = $stmt->fetch(PDO::FETCH_COLUMN, 0);

                            $mathExp = str_replace($reference, $finalValue, $mathExp);
                        }
                    }

                    $evaluatedValue = $evaluator->evaluate($mathExp);

                    $returnedIds +=$this->updateScheduleOfQuantityFormulatedColumnQuantity(array("({$record['relation_id']}, FALSE, '{$record['value']}', {$evaluatedValue}, NOW())"));
                }
            }
        }

        return $returnedIds;
    }

    private function updateScheduleOfQuantityItems(Array $itemsToUpdate, Array $existingItemIds)
    {
        $recordsToUpdate = array();
        $updatedRecordIds = array();

        foreach($itemsToUpdate as $sourceId => $item)
        {
            if(array_key_exists($sourceId, $existingItemIds))
            {
                /* we set has_build_up to false first to handle issue where the existing SOQ item build is removed from this import.
                 * It will be set to true in insertBuildUpQuantityItems() if there is any build up.
                 */
                $recordsToUpdate[$existingItemIds[$sourceId]] = "({$existingItemIds[$sourceId]}, FALSE, '{$item['qty']}', {$item['qty']}, NOW())";
            }
        }

        if(!empty($recordsToUpdate))
        {
            $updatedRecordIds = $this->updateScheduleOfQuantityFormulatedColumnQuantity($recordsToUpdate);
        }

        return $updatedRecordIds;
    }

    private function insertBuildUpQuantityItems(Array $scheduleOfQuantityItemIds)
    {
        $scheduleOfQuantityItemIdsWithBuildUp = array();
        $updatedRecordIds = array();

        $rowsSQL = array();
        $formulatedColumnRowsSQL = array();

        $toBind = array();
        $formulatedColumnToBind = array();

        $priority = 0;
        $idx = 0;

        $formulatedColumns = array();

        $pdoBindChunks = array();
        $pdoBindChunksCount = 0;

        foreach ($this->parser->getBuildUpItemList() as $key => $items)
        {
            if(array_key_exists($key, $scheduleOfQuantityItemIds))
            {
                if(!array_key_exists($key, $scheduleOfQuantityItemIdsWithBuildUp))
                {
                    $scheduleOfQuantityItemIdsWithBuildUp[$key] = $scheduleOfQuantityItemIds[$key];

                    $priority = 0;
                }

                foreach($items as $item)
                {
                    $params = array();

                    $params[] = ":schedule_of_quantity_item_id".$idx;
                    $toBind[":schedule_of_quantity_item_id".$idx] = $scheduleOfQuantityItemIds[$key];

                    $params[] = ":description".$idx;
                    $toBind[":description".$idx] = empty($item['Description']) ? "" : $item['Description'];

                    $params[] = ":total".$idx;
                    $toBind[":total".$idx] = empty($item['Total']) ? 0 : number_format($item['Total'], 5, '.', '');

                    $params[] = ":priority".$idx;
                    $toBind[":priority".$idx] = $priority;

                    $params[] = ":sign".$idx;
                    $toBind[":sign".$idx] = ( $item['IsDeduction'] == 'false' ) ? ScheduleOfQuantityBuildUpItem::SIGN_POSITIVE : ScheduleOfQuantityBuildUpItem::SIGN_NEGATIVE;

                    $params[] = ":can_edit".$idx;
                    $toBind[":can_edit".$idx] = 'FALSE';

                    $params[] = ":created_at".$idx;
                    $toBind[":created_at".$idx] = 'NOW()';

                    $params[] = ":updated_at".$idx;
                    $toBind[":updated_at".$idx] = 'NOW()';

                    $rowsSQL[] = "(" . implode(", ", $params) . ")";

                    $pdoBindChunks[$pdoBindChunksCount]['toBind'] = $toBind;
                    $pdoBindChunks[$pdoBindChunksCount]['rowsSQL'] = $rowsSQL;

                    if(count($toBind) > 60000)
                    {
                        $toBind = array();
                        $rowsSQL = array();
                        $pdoBindChunksCount++;
                    }

                    if(!array_key_exists($scheduleOfQuantityItemIds[$key], $formulatedColumns))
                    {
                        $formulatedColumns[$scheduleOfQuantityItemIds[$key]] = array();
                    }

                    if(array_key_exists('Factor', $item) && !empty($item['Factor']))
                    {
                        $factorFinalValue = number_format($item['Factor'], 5, '.', '');

                        // insert factor
                        $formulatedColumns[$scheduleOfQuantityItemIds[$key]][$priority][] = array(
                            'value'       => $factorFinalValue,
                            'final_value' => $factorFinalValue,
                            'column_name' => ScheduleOfQuantityBuildUpItem::FORMULATED_COLUMN_FACTOR
                        );
                    }

                    foreach($this->dimensions as $dimensionName => $dimensionId)
                    {
                        if(array_key_exists($dimensionName, $item) && is_numeric($item[$dimensionName]) && !empty($item[$dimensionName]))
                        {
                            $formulatedColumns[$scheduleOfQuantityItemIds[$key]][$priority][] = array(
                                'value'       => is_numeric($item[$dimensionName]) ? number_format($item[$dimensionName], 5, '.', '') : 0,
                                'final_value' => is_numeric($item[$dimensionName]) ? number_format($item[$dimensionName], 5, '.', '') : 0,
                                'column_name' => $dimensionId. '-dimension_column'
                            );
                        }
                    }

                    $priority++;
                    $idx++;
                }
            }
        }

        unset($toBind, $rowsSQL);

        if(!empty($scheduleOfQuantityItemIdsWithBuildUp))
        {
            $stmt = $this->pdo->prepare("DELETE FROM " . ScheduleOfQuantityBuildUpItemTable::getInstance()->getTableName() . "
		    WHERE schedule_of_quantity_item_id IN (".implode(',', $scheduleOfQuantityItemIdsWithBuildUp).") AND can_edit IS FALSE");

            $stmt->execute();

            if(!empty($pdoBindChunks))
            {
                $buildUpItemIds = array();

                foreach($pdoBindChunks as $pdoBindChunk)
                {
                    $stmt = $this->pdo->prepare("WITH inserted AS (
                    INSERT INTO ". ScheduleOfQuantityBuildUpItemTable::getInstance()->getTableName() ." 
                    (schedule_of_quantity_item_id, description, total, priority, sign, can_edit, created_at, updated_at)
                    VALUES ". implode(', ', $pdoBindChunk['rowsSQL'])." RETURNING id, schedule_of_quantity_item_id, priority
                    )
                    SELECT CAST(schedule_of_quantity_item_id AS text) || '_' ||CAST(priority AS text) AS key, id
                    FROM inserted
                    ORDER BY schedule_of_quantity_item_id, priority ASC;");

                    foreach($pdoBindChunk['toBind'] as $param => $val)
                    {
                        $stmt->bindValue($param, $val);
                    }

                    $stmt->execute();

                    $buildUpItemIds += $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                }

                $idx = 0;
                $pdoBindChunks = array();
                $pdoBindChunksCount = 0;

                foreach($buildUpItemIds as $key => $buildUpItemId)
                {
                    $pieces = explode("_", $key);

                    if(count($pieces) == 2 && array_key_exists($pieces[0], $formulatedColumns) && array_key_exists($pieces[1], $formulatedColumns[$pieces[0]]))
                    {
                        foreach($formulatedColumns[$pieces[0]][$pieces[1]] as $formulatedColumn)
                        {
                            $formulatedColumnParams = array();

                            $formulatedColumnParams[] = ":relation_id".$idx;
                            $formulatedColumnToBind[":relation_id".$idx] = $buildUpItemId;

                            $formulatedColumnParams[] = ":value".$idx;
                            $formulatedColumnToBind[":value".$idx] = $formulatedColumn['value'];

                            $formulatedColumnParams[] = ":final_value".$idx;
                            $formulatedColumnToBind[":final_value".$idx] = $formulatedColumn['final_value'];

                            $formulatedColumnParams[] = ":column_name".$idx;
                            $formulatedColumnToBind[":column_name".$idx] = $formulatedColumn['column_name'];

                            $formulatedColumnParams[] = ":created_at".$idx;
                            $formulatedColumnToBind[":created_at".$idx] = 'NOW()';

                            $formulatedColumnParams[] = ":updated_at".$idx;
                            $formulatedColumnToBind[":updated_at".$idx] = 'NOW()';

                            $formulatedColumnRowsSQL[] = "(" . implode(", ", $formulatedColumnParams) . ")";

                            $pdoBindChunks[$pdoBindChunksCount]['toBind'] = $formulatedColumnToBind;
                            $pdoBindChunks[$pdoBindChunksCount]['rowsSQL'] = $formulatedColumnRowsSQL;

                            if(count($formulatedColumnToBind) > 60000)
                            {
                                $formulatedColumnToBind = array();
                                $formulatedColumnRowsSQL = array();
                                $pdoBindChunksCount++;
                            }

                            $idx++;
                        }
                    }
                }

                unset($formulatedColumnToBind, $formulatedColumnRowsSQL);

                if(!empty($pdoBindChunks))
                {
                    foreach($pdoBindChunks as $pdoBindChunk)
                    {
                        $stmt = $this->pdo->prepare('INSERT INTO ' . ScheduleOfQuantityBuildUpFormulatedColumnTable::getInstance()->getTableName() . '
                        (relation_id, value, final_value, column_name, created_at, updated_at)
                        VALUES ' . implode(', ', $pdoBindChunk['rowsSQL']));

                        foreach($pdoBindChunk['toBind'] as $param => $val)
                        {
                            $stmt->bindValue($param, $val);
                        }

                        $stmt->execute();
                    }

                    unset($pdoBindChunks);

                    /* update SOQ items quantity value since SOQ items with build up can have manual entry build up. The SOQ item quantity might vary from
                     * the imported file since it can have manual build up.
                     */

                    $stmt = $this->pdo->prepare("SELECT b.schedule_of_quantity_item_id, SUM(b.total) AS total
                    FROM ".ScheduleOfQuantityBuildUpItemTable::getInstance()->getTableName()." b
                    WHERE b.schedule_of_quantity_item_id IN (".implode(',', $scheduleOfQuantityItemIdsWithBuildUp).") AND b.total <> 0 AND b.deleted_at IS NULL
                    GROUP BY b.schedule_of_quantity_item_id");

                    $stmt->execute();

                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $scheduleOfQuantityItemsTotalQty = array();
                    $recordsToUpdate = array();

                    foreach($result as $buildUpItemTotal)
                    {
                        if(!array_key_exists($buildUpItemTotal['schedule_of_quantity_item_id'], $scheduleOfQuantityItemsTotalQty))
                        {
                            $scheduleOfQuantityItemsTotalQty[$buildUpItemTotal['schedule_of_quantity_item_id']] = 0;
                            $recordsToUpdate[$buildUpItemTotal['schedule_of_quantity_item_id']] = "";
                        }

                        $scheduleOfQuantityItemsTotalQty[$buildUpItemTotal['schedule_of_quantity_item_id']] += $buildUpItemTotal['total'];

                        $recordsToUpdate[$buildUpItemTotal['schedule_of_quantity_item_id']] = "({$buildUpItemTotal['schedule_of_quantity_item_id']}, TRUE, '{$scheduleOfQuantityItemsTotalQty[$buildUpItemTotal['schedule_of_quantity_item_id']]}', {$scheduleOfQuantityItemsTotalQty[$buildUpItemTotal['schedule_of_quantity_item_id']]}, NOW())";
                    }

                    if(!empty($recordsToUpdate))
                    {
                        $updatedRecordIds = $this->updateScheduleOfQuantityFormulatedColumnQuantity($recordsToUpdate);
                    }
                }
            }
        }

        return $updatedRecordIds;
    }

    private function updateScheduleOfQuantityFormulatedColumnQuantity(Array $records)
    {
        $stmt = $this->pdo->prepare("UPDATE " . ScheduleOfQuantityItemFormulatedColumnTable::getInstance()->getTableName() . " SET
        has_build_up = newValues.has_build_up2, value = newValues.value2, final_value = newValues.final_value2, updated_at = newValues.updated_at2
        FROM (VALUES " . implode(', ', $records) . ") AS newValues (relation_id2, has_build_up2, value2, final_value2, updated_at2)
        WHERE relation_id = newValues.relation_id2 AND column_name = '" . ScheduleOfQuantityItem::FORMULATED_COLUMN_QUANTITY . "'
        AND (final_value <> newValues.final_value2 OR value <> newValues.value2 OR has_build_up <> newValues.has_build_up2)
        AND deleted_at IS NULL RETURNING id, final_value");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function importScheduleOfQuantityItems(Array $tradeIds)
    {
        if(empty($tradeIds))
            return array();

        $stmt = $this->pdo->prepare("SELECT i.third_party_identifier, i.id, i.description, i.type, i.lft, i.rgt, i.priority, i.level, i.uom_id, u.symbol, i.schedule_of_quantity_trade_id
        FROM ".ScheduleOfQuantityItemTable::getInstance()->getTableName()." AS i
        LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." AS u ON i.uom_id = u.id AND u.deleted_at IS NULL
        WHERE i.schedule_of_quantity_trade_id IN (".implode(',', $tradeIds).") AND i.deleted_at IS NULL
        ORDER BY i.schedule_of_quantity_trade_id, i.priority, i.lft, i.level");

        $stmt->execute();

        $existingItemIds = array();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $existingItems = array();

        foreach($records as $record)
        {
            $existingItems[$record['third_party_identifier']] = array(
                'description' => $record['description'],
                'level' => $record['level'],
                'isItem' => ($record['type'] == ScheduleOfQuantityItem::TYPE_WORK_ITEM),
                'sourceId' => $record['third_party_identifier'],
                'unit' => (!empty($record['uom_id'])) ? $record['symbol'] : "NULL",
                'trade_id' => $record['schedule_of_quantity_trade_id']
            );

            $existingItemIds[$record['third_party_identifier']] = $record['id'];
        }

        foreach($this->parser->getItemList() as $sourceId => $item)
        {
            $itemsList[$sourceId] = $item;

            if(array_key_exists($sourceId, $existingItems))
            {
                unset($existingItems[$sourceId]);
                continue;
            }

            $prevItem = $item;
            foreach($existingItems as $key => $existingItem)
            {
                if(array_key_exists($key, $this->parser->getItemList()))
                {
                    break;
                }

                $itemsList[$key] = $existingItem;

                if($existingItem['isItem'] && $tradeIds[$item['trade_id']] == $existingItem['trade_id'])
                {
                    if(isset($prevItem['isItem']) and $prevItem['isItem'])
                        $itemsList[$key]['level'] = $prevItem['level'];
                    else
                        $itemsList[$key]['level'] = $prevItem['level'] + 1;
                }

                $prevItem = $existingItem;


                unset($existingItems[$key]);
            }
        }

        $flattenStructure = array();

        if(!empty($itemsList))
        {
            $tree = $this->parser->generateTreeModel($itemsList);

            $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($tree));

            $count = 0;

            foreach ($iterator as $key => $value)
            {
                if(strtolower($key) == 'description')
                {
                    $count++;
                    $flattenStructure[$count] = array();
                }

                $flattenStructure[$count][$key] = $value;
            }
        }

        $rowsSQL = array();
        $toBind = array();

        $rootIdToUpdate = array();
        $itemsToUpdate = array();

        foreach($flattenStructure as $idx => $itemRow)
        {
            if(array_key_exists($itemRow['sourceId'], $existingItemIds))
            {
                $description = pg_escape_string($itemRow['description']);
                $type = (isset($itemRow['isItem']) && $itemRow['isItem']) ? ScheduleOfQuantityItem::TYPE_WORK_ITEM : ScheduleOfQuantityItem::TYPE_HEADER;
                $uomId = (isset($itemRow['unit']) && isset ( $this->units[strtolower($itemRow['unit'])] )) ? $this->units[strtolower($itemRow['unit'])] : "NULL";

                $itemsToUpdate[] = "({$existingItemIds[$itemRow['sourceId']]}, '{$description}', {$type}, {$uomId}, {$itemRow['priority']}, {$itemRow['lft']}, {$itemRow['rgt']}, {$itemRow['level']})";
            }

            if(!array_key_exists($itemRow['sourceId'], $existingItemIds))
            {
                $params = array();

                $params[] = ":third_party_identifier".$idx;
                $toBind[":third_party_identifier".$idx] = $itemRow['sourceId'];

                $params[] = ":identifier_type".$idx;
                $toBind[":identifier_type".$idx] = $this->importType;

                $params[] = ":description".$idx;
                $toBind[":description".$idx] = $itemRow['description'];

                $params[] = ":type".$idx;
                $toBind[":type".$idx] = (isset($itemRow['isItem']) && $itemRow['isItem']) ? ScheduleOfQuantityItem::TYPE_WORK_ITEM : ScheduleOfQuantityItem::TYPE_HEADER;

                $params[] = ":schedule_of_quantity_trade_id".$idx;
                $toBind[":schedule_of_quantity_trade_id".$idx] = $tradeIds[$itemRow['trade_id']];

                $params[] = ":uom_id".$idx;
                $toBind[":uom_id".$idx] = (isset($itemRow['unit']) && isset ( $this->units[strtolower($itemRow['unit'])] )) ? $this->units[strtolower($itemRow['unit'])] : NULL;

                $params[] = ":priority".$idx;
                $toBind[":priority".$idx] = $itemRow['priority'];

                $params[] = ":lft".$idx;
                $toBind[":lft".$idx] = $itemRow['lft'];

                $params[] = ":rgt".$idx;
                $toBind[":rgt".$idx] = $itemRow['rgt'];

                $params[] = ":level".$idx;
                $toBind[":level".$idx] = $itemRow['level'];

                $params[] = ":created_at".$idx;
                $toBind[":created_at".$idx] = 'NOW()';

                $params[] = ":updated_at".$idx;
                $toBind[":updated_at".$idx] = 'NOW()';

                $rowsSQL[] = "(" . implode(", ", $params) . ")";
            }

            $rootIdToUpdate[$itemRow['sourceId']] = $itemRow['root_id'];
        }

        if(!empty($rowsSQL))
        {
            $stmt     = $this->pdo->prepare('INSERT INTO ' . ScheduleOfQuantityItemTable::getInstance()->getTableName() . '
            (third_party_identifier, identifier_type, description, type, schedule_of_quantity_trade_id, uom_id, priority, lft, rgt, level, created_at, updated_at)
            VALUES ' . implode(', ', $rowsSQL).' RETURNING third_party_identifier, id');

            foreach($toBind as $param => $val)
            {
                $stmt->bindValue($param, $val);
            }

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $existingItemIds += $result;
        }

        if(!empty($itemsToUpdate))
        {
            $stmt = $this->pdo->prepare("UPDATE " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " SET
            description= updatedValues.description, type = updatedValues.type, uom_id = cast(nullif(updatedValues.uom_id, NULL) AS int),
            priority = updatedValues.priority, lft = updatedValues.lft, rgt = updatedValues.rgt, level = updatedValues.level, updated_at = NOW()
            FROM (VALUES " . implode(', ', $itemsToUpdate) . ") AS updatedValues (item_id, description, type, uom_id, priority, lft, rgt, level)
            WHERE id = updatedValues.item_id AND deleted_at IS NULL");

            $stmt->execute();
        }

        if(!empty($existingItemIds) && !empty($rootIdToUpdate))
        {
            foreach($rootIdToUpdate as $sourceId => $rootId)
            {
                $rootIdToUpdate[$sourceId] = "({$existingItemIds[$sourceId]}, {$existingItemIds[$rootId]})";
            }

            $stmt = $this->pdo->prepare("UPDATE " . ScheduleOfQuantityItemTable::getInstance()->getTableName() . " SET
            root_id = newValues.saved_root_id, updated_at = NOW() FROM (VALUES " . implode(', ', $rootIdToUpdate) . ") AS newValues (saved_id, saved_root_id)
            WHERE id = newValues.saved_id AND deleted_at IS NULL");

            $stmt->execute();
        }

        return $existingItemIds;
    }

    private function importScheduleOfQuantityTrades()
    {
        $stmt     = $this->pdo->prepare("SELECT third_party_identifier, id, description FROM ".ScheduleOfQuantityTradeTable::getInstance()->getTableName()."
        WHERE schedule_of_quantity_id = ".$this->scheduleOfQuantity->id." AND deleted_at IS NULL ORDER BY priority");

        $stmt->execute();

        $existingTrades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tradeIds = array();

        $trades = array();

        foreach($existingTrades as $existingTrade)
        {
            $tradeIds[$existingTrade['third_party_identifier']] = $existingTrade['id'];
            $trades[$existingTrade['third_party_identifier']] = array(
                'description' => $existingTrade['description']
            );
        }

        $rowsSQL = array();
        $toBind = array();

        $priority = $this->scheduleOfQuantity->getScheduleOfQuantityTrades()->count();

        $tradesToUpdate = array();

        $idx = 0;
        foreach($this->parser->getTradeList() as $key => $tradeRow)
        {
            if(array_key_exists($key, $tradeIds) && ($trades[$key]['description'] != $tradeRow['description']))
            {
                $escaped = pg_escape_string($tradeRow['description']);

                $tradesToUpdate[] = "({$tradeIds[$key]}, '{$escaped}')";
            }

            if(!array_key_exists($key, $tradeIds))
            {
                $params = array();

                $params[] = ":schedule_of_quantity_id".$idx;
                $toBind[":schedule_of_quantity_id".$idx] = $this->scheduleOfQuantity->id;

                $params[] = ":identifier_type".$idx;
                $toBind[":identifier_type".$idx] = $this->importType;

                $params[] = ":third_party_identifier".$idx;
                $toBind[":third_party_identifier".$idx] = $key;

                $params[] = ":description".$idx;
                $toBind[":description".$idx] = $tradeRow['description'];

                $params[] = ":priority".$idx;
                $toBind[":priority".$idx] = $priority;

                $params[] = ":created_at".$idx;
                $toBind[":created_at".$idx] = 'NOW()';

                $params[] = ":updated_at".$idx;
                $toBind[":updated_at".$idx] = 'NOW()';

                $rowsSQL[] = "(" . implode(", ", $params) . ")";

                $priority++;
            }

            $idx++;
        }

        if(!empty($rowsSQL))
        {
            $stmt     = $this->pdo->prepare('INSERT INTO ' . ScheduleOfQuantityTradeTable::getInstance()->getTableName() . '
            (schedule_of_quantity_id, identifier_type, third_party_identifier, description, priority, created_at, updated_at)
            VALUES ' . implode(', ', $rowsSQL).' RETURNING third_party_identifier, id');

            foreach($toBind as $param => $val)
            {
                $stmt->bindValue($param, $val);
            }

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $tradeIds += $result;
        }

        if(!empty($tradesToUpdate))
        {
            $stmt = $this->pdo->prepare("UPDATE " . ScheduleOfQuantityTradeTable::getInstance()->getTableName() . " SET
            description= newValues.description, updated_at = NOW() FROM (VALUES " . implode(', ', $tradesToUpdate) . ") AS newValues (trade_id, description)
            WHERE id = newValues.trade_id AND deleted_at IS NULL");

            $stmt->execute();
        }

        return $tradeIds;
    }

    /**
     * Determine which import type will be used.
     *
     * @param $type
     * @throws UnexpectedValueException
     * @return int|null
     */
    private function determineImportType($type)
    {
        $importType = NULL;

        switch($type)
        {
            case ScheduleOfQuantity::IDENTIFIER_TYPE_CUBIT_TEXT:
                $importType = ScheduleOfQuantity::IDENTIFIER_TYPE_CUBIT;
                break;

            default:
                throw new UnexpectedValueException('Invalid Import Type !');
                break;
        }

        return $importType;
    }

    /**
     * Import missing unit of measurement from imported file into BuildSpace
     */
    private function importMissingUnitOfMeasurement()
    {
        $dimensionsPriorities = array(
            'Length' => 0,
            'Width'  => 1,
            'Depth'  => 2,
        );

        $this->unitGetter->getImportedUnits($this->parser->getItemList());

        if ( count($units = $this->unitGetter->getImportedUnits($this->parser->getItemList())) == 0 )
        {
            return;
        }

        if ( count($existingUnits = $this->unitGetter->getExistingUnitsByUnitsSymbol($units)) == 0 )
        {
            return;
        }

        foreach ( $units as $unitName => $unit )
        {
            if ( in_array($unit, $existingUnits) )
            {
                continue;
            }

            $uom          = new UnitOfMeasurement();
            $uom->name    = $unitName;
            $uom->symbol  = $unitName;
            $uom->type    = UnitOfMeasurement::UNIT_TYPE_METRIC;
            $uom->display = TRUE;
            $uom->save($this->conn);

            foreach ( $dimensionsPriorities as $dimensionName => $dimensionPriority )
            {
                if ( ! isset($this->dimensions[$dimensionName]) )
                {
                    continue;
                }

                $unitDimension                         = new UnitOfMeasurementDimensions();
                $unitDimension->unit_of_measurement_id = $uom->id;
                $unitDimension->dimension_id           = $this->dimensions[$dimensionName];
                $unitDimension->priority               = $dimensionPriority;
                $unitDimension->save($this->conn);
            }

            $existingUnits[strtolower($uom->symbol)] = strtolower($uom->symbol);

            unset($unit);
        }

        unset($existingUnits, $availableUnits, $units);
    }
}