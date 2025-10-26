<?php

class sfBuildspaceReportItemRateAndTotalPageGenerator extends sfBuildspaceReportPageGenerator {

    const TOTAL_BILL_ITEM_PROPERTY = 13;

    public $tendererIds;
    public $tenderers;
    public $tenderersNotListedItem;
    public $pageTitle;
    public $sortingType;
    public $itemIds;
    public $fontSize;
    public $contractorRates;
    public $contractorElementGrandTotals;
    public $headSettings;

    public function __construct($bill, $element, $tendererIds, $itemIds, $sortingType, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo         = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->bill        = $bill;
        $this->billElement = $element;
        $this->project = $bill->root_id == $bill->id ? $bill : ProjectStructureTable::getInstance()->find($bill->root_id);

        $this->itemIds     = $itemIds;
        $this->sortingType = $sortingType;
        $this->pageTitle   = $pageTitle;
        $this->currency    = $this->project->MainInformation->Currency;
        $this->tendererIds = $tendererIds;
        $this->tenderers   = $this->getTenderers();
        $this->tenderersNotListedItem = $this->getTendersNotListedItem();

        $this->descriptionFormat = $descriptionFormat;

        $this->setOrientationAndSize();

        $this->elementsOrder  = $this->getElementOrder();
        $this->printSettings  = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, TRUE);
        $this->fontSize       = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType       = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings   = $this->printSettings['headSettings'];

        $this->contractorRates              = $this->getContractorRates();
        $this->contractorTotals             = $this->getContractorTotals();

        $this->contractorElementGrandTotals = $this->getContractorElementGrandTotals();

        self::setMaxCharactersPerLine();
    }

    public function generatePages()
    {
        $this->billStructure = $billStructure = $this->queryBillStructure();
        $estimationRates       = $this->getRatesAfterMarkup();
        $estimationTotal       = $this->getTotalAfterMarkup();
        $estimationValues = array(
            'rates' => $estimationRates,
            'total' => $estimationTotal
        );
        $lumpSumPercents       = $this->getLumpSumPercent();
        $itemQuantities        = $this->getItemQuantities();
        $itemIncludeStatus     = $this->getItemIncludeStatus();
        $billColumnSettings    = $this->bill->BillColumnSettings->toArray();
        $totalPage = 0;
        $pages                 = array();
        $billElement           = $this->billElement;

        if($billElement instanceof BillElement)
        {
            $stmt = $this->pdo->prepare("SELECT e.id FROM ".BillElementTable::getInstance()->getTableName()." e
                WHERE e.project_structure_id = ".$this->bill->id." AND e.deleted_at IS NULL ORDER BY e.priority");

            $stmt->execute();

            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 1;
            $elementCount = array();

            foreach($elements as $element)
            {
                $elementCount[$element['id']] = $count++;

                unset($element);
            }

            unset($elements);
        }
        else
        {
            $elementCount = 1;
        }

        if(count($billStructure))
        {
            foreach($billStructure as $element)
            {
                if(count($element['items']))
                {
                    $itemPages = array();

                    $elemCount = $billElement instanceof BillElement ? $elementCount[$element['id']] : $elementCount;

                    $elementInfo = array(
                        'description' => $element['description'],
                        'element_count' => $elemCount
                    );

                    $this->generateBillItemPages($element['items'], $billColumnSettings, $elementInfo, 1, array(), $itemPages, $estimationValues, $lumpSumPercents, $itemIncludeStatus, $itemQuantities);

                    $page = array(
                        'description' => $element['description'],
                        'element_count' => $elemCount,
                        'item_pages' => SplFixedArray::fromArray($itemPages)
                    );

                    $totalPage+= count($itemPages);

                    $pages[$element['id']] = $page;

                    if(!$billElement instanceof BillElement)
                        $elementCount++;

                    unset($itemPages, $element);
                }
            }
        }
        else
        {
            $this->generateBillItemPages(array(), $billColumnSettings, null, 1, array(), $itemPages, $estimationValues, $lumpSumPercents, $itemIncludeStatus, $itemQuantities);

            $page = array(
                'description' => "N/a",
                'element_count' => 1,
                'item_pages' => SplFixedArray::fromArray($itemPages)
            );

            $totalPage+= count($itemPages);

            $pages[0] = $page;
        }

        $this->totalPage = $totalPage;

        return $pages;
    }

    /*
     * We use SplFixedArray as data structure to boost performance. Since associative array cannot be used in SplFixedArray, we have to use indexes
     * to get values. Below are indexes and what they represent as their values
     *
     * $row:
     * 0 - id
     * 1 - row index
     * 2 - description
     * 3 - level
     * 4 - type
     * 5 - unit
     * 6 - rate
     * 7 - quantity per unit by bill column settings
     * 8 - include (bill column types)
     */
    public function generateBillItemPages(Array $billItems, Array $billColumnSettings, $elementInfo, $pageCount, $ancestors, &$itemPages, $valuesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, $newPage = false)
    {
        $itemPages[$pageCount] = array();
        $layoutSettings        = $this->printSettings['layoutSetting'];
        $maxRows               = $this->getMaxRows();
        $ancestors = (is_array($ancestors) && count($ancestors)) ? $ancestors : array();

        $ratesAfterMarkup = array_key_exists('rates', $valuesAfterMarkup) ? $valuesAfterMarkup['rates'] : [];
        $totalAfterMarkup = array_key_exists('total', $valuesAfterMarkup) ? $valuesAfterMarkup['total'] : [];

        $blankRow                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $blankRow[self::ROW_BILL_ITEM_ID]           = -1;//id
        $blankRow[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
        $blankRow[self::ROW_BILL_ITEM_DESCRIPTION]  = null;//description
        $blankRow[self::ROW_BILL_ITEM_LEVEL]        = 0;//level
        $blankRow[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK;//type
        $blankRow[self::ROW_BILL_ITEM_UNIT]         = null;//unit
        $blankRow[self::ROW_BILL_ITEM_RATE]         = null;//rate
        $blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//quantity per unit
        $blankRow[self::ROW_BILL_ITEM_INCLUDE]      = null;//include

        //generate TendererNotlistedVal
        $notListedValues = array(0);

        if(count($this->tenderers))
        {
            foreach($this->tenderers as $tenderer)
            {
                $notListedValues[] = 0;
            }
        }

        $estimatedTotalValues = array(0);
        if(count($this->tenderers))
        {
            foreach($this->tenderers as $tenderer)
            {
                $estimatedTotalValues[] = 0;
            }
        }

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
        $rowCount = 1;

        $occupiedRows = Utilities::justify($elementInfo['description'], $this->MAX_CHARACTERS);

        if($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
        {
            $oneLineDesc = $occupiedRows[0];
            $occupiedRows = new SplFixedArray(1);
            $occupiedRows[0] = $oneLineDesc;
        }

        foreach($occupiedRows as $occupiedRow)
        {
            $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
            $row[self::ROW_BILL_ITEM_ID] = -1;//id
            $row[self::ROW_BILL_ITEM_ROW_IDX] = null;//row index
            $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;//description
            $row[self::ROW_BILL_ITEM_LEVEL] = 0;//level
            $row[self::ROW_BILL_ITEM_TYPE] = self::ROW_TYPE_ELEMENT;//type
            $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
            $row[self::ROW_BILL_ITEM_RATE] = null;//rate
            $row[self::ROW_BILL_ITEM_TOTAL] = null;//total
            $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//quantity per unit
            $row[self::ROW_BILL_ITEM_INCLUDE] = null;//include

            array_push($itemPages[$pageCount], $row);

            unset($row);
        }

        //blank row
        array_push($itemPages[$pageCount], $blankRow);

        $rowCount += count($occupiedRows)+1;//plus one blank row

        foreach($ancestors as $k => $row)
        {
            array_push($itemPages[$pageCount], $row);
            $rowCount += 1;
            unset($row);
        }

        $ancestors = array();

        $itemIndex    = 1;
        $counterIndex = 0;//display item's index in BQ

        while (list($x, $billItem) = each($billItems))
        {

            $occupiedRows      = $this->calculateBQItemDescription($billItem);
            $totalOccupiedRows = ($billItem['type'] == BillItem::TYPE_ITEM_PC_RATE) ? (self::PC_RATE_TABLE_SIZE+$occupiedRows->count()) : $occupiedRows->count();
            $availableRows     = ($maxRows - $rowCount);
            $availableRows     = ($availableRows < 0 ) ? 0 : $availableRows;
            $isLastChunk       = true;

            if($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
            {
                $oneLineDesc = $occupiedRows[0];
                $occupiedRows = new SplFixedArray(1);
                $occupiedRows[0] = $oneLineDesc;
            }

            $notListedItem = ($billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED) ? true : false;

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
                    
                    $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $valuesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
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

                    $row[self::ROW_BILL_ITEM_ROW_IDX] = ($key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID) ? $billItem['bill_ref_element_no'].'/'.$billItem['bill_ref_page_no'].' '.$billItem['bill_ref_char'] : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;
                    $row[self::ROW_BILL_ITEM_LEVEL] = $billItem['level'];
                    $row[self::ROW_BILL_ITEM_TYPE] = $billItem['type'];

                    if($isLastChunk && $key+1 == $occupiedRows->count() && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID)
                    {
                        $row[self::ROW_BILL_ITEM_ID] = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT] = $billItem['uom'];

                        if($billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED)
                        {
                            $notListedRates = $notListedValues;

                            $notListedRates[0] =  self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $ratesAfterMarkup) ? $ratesAfterMarkup[$billItem['id']] : 0);

                            $row[self::ROW_BILL_ITEM_RATE] = $notListedRates;


                            $estimatedTotal = $estimatedTotalValues;
                            $estimatedTotal[0] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $totalAfterMarkup) ? $totalAfterMarkup[$billItem['id']] : 0);

                            $row[self::ROW_BILL_ITEM_TOTAL] = $estimatedTotal;
                        }
                        else
                        {
                            $row[self::ROW_BILL_ITEM_RATE] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $ratesAfterMarkup) ? $ratesAfterMarkup[$billItem['id']] : 0);
                            $row[self::ROW_BILL_ITEM_TOTAL] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $totalAfterMarkup) ? $totalAfterMarkup[$billItem['id']] : 0);
                        }

                        $quantityPerUnit = array();
                        $includeStatus = null;

                        foreach($billColumnSettings as $billColumnSetting)
                        {
                            $itemQuantity = array_key_exists($billItem['id'], $itemQuantities[$billColumnSetting['id']]) ? $itemQuantities[$billColumnSetting['id']][$billItem['id']][0] : 0;

                            $quantityPerUnit[$billColumnSetting['id']] = $itemQuantity;

                            $includeStatus[$billColumnSetting['id']] = array_key_exists($billItem['id'], $itemIncludeStatus[$billColumnSetting['id']]) ? $itemIncludeStatus[$billColumnSetting['id']][$billItem['id']] : null;
                        }

                        $row[self::ROW_BILL_ITEM_INCLUDE]      = $includeStatus;
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $quantityPerUnit;
                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID] = null;
                        $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
                        $row[self::ROW_BILL_ITEM_RATE] = null;//rate
                        $row[self::ROW_BILL_ITEM_TOTAL] = null;//total
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//qty per unit
                        $row[self::ROW_BILL_ITEM_INCLUDE] = true;// include

                        if ( $key+1 == $occupiedRows->count() && $billItem['type'] == BillItem::TYPE_NOID )
                        {
                            $row[self::ROW_BILL_ITEM_UNIT] = $billItem['uom'];//unit
                        }
                    }

                    array_push($itemPages[$pageCount], $row);

                    unset($row);
                }

                //blank row
                array_push($itemPages[$pageCount], $blankRow);

                $rowCount++;//plus one blank row;
                $itemIndex++;
                $newPage = false;

                if($notListedItem && count($this->tenderers))
                {
                    $tenderersCount = 1;

                    $newPage = false;

                    foreach($this->tenderers as $tenderer)
                    {
                        if(array_key_exists($tenderer['id'], $this->tenderersNotListedItem) && array_key_exists($billItem['id'], $this->tenderersNotListedItem[$tenderer['id']]))
                        {
                            $item = $this->tenderersNotListedItem[$tenderer['id']][$billItem['id']];

                            $item['description'] = "({$item['tenderer']})  " . $item['description'];

                            $padding = '&nbsp;';
                            $characterToReduce = 1;

                            $occupiedRows  = Utilities::justify($item['description'], $this->MAX_CHARACTERS - $characterToReduce);

                            $rowCount += count($occupiedRows) + 1;

                            $pushToTemp = ($rowCount <= $maxRows) ? false : true;

                            foreach($occupiedRows as $key => $occupiedRow)
                            {
                                $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                                $row[self::ROW_BILL_ITEM_ROW_IDX] = null;
                                $row[self::ROW_BILL_ITEM_DESCRIPTION] = $padding.$occupiedRow;
                                $row[self::ROW_BILL_ITEM_LEVEL] = $billItem['level'];
                                $row[self::ROW_BILL_ITEM_TYPE] = $billItem['type'];

                                if($key+1 == $occupiedRows->count() && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID)
                                {
                                    $notListedRates = $notListedValues;

                                    $estimatedTotal = $estimatedTotalValues;

                                    $row[self::ROW_BILL_ITEM_ID] = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                                    $row[self::ROW_BILL_ITEM_UNIT] = $this->tenderersNotListedItem[$tenderer['id']][$billItem['id']]['uom'] ?? null;

                                    $notListedRates[$tenderersCount] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $this->contractorRates[$tenderer['id']]) ? $this->contractorRates[$tenderer['id']][$billItem['id']] : 0);
                                    $estimatedTotal[$tenderersCount] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $this->contractorTotals[$tenderer['id']]) ? $this->contractorTotals[$tenderer['id']][$billItem['id']] : 0);

                                    $row[self::ROW_BILL_ITEM_RATE] = $notListedRates;
                                    $row[self::ROW_BILL_ITEM_TOTAL] = $estimatedTotal;
                                    $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $item['quantities'];
                                    $row[self::ROW_BILL_ITEM_INCLUDE] = $item['include'];
                                }
                                else
                                {
                                    $row[self::ROW_BILL_ITEM_ID] = null;
                                    $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
                                    $row[self::ROW_BILL_ITEM_RATE] = null;//rate
                                    $row[self::ROW_BILL_ITEM_TOTAL] = null; //total
                                    $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//qty per unit
                                    $row[self::ROW_BILL_ITEM_INCLUDE] = true;// include
                                }

                                if($pushToTemp)
                                {
                                    array_push($ancestors, $row);

                                    $newPage = true;
                                }
                                else
                                {
                                    array_push($itemPages[$pageCount], $row);
                                }

                                unset($row);
                            }

                            if($pushToTemp)
                            {
                                array_push($ancestors, $blankRow);
                            }
                            else
                            {
                                array_push($itemPages[$pageCount], $blankRow);
                            }
                        }
                        $tenderersCount++;
                    }

                    if($newPage)
                    {
                        $pageCount++;
                        unset($billItems[$x], $occupiedRows);

                        $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $valuesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                        break;
                    }

                    unset($notListedItem);
                }

                unset($billItems[$x], $occupiedRows);

                reset($billItems);
            }
            else
            {
                reset($billItems);
                $pageCount++;
                
                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $valuesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                break;
            }
        }
    }

    public function getTotalAfterMarkup()
    {
        $result = array();

        if(count($this->itemIds))
        {
            $stmt = $this->pdo->prepare("SELECT i.id, COALESCE(i.grand_total_after_markup ,0) AS value
              FROM ".BillItemTable::getInstance()->getTableName()." i
              JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
              WHERE e.project_structure_id = ".$this->bill->id." AND i.id IN (".implode(',', $this->itemIds).")
              AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

            foreach($result as $itemId => $value)
            {
                $result[$itemId] = $value[0];
            }
        }

        return $result;
    }

    public function getRatesAfterMarkup()
    {
        return parent::getRatesAfterMarkup();
    }

    public function getContractorTotals()
    {
        $result = array();

        if(count($this->tendererIds) && count($this->itemIds))
        {
            foreach($this->tendererIds as $k => $companyId)
            {
                $stmt = $this->pdo->prepare("SELECT i.id, COALESCE(rate.grand_total, 0) AS value
            FROM ".TenderBillItemRateTable::getInstance()->getTableName()." rate
            LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON rate.bill_item_id = i.id
            LEFT JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.id = rate.tender_company_id
            WHERE i.id IN (".implode(',', $this->itemIds).") AND tc.company_id = ".$companyId." AND i.deleted_at IS NULL ORDER BY i.id");

                $stmt->execute();

                $rates = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

                /* Temporary Solution */
                foreach($rates as $itemId => $rate)
                {
                    $rates[$itemId] = $rate[0];
                }

                $result[$companyId] = $rates;

            }
        }

        return $result;
    }

    public function getContractorRates()
    {
        return parent::getContractorRates();
    }
}
