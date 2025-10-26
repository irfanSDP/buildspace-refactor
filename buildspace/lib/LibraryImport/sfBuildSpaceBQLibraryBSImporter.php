<?php

/**
 * @property mixed availableUnits
 * @property mixed dimensions
 */
class sfBuildSpaceBQLibraryBSImporter
{

    /**
     * @var BQLibrary
     */
    private $bqLibrary;

    /**
     * @var sfBuildspaceXMLParser
     */
    private $parser;

    /**
     * @var Doctrine_Connection
     */
    private $con;

    public function __construct(BQLibrary $bqLibrary, SimpleXMLElement $parser, Doctrine_Connection $con)
    {
        $this->bqLibrary  = $bqLibrary;
        $this->parser     = $parser;
        $this->con        = $con;
        $this->pdo        = $con->getDbh();
        $this->bqElements = $parser->ELEMENTS;
        $this->bqItems    = $parser->ITEMS;

        // get Last Priority for current Element
        $lastPriority = BQElement::getMaxPriorityByLibraryId($bqLibrary->id);

        $this->priority = $lastPriority + 1;
    }

    public function import()
    {
        $userId = $this->getCurrentUserId();

        $unitGetter = new ScheduleOfQuantityUnitGetter($this->con);
        $stmt       = new sfImportExcelStatementGenerator();

        $this->availableUnits = $unitGetter->getAvailableUnitOfMeasurements();
        $this->dimensions     = $unitGetter->getDimensions();

        $importedElementToElementIds = $this->saveElementLevel($stmt, $userId);

        $xmlItems = $this->bqItems->children();

        $this->checkUnitOfMeasurement($xmlItems);

        unset( $bqItems );

        // Process Root Items
        list(
            $originalItemIdsToRootId, $originalItemsToSave, $importedItemToItemIds,
            $rootOriginalIdsToPriority, $ratesToSave
            ) = $this->parseBQItems($xmlItems, $importedElementToElementIds, $userId);

        unset( $xmlItems, $billItems );

        $this->saveImportedItemsByBatch($originalItemsToSave, $originalItemIdsToRootId, $importedItemToItemIds,
            $rootOriginalIdsToPriority);

        $this->saveImportedRates($ratesToSave);

        unset( $originalItemsToSave );
    }

    /**
     * @param sfImportExcelStatementGenerator $stmt
     * @param                                 $userId
     * @return mixed
     */
    private function saveElementLevel(sfImportExcelStatementGenerator $stmt, $userId)
    {
        $elements = array();

        $stmt->createInsert(
            BQElementTable::getInstance()->getTableName(),
            array( 'description', 'library_id', 'priority', 'created_at', 'updated_at', 'created_by', 'updated_by' )
        );

        foreach ($this->bqElements->children() as $importedElement)
        {
            $elementId   = (int) $importedElement->id;
            $description = html_entity_decode((string) $importedElement->description);

            $stmt->addRecord(array(
                $description,
                $this->bqLibrary->id,
                $this->priority,
                'NOW()',
                'NOW()',
                $userId,
                $userId
            ), $elementId);

            $elements[] = array(
                'id'          => $elementId,
                'description' => $description,
                'relation_id' => $this->bqLibrary->id,
                '_csrf_token' => '',
                'updated_at'  => date('d/m/Y H:i'),
                'original_id' => $elementId,
            );

            $this->priority ++;

            unset( $importedElement );
        }

        unset( $this->bqElements );

        $stmt->save();

        $importedElementToElementIds = $stmt->returningIds;

        //reassign elementId
        foreach ($elements as $k => $element)
        {
            $elements[$k]['id'] = $importedElementToElementIds[$element['id']];

            unset( $element );
        }

        return $importedElementToElementIds;
    }

    /**
     * @param SimpleXMLElement $xmlItems
     */
    private function checkUnitOfMeasurement(SimpleXMLElement $xmlItems)
    {
        $newUnits = array();

        // will loop to get new unit(s) if available
        foreach ($xmlItems as $importedItem)
        {
            $uomSymbol = (string) $importedItem->uom_symbol;

            $this->checkEmptyUOMSymbol($uomSymbol, $newUnits);
        }

        $this->insertMissingUnit($newUnits);

        unset( $newUnits );
    }

    /**
     * @param SimpleXMLElement $xmlItems
     * @param                  $importedElementToElementIds
     * @param                  $userId
     * @return array
     */
    private function parseBQItems(SimpleXMLElement $xmlItems, $importedElementToElementIds, $userId)
    {
        $importedItemToItemIds     = array();
        $rootOriginalIdsToPriority = array();
        $originalItemsToSave       = array();
        $originalItemIdsToRootId   = array();
        $ratesToSave               = array();
        $currentPriority           = - 1;
        $currentElementId          = null;

        foreach ($xmlItems as $importedItem)
        {
            $asRoot         = null;
            $rate           = null;
            $elementId      = $importedElementToElementIds[(string) $importedItem->element_id];
            $description    = html_entity_decode((string) $importedItem->description);
            $uomSymbol      = ( (string) $importedItem->uom_symbol ) ? (string) $importedItem->uom_symbol : null;
            $type           = (string) $importedItem->type;
            $level          = (string) $importedItem->level;
            $originalItemId = (string) $importedItem->id;
            $lft            = (string) $importedItem->lft;
            $rgt            = (string) $importedItem->rgt;
            $originalRootId = null;

            if ((int) $importedItem->level != 0)
            {
                $originalRootId                           = (string) $importedItem->root_id;
                $originalItemIdsToRootId[$originalItemId] = $originalRootId;
            }
            else
            {
                if ($elementId != $currentElementId)
                {
                    $currentPriority  = 0;
                    $currentElementId = $elementId;
                }
                else
                {
                    $currentPriority ++;
                }

                //Set As Root and set root Id to null
                $asRoot                                     = true;
                $rootOriginalIdsToPriority[$originalItemId] = $priority = $currentPriority;
            }

            if (!is_null($importedItem->RATES) AND count($importedItem->RATES) > 0)
            {
                $rate = (string) $importedItem->RATES->final_value;
                $rate = ( $rate ) ? number_format($rate, 2, '.', '') : null;
            }

            if (!$asRoot)
            {
                if (!is_null($rate))
                {
                    $item              = new BQItem();
                    $item->description = $description;
                    $item->uom_id      = ( isset ( $this->availableUnits[strtolower($uomSymbol)] ) ) ? $this->availableUnits[strtolower($uomSymbol)] : null;
                    $item->priority    = $priority;
                    $item->element_id  = $elementId;
                    $item->type        = $type;
                    $item->root_id     = $importedItemToItemIds[$originalRootId];
                    $item->lft         = $lft;
                    $item->rgt         = $rgt;
                    $item->level       = $level;

                    $item->save($this->con);

                    $itemId = $item->id;

                    $item->free(true);

                    unset( $item );
                }
                else
                {
                    $originalItemsToSave[$originalItemId] = array(
                        $elementId,
                        $description,
                        $type,
                        $uomSymbol,
                        $level,
                        $originalRootId,
                        $lft,
                        $rgt,
                        $priority,
                        'NOW()',
                        'NOW()',
                        $userId,
                        $userId
                    );
                }
            }
            else
            {
                $item              = new BQItem();
                $item->description = $description;
                $item->priority    = $priority;
                $item->element_id  = $elementId;
                $item->type        = $type;

                $item->save($this->con);

                $item->root_id = $item->id;
                $item->lft     = $lft;
                $item->rgt     = $rgt;
                $item->level   = 0;

                $item->save($this->con);

                $importedItemToItemIds[(string) $importedItem->id] = $itemId = $item->id;

                $item->free(true);

                unset( $item );
            }

            // Save Rate if Not Header
            if ((string) $importedItem->type != BQItem::TYPE_HEADER AND !is_null($rate))
            {
                $ratesToSave[] = array(
                    $itemId,
                    BQItem::FORMULATED_COLUMN_RATE,
                    $rate,
                    $rate,
                    'NOW()',
                    'NOW()',
                    $userId,
                    $userId
                );
            }

            unset( $importedItem );
        }

        return array(
            $originalItemIdsToRootId,
            $originalItemsToSave,
            $importedItemToItemIds,
            $rootOriginalIdsToPriority,
            $ratesToSave
        );
    }

    /**
     * @param $originalItemIdsToRootId
     * @param $itemRootId
     * @return mixed
     */
    public function checkRootId(&$originalItemIdsToRootId, $itemRootId)
    {
        if (array_key_exists($itemRootId, $originalItemIdsToRootId))
        {
            $originalRootId = $originalItemIdsToRootId[$itemRootId];

            return $originalRootId = $this->checkRootId($originalItemIdsToRootId, $originalRootId);
        }

        return $itemRootId;
    }

    /**
     * @param array $newUnits
     */
    private function insertMissingUnit(array $newUnits)
    {
        // insert new unit if available
        foreach ($newUnits as $unitName => $unit)
        {
            if (isset( $this->availableUnits[$unit] ))
            {
                continue;
            }

            $uom          = new UnitOfMeasurement();
            $uom->name    = $unitName;
            $uom->symbol  = $unitName;
            $uom->type    = UnitOfMeasurement::UNIT_TYPE_METRIC;
            $uom->display = true;
            $uom->save($this->con);

            $this->insertDimensionForNewUnit($uom);

            $this->availableUnits[strtolower($uom->symbol)] = $uom->id;

            unset( $unit );
        }
    }

    /**
     * @param $uomSymbol
     * @param $newUnits
     */
    private function checkEmptyUOMSymbol($uomSymbol, &$newUnits)
    {
        if (!empty( $uomSymbol ))
        {
            $newUnits[$uomSymbol] = strtolower($uomSymbol);
        }
    }

    /**
     * Get Current User Information
     *
     * @return mixed
     */
    private function getCurrentUserId()
    {
        return sfContext::getInstance()->getUser()->getAttribute('user_id', null, 'sfGuardSecurityUser');
    }

    /**
     * Insert pre-defined dimensions for newly created Unit Of Measurement
     *
     * @param \UnitOfMeasurement $uom
     */
    private function insertDimensionForNewUnit(UnitOfMeasurement $uom)
    {
        $dimensionsPriorities = array(
            'Length' => 0,
            'Width'  => 1,
            'Depth'  => 2,
        );

        foreach ($dimensionsPriorities as $dimensionName => $dimensionPriority)
        {
            if (!isset( $this->dimensions[$dimensionName] ))
            {
                continue;
            }

            $unitDimension                         = new UnitOfMeasurementDimensions();
            $unitDimension->unit_of_measurement_id = $uom->id;
            $unitDimension->dimension_id           = $this->dimensions[$dimensionName];
            $unitDimension->priority               = $dimensionPriority;
            $unitDimension->save($this->con);
        }
    }

    /**
     * @param array $item
     * @return array
     */
    private function getCurrentExistingUnitOfMeasurementId(array $item)
    {
        if (isset ( $this->availableUnits[strtolower($item[3])] ))
        {
            $item[3] = $this->availableUnits[strtolower($item[3])];
        }
        else
        {
            $item[3] = 'NULL';
        }

        return $item;
    }

    /**
     * @param array $ratesToSave
     * @return bool
     */
    private function saveImportedRates(array $ratesToSave)
    {
        if (count($ratesToSave) == 0)
        {
            return false;
        }

        $ratesToSaveNewArray = array();

        foreach ($ratesToSave as $rate)
        {
            $columnName = pg_escape_string($rate[1]);

            $ratesToSaveNewArray[] = "({$rate[0]}, '{$columnName}', {$rate[2]}, {$rate[3]}, {$rate[4]}, {$rate[5]}, {$rate[6]}, {$rate[7]})";

            unset( $rate );
        }

        $stmt = $this->pdo->prepare('INSERT INTO ' . BQItemFormulatedColumnTable::getInstance()->getTableName() . ' (relation_id, column_name, value, final_value, created_at, updated_at, created_by, updated_by) VALUES ' . implode(', ',
                $ratesToSaveNewArray));
        $stmt->execute();

        return true;
    }

    /**
     * @param array $originalItemsToSave
     * @param array $originalItemIdsToRootId
     * @param array $importedItemToItemIds
     * @param array $rootOriginalIdsToPriority
     * @return bool
     */
    private function saveImportedItemsByBatch(
        array $originalItemsToSave,
        array $originalItemIdsToRootId,
        array $importedItemToItemIds,
        array $rootOriginalIdsToPriority
    ) {
        if (count($originalItemsToSave) == 0)
        {
            return false;
        }

        $originalItemsToSaveNewArray = array();

        foreach ($originalItemsToSave as $item)
        {
            $item = $this->getCurrentExistingUnitOfMeasurementId($item);

            $originalRootId = $this->checkRootId($originalItemIdsToRootId, $item[5]);

            if($originalRootId && array_key_exists($originalRootId, $importedItemToItemIds) && array_key_exists($originalRootId, $rootOriginalIdsToPriority))
            {
                $rootId         = $importedItemToItemIds[$originalRootId];
                $priority       = $rootOriginalIdsToPriority[$originalRootId];
                $item[5]        = $rootId;
                $item[8]        = $priority;

                $description = pg_escape_string($item[1]);

                $originalItemsToSaveNewArray[] = "($item[0], '{$description}', $item[2], $item[3], $item[4], $item[5], $item[6], $item[7], $item[8], $item[9], $item[10])";
            }

            unset( $item );
        }

        if(!empty($originalItemsToSaveNewArray))
        {
            $stmt = $this->pdo->prepare('INSERT INTO ' . BQItemTable::getInstance()->getTableName() . ' (element_id, description, type, uom_id, level, root_id, lft, rgt, priority, created_at, updated_at) VALUES ' . implode(', ',
                    $originalItemsToSaveNewArray));
            $stmt->execute();

            unset( $originalItemsToSaveNewArray );
        }

        return true;
    }

} 