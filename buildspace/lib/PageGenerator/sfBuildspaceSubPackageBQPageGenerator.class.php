<?php

class sfBuildspaceSubPackageBQPageGenerator extends sfBuildspaceBQPageGenerator
{
    protected $subPackage;

    function __construct(ProjectStructure $bill, $element, $subPackage)
    {
        $this->subPackage = $subPackage;

        parent::__construct($bill, $element);
    }

    public function setNewBillRef($newBillRef = null)
    {
        $this->newBillRef = $newBillRef;
    }

    protected function updateBillReferences()
    {
        //Do nothing
    }

    protected function queryBillStructure()
    {
        $pdo = $this->pdo;
        $bill = $this->bill;
        $subPackage = $this->subPackage;
        $billElement = $this->billElement;
        $billStructure = [];

        $elementSqlPart = $billElement instanceof BillElement ? "AND e.id = ".$billElement->id : null;

        $stmt = $pdo->prepare("SELECT e.id, e.description FROM ".BillElementTable::getInstance()->getTableName()." e
        WHERE e.project_structure_id = ".$bill->id." ".$elementSqlPart." AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($elements as $element)
        {
            $result = [
                'id'          => $element['id'],
                'description' => $element['description'],
                'items'       => []
            ];

            $sqlFieldCond = '(
            CASE 
                WHEN spsori.sub_package_id is not null THEN spsori.sub_package_id
                WHEN spri.sub_package_id is not null THEN spri.sub_package_id
            ELSE
                spbi.sub_package_id
            END
            ) AS sub_package_id';

                $stmtItem = $pdo->prepare("SELECT DISTINCT c.id AS bill_column_setting_id, c.use_original_quantity,
                    i.id AS bill_item_id, rate.final_value AS final_value, $sqlFieldCond
                    FROM ".SubPackageTable::getInstance()->getTableName()." sp
                    LEFT JOIN ".ProjectStructureTable::getInstance()->getTableName()." bill ON bill.root_id = sp.project_structure_id
                    LEFT JOIN ".SubPackageResourceItemTable::getInstance()->getTableName()." AS spri ON spri.sub_package_id = sp.id
                    LEFT JOIN ".SubPackageScheduleOfRateItemTable::getInstance()->getTableName()." AS spsori ON spsori.sub_package_id = sp.id
                    JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = bill.id
                    LEFT JOIN ".BillColumnSettingTable::getInstance()->getTableName()." c ON c.project_structure_id = e.project_structure_id
                    JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id
                    LEFT JOIN ".BillBuildUpRateItemTable::getInstance()->getTableName()." bur ON bur.bill_item_id = i.id AND bur.resource_item_library_id = spri.resource_item_id AND bur.deleted_at IS NULL
                    LEFT JOIN ".ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName()." AS sifc ON sifc.relation_id = spsori.schedule_of_rate_item_id AND sifc.deleted_at IS NULL
                    LEFT JOIN ".BillItemFormulatedColumnTable::getInstance()->getTableName()." AS rate ON rate.relation_id = i.id
                    LEFT JOIN " . SubPackageBillItemTable::getInstance()->getTableName() . " spbi ON spbi.bill_item_id = i.id AND spbi.sub_package_id = sp.id
                    WHERE sp.id =".$subPackage->id." AND sp.deleted_at IS NULL
                    AND bill.id = ".$bill->id." AND bill.deleted_at IS NULL
                    AND e.id = ".$element['id']." AND e.deleted_at IS NULL
                    AND NOT (spri.sub_package_id IS NULL AND spsori.sub_package_id IS NULL AND spbi.sub_package_id is null)
                    AND (rate.relation_id = bur.bill_item_id OR rate.schedule_of_rate_item_formulated_column_id = sifc.id OR rate.relation_id = spbi.bill_item_id)
                    AND rate.column_name = '".BillItem::FORMULATED_COLUMN_RATE."' AND rate.final_value <> 0 AND rate.deleted_at IS NULL
                    AND i.type <> ".BillItem::TYPE_ITEM_NOT_LISTED." AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
                    AND c.deleted_at IS NULL ORDER BY i.id");

            $stmtItem->execute();

            $records = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

            if(count($records))
            {
                $billItemIds = Utilities::arrayValueRecursive('bill_item_id', $records);

                $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id,
                    uom.symbol as uom, uom.id as uom_id, p.grand_total, COALESCE(p.grand_total_after_markup, 0) AS grand_total_after_markup, p.grand_total_quantity, p.level, p.priority,
                    p.lft, p.rgt, p.root_id, p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char
                    FROM ".BillItemTable::getInstance()->getTableName()." c
                    JOIN ".BillItemTable::getInstance()->getTableName()." p ON c.lft BETWEEN p.lft AND p.rgt
                    LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
                    WHERE c.root_id = p.root_id AND c.type != ".BillItem::TYPE_ITEM_NOT_LISTED."
                    AND c.id IN (".implode(',', $billItemIds).")
                    AND c.element_id = ".$element['id']." AND p.element_id = ".$element['id']." AND c.project_revision_deleted_at IS NULL
                    AND c.deleted_at IS NULL AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
                    ORDER BY p.element_id, p.priority, p.lft, p.level ASC");

                $stmt->execute();

                $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $result['items'] = $billItems;

                array_push($billStructure, $result);
            }

            unset($element, $billItems);
        }

        return $billStructure;
    }


    protected function getItemQuantities()
    {
        $implodedItemIds = null;
        $result = [];

        foreach($this->billStructure as $element)
        {
            if(count($element['items']) == 0)
                continue;//we skip element with empty items

            $itemIds = Utilities::arrayValueRecursive('id', $element['items']);

            if(is_array($itemIds))
            {
                $implodedItemIds .= implode(',', $itemIds);
                $implodedItemIds .= ",";
            }

            unset($element, $itemIds);
        }

        $implodedItemIds = rtrim($implodedItemIds, ",");

        if($this->printGrandTotalQty)
        {
            if ( ! empty($implodedItemIds) )
            {
                $stmt = $this->pdo->prepare("SELECT i.id, type.bill_column_setting_id, ifc.final_value AS value 
                    FROM ".BillItemTable::getInstance()->getTableName()." i
                    LEFT JOIN ".BillItemTypeReferenceTable::getInstance()->getTableName()." type ON type.bill_item_id = i.id AND type.deleted_at IS NULL
                    LEFT JOIN ".BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName()." ifc ON ifc.relation_id = type.id AND ifc.deleted_at IS NULL
                    WHERE i.id IN (".$implodedItemIds.") AND ifc.column_name = '".BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT."' AND i.grand_total_quantity <> 0 
                    AND i.deleted_at IS NULL GROUP BY i.id, type.bill_column_setting_id, ifc.final_value ORDER BY i.id, type.bill_column_setting_id ASC");

                $stmt->execute();

                $quantities = $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

                $billColumnSettings = $this->getBillColumnSettings();

                $columnArray = $result = array();

                foreach($billColumnSettings as $key => $column)
                {
                    if(!array_key_exists($column['id'], $columnArray))
                    {
                        $columnArray[$column['id']] = array(
                            'name' => $column['name'],
                            'quantity' => $column['quantity']
                        );
                    }

                    unset($column);
                }

                unset($billColumnSettings);

                foreach($quantities as $itemId => $item)
                {
                    if(count($item))
                    {
                        if(!array_key_exists($itemId, $result))
                        {
                            $result[$itemId] = array();
                        }

                        $totalQty = 0;

                        foreach($item as $k => $quantity)
                        {
                            if(array_key_exists($quantity['bill_column_setting_id'], $columnArray))
                            {
                                $totalQty+= $quantity['value'] * $columnArray[$quantity['bill_column_setting_id']]['quantity'];
                            }

                            unset($quantity);
                        }

                        array_push($result[$itemId], $totalQty);
                    }

                    unset($item);
                }

                unset($quantities);
            }
        }
        else
        {
            foreach($this->bill->BillColumnSettings->toArray() as $column)
            {
                $quantityFieldName = $column['use_original_quantity'] ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                if ( ! empty($implodedItemIds) )
                {
                    $stmt = $this->pdo->prepare("SELECT r.bill_item_id, COALESCE(fc.final_value, 0) AS value FROM ".BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName()." fc
                    JOIN ".BillItemTypeReferenceTable::getInstance()->getTableName()." r ON fc.relation_id = r.id
                    WHERE r.bill_item_id IN (".$implodedItemIds.") AND r.bill_column_setting_id = ".$column['id']."
                    AND r.include IS TRUE AND fc.column_name = '".$quantityFieldName."' AND fc.final_value <> 0
                    AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

                    $stmt->execute();

                    $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

                    $result[$column['id']] = $quantities;

                    unset($quantities);
                }
                else
                {
                    $result[$column['id']] = 0;
                }
            }
        }

        return $result;
    }


    protected function generateBillItemPages(Array $billItems, Array $billColumnSettings, $elementInfo, $pageCount, $ancestors, &$itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, $newPage = false)
    {
        $itemPages[$pageCount] = [];
        $layoutSettings        = $this->printSettings['layoutSetting'];
        $maxRows               = $this->getMaxRows();

        //starts with a blank row
        $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
        $rowCount = 1;

        /*
         * Always display element description at start of every page.
         */
        $descriptionCont = $pageCount > 1 ? $layoutSettings['contdPrefix'] : null;

        $occupiedRows = $this->addElementDescription($elementInfo, $pageCount, $itemPages, $descriptionCont);

        $rowCount += $occupiedRows->count();

        //blank row
        $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
        $rowCount++;

        $itemIndex    = 1;
        $counterIndex = 0;//display item's index in BQ

        while (list($x, $billItem) = each($billItems))
        {
            $ancestors = $billItem['level'] == 0 ? [] : $ancestors;

            if(array_key_exists($billItem['id'], $this->newBillRef))
            {
                $billItems[$x]['bill_ref_char'] = $this->newBillRef[$billItem['id']]['char'];
                $billItems[$x]['bill_ref_element_no'] = $this->newBillRef[$billItem['id']]['elementNo'];
                $billItems[$x]['bill_ref_page_no'] = $this->newBillRef[$billItem['id']]['pageCount'];
            }

            if (($billItem['type'] == BillItem::TYPE_HEADER || $billItem['type'] == BillItem::TYPE_HEADER_N) && ($billItem['rgt'] - $billItem['lft'] > 1 ))
            {
                $row                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
                $row[self::ROW_BILL_ITEM_ID]           = $billItem['id'];//id
                $row[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
                $row[self::ROW_BILL_ITEM_DESCRIPTION]  = $billItem['description'];//description
                $row[self::ROW_BILL_ITEM_LEVEL]        = $billItem['level'];//level
                $row[self::ROW_BILL_ITEM_TYPE]         = $billItem['type'];//type
                $row[self::ROW_BILL_ITEM_UNIT]         = $billItem['lft']; //set lft info (only for ancestor)
                $row[self::ROW_BILL_ITEM_RATE]         = $billItem['rgt']; //set rgt info (only for ancestor)
                $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $billItem['root_id']; //set root_id info (only for )
                $row[self::ROW_BILL_ITEM_INCLUDE]      = null;//include

                // Add item to ancestors.
                $ancestors[$billItem['level']] = $row;

                // Remove all items after current item in ancestors.
                $ancestors = array_splice($ancestors, 0, $billItem['level']+1);

                unset($row);
            }

            /*
             * To get all ancestors from previous page so we can display it as continued headers
             * before we print out the item
             */
            if($pageCount > 1 and $itemIndex == 1 and $billItem['level'] != 0 )
            {
                // if detected current looped item is same level with ancestor, then overwrite it
                $this->unsetHeadThatIsSameLevelWithItemOnNextPage($ancestors, $billItem);

                foreach($ancestors as $ancestor)
                {
                    if ( $ancestor[self::ROW_BILL_ITEM_ID] == $billItem['id'] )
                    {
                        continue;
                    }

                    $occupiedRows = $this->generateAncestorOccupiedRows($ancestor, $pageCount, $layoutSettings);
                    $availableRows = $maxRows - $rowCount;

                    if(($occupiedRows->count()+4) > $availableRows)
                    {
                        /*
                         * For ancestor we need to spare 4 rows that is to consider
                         * a. (at least) 1 line of the child's description
                         * b. 1 line for bottom ellipsis (if it's a truncated description)
                         * c. 1 line for top ellipsis (if it's a continued descriptoin from previous page)
                         * d. 1 line for blank row
                         * 
                         * This needs to be considered since we will reprint ancestors as parents for all items
                         * underneath it and given the rules that headers description cannot be truncated
                         */
                        throw new PageGeneratorException(PageGeneratorException::ERROR_INSUFFICIENT_ROW, [
                            'id'             => $billItem['id'],
                            'page_number'    => $pageCount,
                            'page_items'     => $itemPages[$pageCount], 
                            'rows_available' => $availableRows,
                            'max_rows'       => $maxRows,
                            'occupied_rows'  => $occupiedRows
                        ]);
                    }

                    foreach($occupiedRows as $occupiedRow)
                    {
                        $this->addRowToItemPage($itemPages[$pageCount], $ancestor[self::ROW_BILL_ITEM_ID], null, $occupiedRow, $ancestor[self::ROW_BILL_ITEM_LEVEL], $ancestor[self::ROW_BILL_ITEM_TYPE]);
                        $rowCount++;
                    }

                    //blank row
                    $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
                    $rowCount++;

                    unset($occupiedRow, $occupiedRows, $ancestor);
                }
            }

            if($billItem['type'] == BillItem::TYPE_HEADER_N and !$newPage)
            {
                $occupiedRows = null;
                unset($occupiedRows);

                reset($billItems);
                $pageCount++;

                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                break;
            }
            
            $occupiedRows      = $this->calculateBQItemDescription($billItem);
            $totalOccupiedRows = ($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE) ? (self::PC_RATE_TABLE_SIZE+$occupiedRows->count()) : $occupiedRows->count();
            $availableRows     = ($maxRows - $rowCount);
            $availableRows     = ($availableRows < 0 ) ? 0 : $availableRows;
            $isLastChunk       = true;

            if($totalOccupiedRows > $availableRows)
            {
                /*
                 * If item description cannot fit into page we have to determine either to truncate the description or move the item to the next page.
                 * Item will be truncated if it is the ONLY item in the page (the whole description is too long to fit into a page) else we just
                 * push the item to the next page.
                 * 
                 */
                if($itemIndex > 1)
                {
                    $occupiedRows = null;
                    unset($occupiedRows);

                    reset($billItems);
                    $pageCount++;

                    $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                    break;
                }
                else
                {
                    try
                    {
                        list($availableRows, $isLastChunk) = $this->breakDownItemDescription($billItems, $billItem, $occupiedRows, $availableRows);
                        $totalOccupiedRows = ($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE && $isLastChunk) ? (self::PC_RATE_TABLE_SIZE+$occupiedRows->count()) : $occupiedRows->count();
                    }
                    catch(PageGeneratorException $e)
                    {
                        throw new PageGeneratorException($e->getMessage(), [
                            'id'             => $billItem['id'],
                            'page_number'    => $pageCount,
                            'page_items'     => $itemPages[$pageCount], 
                            'rows_available' => $availableRows,
                            'max_rows'       => $maxRows,
                            'occupied_rows'  => $occupiedRows
                        ]);
                    }
                }
            }

            if($isLastChunk && ($billItem['isContinuedDescription'] ?? false))
            {
                $billItem['isContinuingDescription'] = false;
                $this->addEllipses($billItem, $occupiedRows);
                
                $totalOccupiedRows = ($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE && $isLastChunk) ? (self::PC_RATE_TABLE_SIZE+$occupiedRows->count()) : $occupiedRows->count();
                
                if($totalOccupiedRows > $availableRows)
                {
                    throw new PageGeneratorException(PageGeneratorException::ERROR_INSUFFICIENT_ROW, [
                        'id'             => $billItem['id'],
                        'page_number'    => $pageCount,
                        'page_items'     => $itemPages[$pageCount], 
                        'rows_available' => $availableRows,
                        'max_rows'       => $maxRows,
                        'occupied_rows'  => $occupiedRows
                    ]);
                }
            }

            $primeCostRateRows = null;
            if($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE && $isLastChunk)
            {
                if($totalOccupiedRows > $availableRows)
                {
                    throw new PageGeneratorException(PageGeneratorException::ERROR_PC_RATE_INSUFFICIENT_ROW, [
                        'id'             => $billItem['id'],
                        'page_number'    => $pageCount,
                        'page_items'     => $itemPages[$pageCount], 
                        'rows_available' => $availableRows,
                        'max_rows'       => $maxRows,
                        'occupied_rows'  => $occupiedRows
                    ]);
                }

                $primeCostRateRows = $this->generatePrimeCostRateRows($billItem['id']);
            }

            reset($billItems);
            $x = key($billItems);//reset current $billItems iteration key to a latest key from the truncated item*/

            if($availableRows >= $totalOccupiedRows) // If can fit in remaining space of current page.
            {
                $rowCount += $occupiedRows->count();

                foreach($occupiedRows as $key => $occupiedRow)
                {
                    if($key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID)
                    {
                        $counterIndex++;
                    }

                    $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                    $row[self::ROW_BILL_ITEM_ROW_IDX] = ($key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID) ? $counterIndex : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = (!empty($occupiedRow) && !in_array($billItem['type'], [BillItem::TYPE_ITEM_HTML_EDITOR, BillItem::TYPE_NOID]) ) ? Utilities::inlineJustify($occupiedRow, $this->MAX_CHARACTERS) : $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEVEL] = $billItem['level'];
                    $row[self::ROW_BILL_ITEM_TYPE] = $billItem['type'];

                    //Generate Bill Ref
                    if($key+1 == $occupiedRows->count())
                    {
                        $this->generateBillReference($billItem, $counterIndex, $pageCount);
                    }

                    if($isLastChunk && $key+1 == $occupiedRows->count())
                    {
                        $row[self::ROW_BILL_ITEM_ID]   = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT] = $billItem['uom'];
                        $row[self::ROW_BILL_ITEM_RATE] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $ratesAfterMarkup) ? $ratesAfterMarkup[$billItem['id']] : 0);

                        $quantityPerUnit = [];
                        $includeStatus = null;

                        if($this->printGrandTotalQty)
                        {
                            /*
                             * this is actually not a quantity per unit but grand total quantity instead. But we just assign it to the same variable name so it can be used
                             * for case where print grand total qty is disabled.
                             */
                            $quantityPerUnit = array_key_exists($billItem['id'], $itemQuantities) ? $itemQuantities[$billItem['id']][0] : 0;

                            $row[self::ROW_BILL_ITEM_INCLUDE] = $includeStatus;

                            if ( $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT)
                            {
                                $quantityPerUnit = array_key_exists($billItem['id'], $lumpSumPercents) ? $lumpSumPercents[$billItem['id']][0] : 0;
                            }

                            if($billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM or $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT or $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE)
                            {
                                $row[self::ROW_BILL_ITEM_RATE] = array_key_exists('grand_total_after_markup', $billItem) ? self::gridCurrencyRoundingFormat($billItem['grand_total_after_markup']) : 0;
                            }
                        }
                        else
                        {
                            foreach($billColumnSettings as $billColumnSetting)
                            {
                                $itemQuantity = array_key_exists($billItem['id'], $itemQuantities[$billColumnSetting['id']]) ? $itemQuantities[$billColumnSetting['id']][$billItem['id']][0] : 0;

                                $quantityPerUnit[$billColumnSetting['id']] = $itemQuantity;

                                $includeStatus[$billColumnSetting['id']] = array_key_exists($billItem['id'], $itemIncludeStatus[$billColumnSetting['id']]) ? $itemIncludeStatus[$billColumnSetting['id']][$billItem['id']] : true;
                                
                                if ( $billItem['type'] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT)
                                {
                                    $quantityPerUnit[$billColumnSetting['id']] = array_key_exists($billItem['id'], $lumpSumPercents) ? $lumpSumPercents[$billItem['id']][0] : 0;
                                }
                            }

                            $row[self::ROW_BILL_ITEM_INCLUDE] = $includeStatus;
                        }

                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $quantityPerUnit;
                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID] = null;
                        $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
                        $row[self::ROW_BILL_ITEM_RATE] = null;//rate
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//qty per unit
                        $row[self::ROW_BILL_ITEM_INCLUDE] = true;// include
                    }

                    $this->addRowToItemPage(
                        $itemPages[$pageCount],
                        $row[self::ROW_BILL_ITEM_ID],
                        $row[self::ROW_BILL_ITEM_ROW_IDX],
                        $row[self::ROW_BILL_ITEM_DESCRIPTION],
                        $row[self::ROW_BILL_ITEM_LEVEL],
                        $row[self::ROW_BILL_ITEM_TYPE],
                        $row[self::ROW_BILL_ITEM_UNIT],
                        $row[self::ROW_BILL_ITEM_RATE],
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT],
                        $row[self::ROW_BILL_ITEM_INCLUDE]
                    );

                    unset($row);
                }

                if($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE && $primeCostRateRows)
                {
                    foreach($primeCostRateRows as $primeCostRateRow)
                    {
                        $this->addRowToItemPage(
                            $itemPages[$pageCount],
                            $primeCostRateRow[self::ROW_BILL_ITEM_ID],
                            $primeCostRateRow[self::ROW_BILL_ITEM_ROW_IDX],
                            $primeCostRateRow[self::ROW_BILL_ITEM_DESCRIPTION],
                            $primeCostRateRow[self::ROW_BILL_ITEM_LEVEL],
                            $primeCostRateRow[self::ROW_BILL_ITEM_TYPE],
                            $primeCostRateRow[self::ROW_BILL_ITEM_UNIT],
                            $primeCostRateRow[self::ROW_BILL_ITEM_RATE],
                            $primeCostRateRow[self::ROW_BILL_ITEM_QTY_PER_UNIT],
                            $primeCostRateRow[self::ROW_BILL_ITEM_INCLUDE]
                        );

                        $rowCount++;
                    }
                }

                //blank row
                $this->addBlankRowToItemPage($itemPages[ $pageCount ]);
                $rowCount++;

                $itemIndex++;
                $newPage = false;

                unset($billItems[$x], $occupiedRows);

                reset($billItems);
            }
            else
            {
                reset($billItems);
                $pageCount++;

                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                break;
            }
        }
    }

    public function getPrintSetting()
    {
        if(!$this->subPackage->SubPackageBillLayoutSetting->id)
        {
            $printSettingId = SubPackageBillLayoutSettingTable::cloneExistingPrintingLayoutSettingsForSubPackage($this->subPackage->id);
        }
        else
        {
            $printSettingId = $this->subPackage->SubPackageBillLayoutSetting->id;
        }

        return SubPackageBillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($printSettingId, TRUE);
    }

    public function getBillColumnSettingCount()
    {
        return count(SubPackageTypeReferenceTable::getBySubPackageId($this->subPackage->id, $this->bill->id));
    }

    public function getBillColumnSettings()
    {
        return SubPackageTypeReferenceTable::getBySubPackageId($this->subPackage->id, $this->bill->id);
    }
    
    public function calculateBQItemDescription(Array $billItem)
    {
        $billRef                 = $this->generateBillRefString($billItem, $this->bill->BillLayoutSetting->page_no_prefix);
        $descriptionBillRef      = (strlen($billRef)) ? '<b>('.$billRef.') - </b>' : '';
        $billItem['description'] = $descriptionBillRef.$billItem['description'];

        return parent::calculateBQItemDescription($billItem);
    }

    protected function getRatesAfterMarkup()
    {
        $billItemRates = SubPackageTable::getBillItemRates($this->subPackage->ProjectStructure, $this->subPackage);

        $totalCostByBillItems = [];

        if(array_key_exists($this->subPackage->id, $billItemRates))
        {
            foreach($billItemRates[$this->subPackage->id] as $resourceId => $byBills)
            {
                if(array_key_exists($this->bill->id, $byBills))
                {
                    foreach($byBills[$this->bill->id] as $itemId => $data)
                    {
                        if(!array_key_exists($itemId, $totalCostByBillItems))
                        {
                            $totalCostByBillItems[$itemId] = 0;
                        }

                        $totalCostByBillItems[$itemId] += $data['total_cost_after_conversion'];
                    }
                }

                unset($billItemRates[$this->subPackage->id][$resourceId]);
            }
        }

        $billItemRates = SubPackageTable::getNoBuildUpBillItems($this->subPackage->ProjectStructure, $this->subPackage);

        if(array_key_exists($this->subPackage->id, $billItemRates))
        {
            foreach($billItemRates[$this->subPackage->id] as $billId => $items)
            {
                foreach($items as $billItemId => $item)
                {
                    if(!array_key_exists($billItemId, $totalCostByBillItems))
                    {
                        $totalCostByBillItems[$billItemId] = 0;
                    }

                    $totalCostByBillItems[$billItemId] += $item['rate'];
                }
            }
        }

        return $totalCostByBillItems;
    }
}