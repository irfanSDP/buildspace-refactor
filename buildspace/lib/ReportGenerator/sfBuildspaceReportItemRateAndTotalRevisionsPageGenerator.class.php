<?php

class sfBuildspaceReportItemRateAndTotalRevisionsPageGenerator extends sfBuildspaceReportItemRateAndTotalPageGenerator {

    const TOTAL_BILL_ITEM_PROPERTY      = 14;
    const ROW_BILL_ITEM_ID              = 0;
    const ROW_BILL_ITEM_ROW_IDX         = 1;
    const ROW_BILL_ITEM_DESCRIPTION     = 2;
    const ROW_BILL_ITEM_LEVEL           = 3;
    const ROW_BILL_ITEM_TYPE            = 4;
    const ROW_BILL_ITEM_UNIT            = 5;
    const ROW_BILL_ITEM_RATE            = 6;
    const ROW_BILL_ITEM_QTY_PER_UNIT    = 7;
    const ROW_BILL_ITEM_INCLUDE         = 8;
    const ROW_BILL_ITEM_TOTAL           = 12;
    const ROW_BILL_ITEM_DELETED         = 13;

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
    private $projectRevisions;
    private $billItemsWithDeleted;
    private $billStructureWithoutDeleted;

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

        $project = Doctrine_Core::getTable('ProjectStructure')->find($bill->root_id);

        $this->projectRevisions = ProjectRevisionTable::getRevisions($project);

        $billItemsWithDeleted = array();
        foreach(BillItemTable::getAllBillItemsIncludingDeleted($itemIds, $bill) as $elementId => $billItems)
        {
            $billItemsWithDeleted = array_merge($billItemsWithDeleted, $billItems);
        }
        $this->billItemsWithDeleted = $billItemsWithDeleted;

        self::setMaxCharactersPerLine();
    }

    public function generatePages()
    {
        $this->billStructure = $billStructure = $this->queryBillStructure();
        $billStructureWithoutDeleted = array();
        foreach($this->queryBillStructureWithoutDeletedItems() as $element)
        {
            foreach($element['items'] as $item)
            {
                $billStructureWithoutDeleted[] = $item;
            }
        }
        $this->billStructureWithoutDeleted = $billStructureWithoutDeleted;

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
        else    // There are no items.
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

    public function generateBillItemPages(Array $billItems, Array $billColumnSettings, $elementInfo, $pageCount, $ancestors, &$itemPages, $valuesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, $newPage = false)
    {
        $itemPages[$pageCount] = array();
        $maxRows               = $this->getMaxRows();
        $ancestors = (is_array($ancestors) && count($ancestors)) ? $ancestors : array();

        $ratesAfterMarkup = $valuesAfterMarkup['rates'];
        $totalAfterMarkup = $valuesAfterMarkup['total'];

        $deletedItemIds = ProjectRevisionTable::getDeletedItemIds($this->billItemsWithDeleted, $this->billStructureWithoutDeleted);

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

        self::addBlankRow($itemPages[$pageCount]); //starts with a blank row
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
            self::addRow($itemPages[$pageCount], -1, null, $occupiedRow, 0, self::ROW_TYPE_ELEMENT, null, null, null, null, null);
            $rowCount++;
        }

        self::addBlankRow($itemPages[$pageCount]);
        $rowCount++;

        foreach($ancestors as $k => $row)
        {
            array_push($itemPages[$pageCount], $row);
            $rowCount += 1;
            unset($row);
        }

        $ancestors = array();

        $itemIndex    = 1;
        $counterIndex = 0;//display item's index in BQ

        foreach($billItems as $x => $billItem)
        {
            $occupiedRows = ( $billItems[ $x ]['type'] == BillItem::TYPE_ITEM_HTML_EDITOR or $billItems[ $x ]['type'] == BillItem::TYPE_NOID ) ? Utilities::justifyHtmlString($billItems[ $x ]['description'], $this->MAX_CHARACTERS) : Utilities::justify($billItems[ $x ]['description'], $this->MAX_CHARACTERS);

            if( $this->descriptionFormat == self::DESC_FORMAT_ONE_LINE )
            {
                $oneLineDesc = $occupiedRows[0];
                $occupiedRows = new SplFixedArray(1);
                $occupiedRows[0] = $oneLineDesc;
            }

            $notListedItem = ( $billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED ) ? true : false;

            $rowCount += count($occupiedRows);

            if( $rowCount <= $maxRows )
            {
                foreach($occupiedRows as $key => $occupiedRow)
                {
                    if( $key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID )
                    {
                        $counterIndex++;
                    }

                    $rowIdx = ( $key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID ) ? $billItem['bill_ref_element_no'] . '/' . $billItem['bill_ref_page_no'] . ' ' . $billItem['bill_ref_char'] : null;
                    $description = $occupiedRow;
                    $level = $billItem['level'];
                    $type = $billItem['type'];

                    if( $key + 1 == $occupiedRows->count() && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID )
                    {
                        $id = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $unit = $billItem['uom'];

                        if( $billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED )
                        {
                            $notListedRates = $notListedValues;

                            $notListedRates[0] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $ratesAfterMarkup) ? $ratesAfterMarkup[ $billItem['id'] ] : 0);

                            $rate = $notListedRates;

                            $estimatedTotal = $estimatedTotalValues;
                            $estimatedTotal[0] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $totalAfterMarkup) ? $totalAfterMarkup[ $billItem['id'] ] : 0);

                            $total = $estimatedTotal;
                        }
                        else
                        {
                            $rate = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $ratesAfterMarkup) ? $ratesAfterMarkup[ $billItem['id'] ] : 0);
                            $total = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $totalAfterMarkup) ? $totalAfterMarkup[ $billItem['id'] ] : 0);
                        }

                        $quantityPerUnit = array();
                        $includeStatus = null;

                        foreach($billColumnSettings as $billColumnSetting)
                        {
                            $itemQuantity = array_key_exists($billItem['id'], $itemQuantities[ $billColumnSetting['id'] ]) ? $itemQuantities[ $billColumnSetting['id'] ][ $billItem['id'] ][0] : 0;

                            $quantityPerUnit[ $billColumnSetting['id'] ] = $itemQuantity;

                            $includeStatus[ $billColumnSetting['id'] ] = array_key_exists($billItem['id'], $itemIncludeStatus[ $billColumnSetting['id'] ]) ? $itemIncludeStatus[ $billColumnSetting['id'] ][ $billItem['id'] ] : true;
                        }
                    }
                    else
                    {
                        $id = null;
                        $unit = null;//unit
                        $rate = null;//rate
                        $total = null;//total
                        $quantityPerUnit = null;//qty per unit
                        $includeStatus = true;// include

                        if( $key + 1 == $occupiedRows->count() && $billItem['type'] == BillItem::TYPE_NOID )
                        {
                            $unit = $billItem['uom'];//unit
                        }
                    }

                    $deleted = in_array($billItem['id'], $deletedItemIds);

                    self::addRow($itemPages[ $pageCount ], $id, $rowIdx, $description, $level, $type, $unit, $rate, $quantityPerUnit, $includeStatus, $total, $deleted);

                    unset( $row );
                }

//                //blank row
                self::addBlankRow($itemPages[ $pageCount ]);

                $rowCount++;//plus one blank row;
                $itemIndex++;

                if( $notListedItem && count($this->tenderers) )
                {
                    $tenderersCount = 1;

                    $newPage = false;

                    foreach($this->tenderers as $tenderer)
                    {
                        if( array_key_exists($tenderer['id'], $this->tenderersNotListedItem) && array_key_exists($billItem['id'], $this->tenderersNotListedItem[ $tenderer['id'] ]) )
                        {
                            $item = $this->tenderersNotListedItem[ $tenderer['id'] ][ $billItem['id'] ];

                            $item['description'] = "({$item['tenderer']})  " . $item['description'];

                            $padding = '&nbsp;';
                            $characterToReduce = 1;

                            $occupiedRows = Utilities::justify($item['description'], $this->MAX_CHARACTERS - $characterToReduce);

                            $rowCount += count($occupiedRows) + 1;

                            $pushToTemp = ( $rowCount <= $maxRows ) ? false : true;

                            foreach($occupiedRows as $key => $occupiedRow)
                            {
                                $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                                $row[ self::ROW_BILL_ITEM_ROW_IDX ] = null;
                                $row[ self::ROW_BILL_ITEM_DESCRIPTION ] = $padding . $occupiedRow;
                                $row[ self::ROW_BILL_ITEM_LEVEL ] = $billItem['level'];
                                $row[ self::ROW_BILL_ITEM_TYPE ] = $billItem['type'];

                                if( $key + 1 == $occupiedRows->count() && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID )
                                {
                                    $notListedRates = $notListedValues;

                                    $estimatedTotal = $estimatedTotalValues;

                                    $row[ self::ROW_BILL_ITEM_ID ] = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                                    $row[ self::ROW_BILL_ITEM_UNIT ] = $this->tenderersNotListedItem[$tenderer['id']][$billItem['id']]['uom'] ?? null;

                                    $notListedRates[ $tenderersCount ] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $this->contractorRates[ $tenderer['id'] ]) ? $this->contractorRates[ $tenderer['id'] ][ $billItem['id'] ] : 0);
                                    $estimatedTotal[ $tenderersCount ] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $this->contractorTotals[ $tenderer['id'] ]) ? $this->contractorTotals[ $tenderer['id'] ][ $billItem['id'] ] : 0);

                                    $row[ self::ROW_BILL_ITEM_RATE ] = $notListedRates;
                                    $row[ self::ROW_BILL_ITEM_TOTAL ] = $estimatedTotal;
                                    $row[ self::ROW_BILL_ITEM_QTY_PER_UNIT ] = $item['quantities'];
                                    $row[ self::ROW_BILL_ITEM_INCLUDE ] = $item['include'];
                                }
                                else
                                {
                                    $row[ self::ROW_BILL_ITEM_ID ] = null;
                                    $row[ self::ROW_BILL_ITEM_UNIT ] = null;//unit
                                    $row[ self::ROW_BILL_ITEM_RATE ] = null;//rate
                                    $row[ self::ROW_BILL_ITEM_TOTAL ] = null; //total
                                    $row[ self::ROW_BILL_ITEM_QTY_PER_UNIT ] = null;//qty per unit
                                    $row[ self::ROW_BILL_ITEM_INCLUDE ] = true;// include
                                }

                                if( $pushToTemp )
                                {
                                    array_push($ancestors, $row);

                                    $newPage = true;
                                }
                                else
                                {
                                    array_push($itemPages[ $pageCount ], $row);
                                }

                                unset( $row );
                            }

                            if( $pushToTemp )
                            {
                                self::addBlankRow($ancestors);
                            }
                            else
                            {
                                self::addBlankRow($itemPages[ $pageCount ]);
                            }
                        }
                        $tenderersCount++;
                    }

                    if( $newPage )
                    {
                        $pageCount++;
                        unset( $billItems[ $x ], $occupiedRows );

                        $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $valuesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                        break;
                    }

                    unset( $notListedItem );
                }

                unset( $billItems[ $x ], $occupiedRows );
            }
            else
            {
                unset( $occupiedRows );

                $pageCount++;
                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $valuesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                break;
            }
        }
    }

    public function queryBillStructure()
    {
        $billStructure = array();

        if(count($this->itemIds))
        {
            $stmt = $this->pdo->prepare("SELECT e.id, e.id, e.description FROM ".BillElementTable::getInstance()->getTableName()." e
            WHERE e.project_structure_id = ".$this->bill->id." AND e.deleted_at IS NULL ORDER BY e.priority");

            $stmt->execute();

            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

            foreach($elements as $elementId => $element)
            {
                $result = array(
                    'id'          => $element[0]['id'],
                    'description' => $element[0]['description'],
                    'items'       => array()
                );

                $sql = "SELECT DISTINCT p.id, p.element_id, p.root_id, p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char, p.description, p.type, p.uom_id, uom.symbol AS uom, p.level, p.priority, p.lft
                    FROM ".BillItemTable::getInstance()->getTableName()." c
                    JOIN ".BillItemTable::getInstance()->getTableName()." p ON c.lft BETWEEN p.lft AND p.rgt
                    LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
                    WHERE c.id IN (".implode(',', $this->itemIds).") AND c.root_id = p.root_id AND c.element_id = ".$elementId." AND p.element_id = ".$elementId."
                    --AND c.deleted_at IS NULL AND c.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
                    ORDER BY p.element_id, p.priority, p.lft, p.level ASC";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                $result['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if(count($result['items']))
                {
                    array_push($billStructure, $result);
                }

                unset($element, $result);
            }
        }

        return $billStructure;
    }

    public function queryBillStructureWithoutDeletedItems()
    {
        return parent::queryBillStructure();
    }

    public function addRow(&$itemPage, $id, $rowIdx, $description, $level, $type, $unit, $rate, $quantityPerUnit, $include, $total, $deleted = false)
    {
        if( $deleted )
        {
            $rate = null;
            $quantityPerUnit = null;
            $include = null;
            $total = null;
        }

        $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $row[ self::ROW_BILL_ITEM_ID ] = $id;
        $row[ self::ROW_BILL_ITEM_ROW_IDX ] = $rowIdx;
        $row[ self::ROW_BILL_ITEM_DESCRIPTION ] = $description;
        $row[ self::ROW_BILL_ITEM_LEVEL ] = $level;
        $row[ self::ROW_BILL_ITEM_TYPE ] = $type;
        $row[ self::ROW_BILL_ITEM_UNIT ] = $unit;
        $row[ self::ROW_BILL_ITEM_RATE ] = $rate;
        $row[ self::ROW_BILL_ITEM_QTY_PER_UNIT ] = $quantityPerUnit;
        $row[ self::ROW_BILL_ITEM_INCLUDE ] = $include;
        $row[ self::ROW_BILL_ITEM_TOTAL ] = $total;
        $row[ self::ROW_BILL_ITEM_DELETED ] = $deleted;

        array_push($itemPage, $row);
    }

    public function addBlankRow(&$itemPage)
    {
        self::addRow($itemPage, -1, null, null, 0, self::ROW_TYPE_BLANK, null, null, null, null, null);
    }

}