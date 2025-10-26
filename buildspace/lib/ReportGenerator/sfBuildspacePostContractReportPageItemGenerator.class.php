<?php

class sfBuildspacePostContractReportPageItemGenerator extends sfBuildspaceBQMasterFunction
{
    public $pageTitle;
    public $sortingType;
    public $itemIds;
    public $fontSize;
    public $headSettings;
    public $affectedElements;
    public $revision;
    public $typeRef;

    const CLAIM_PREFIX  = "Valuation No: ";

    const TOTAL_BILL_ITEM_PROPERTY      = 12;
    const ROW_CLAIM_PREVIOUS            = 9;
    const ROW_CLAIM_WORKDONE            = 10;
    const ROW_CLAIM_CURRENT             = 11;
    const ROW_BILL_ITEM_CONTRACT_AMOUNT = 8;

    public function __construct($project = false, $bill, $affectedElements, $itemIds, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo         = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $this->bill        = $bill;
        $this->project     = $project;
        $project = ($project instanceof ProjectStructure) ? $project : ProjectStructureTable::getInstance()->find($bill->root_id);

        $this->itemIds           = $itemIds;
        $this->affectedElements  = count($affectedElements) ? $affectedElements : array();

        $this->pageTitle         = $pageTitle;
        $this->currency          = $project->MainInformation->Currency;
        $this->descriptionFormat = $descriptionFormat;

        $this->setOrientationAndSize();

        $this->printSettings  = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, TRUE);
        $this->fontSize       = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType       = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings   = $this->printSettings['headSettings'];

        self::setMaxCharactersPerLine();
    }

    public function generatePages($typeRef)
    {
        $pages                 = array();
        $this->typeRef         = $typeRef;
        $billStructure         = array();

        $this->revision = $revision  = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($this->project->PostContract);

        $elementTotals = $this->affectedElements;
        $elementGrandTotals = (count($this->itemIds)) ? PostContractTable::getTotalClaimRateGroupByElementAndItemIds($this->bill->id,  $this->itemIds, $typeRef, $revision, $this->project->PostContract->id) : array();
        $totalPage = 0;
        $itemPages = array();

        if(count($this->affectedElements))
        {
            foreach($this->affectedElements as $elementId => $affectedElement)
            {
                $element = new BillElement();
                $element->id = $elementId;

                list(
                    $billItems
                    ) = BillItemTable::getDataStructureForStandardClaimBillItemListFilteredByItemIds($element, $this->bill, $revision, $this->project->PostContract->id, $this->typeRef, json_encode($this->itemIds, true));

                if(count($billItems))
                {
                    $elementInfo = array(
                        'description' => $affectedElement['description']
                    );

                    $itemPages = array();

                    $this->generateBillItemPages($billItems, $elementInfo, 1, array(), $itemPages);

                    $page = array(
                        'description' => $affectedElement['description'],
                        'item_pages' => SplFixedArray::fromArray($itemPages)
                    );

                    $totalPage+= count($itemPages);

                    $pages[$elementId] = $page;
                }

                if(array_key_exists($elementId, $elementGrandTotals))
                {
                    $prevAmount = $elementGrandTotals[$elementId][0]['prev_amount'];
                    $currentAmount = $elementGrandTotals[$elementId][0]['current_amount'];
                    $prevPercentage = $elementGrandTotals[$elementId][0]['prev_percentage'];
                    $totalPerUnit = $elementGrandTotals[$elementId][0]['total_per_unit'];
                    $upToDateAmount = $elementGrandTotals[$elementId][0]['up_to_date_amount'];

                    $elementTotals[$elementId]['total_per_unit']        = $totalPerUnit;
                    $elementTotals[$elementId]['prev_percentage']       = ($totalPerUnit > 0) ? number_format(($prevAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
                    $elementTotals[$elementId]['prev_amount']           = $prevAmount;
                    $elementTotals[$elementId]['current_percentage']    = ($totalPerUnit > 0) ? number_format(($currentAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
                    $elementTotals[$elementId]['current_amount']        = $currentAmount;
                    $elementTotals[$elementId]['up_to_date_percentage'] = ($totalPerUnit > 0) ? number_format(($upToDateAmount / $totalPerUnit) * 100, 2, '.', '') : 0;
                    $elementTotals[$elementId]['up_to_date_amount']     = $upToDateAmount;
                    $elementTotals[$elementId]['up_to_date_qty']        = $elementGrandTotals[$elementId][0]['up_to_date_qty'];
                }

                $elementTotals[$elementId]['claim_type_ref_id'] = $typeRef->id;
                $elementTotals[$elementId]['relation_id']       = $this->bill->id;

                unset($itemPages, $element, $affectedElement, $billItems);
            }
        }
        else
        {
            $elementInfo = array(
                'description' => ''
            );

            $this->generateBillItemPages(array(), $elementInfo, 1, array(), $itemPages);

            $page = array(
                'description' => '',
                'item_pages' => SplFixedArray::fromArray($itemPages)
            );

            $totalPage+= count($itemPages);

            $pages[0] = $page;
        }

        $this->elementTotals = $elementTotals;
        $this->totalPage     = $totalPage;

        return $pages;
    }

    public function generateBillItemPages(Array $billItems, $elementInfo, $pageCount, $ancestors, &$itemPages, $newPage = false)
    {
        $itemPages[$pageCount] = array();
        $layoutSettings        = $this->printSettings['layoutSetting'];
        $maxRows               = $this->getMaxRows();
        $ancestors = (is_array($ancestors) && count($ancestors)) ? $ancestors : array();

        $blankRow                                       = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $blankRow[self::ROW_BILL_ITEM_ID]               = -1;//id
        $blankRow[self::ROW_BILL_ITEM_ROW_IDX]          = null;//row index
        $blankRow[self::ROW_BILL_ITEM_DESCRIPTION]      = null;//description
        $blankRow[self::ROW_BILL_ITEM_LEVEL]            = 0;//level
        $blankRow[self::ROW_BILL_ITEM_TYPE]             = self::ROW_TYPE_BLANK;//type
        $blankRow[self::ROW_BILL_ITEM_UNIT]             = null;//unit
        $blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//unit
        $blankRow[self::ROW_BILL_ITEM_RATE] = null;//unit
        $blankRow[self::ROW_BILL_ITEM_CONTRACT_AMOUNT]  = null;
        $blankRow[self::ROW_CLAIM_WORKDONE]             = null;
        $blankRow[self::ROW_CLAIM_PREVIOUS]             = null;
        $blankRow[self::ROW_CLAIM_CURRENT]              = null;

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
            $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//unit
            $row[self::ROW_BILL_ITEM_RATE] = null;//unit
            $row[self::ROW_BILL_ITEM_CONTRACT_AMOUNT]  = null;
            $row[self::ROW_CLAIM_WORKDONE]             = null;
            $row[self::ROW_CLAIM_PREVIOUS]             = null;
            $row[self::ROW_CLAIM_CURRENT]              = null;

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

        foreach($billItems as $x => $billItem)
        {
            $occupiedRows = ($billItems[$x]['type'] == BillItem::TYPE_ITEM_HTML_EDITOR or $billItems[$x]['type'] == BillItem::TYPE_NOID) ? Utilities::justifyHtmlString($billItems[$x]['description'], (strtoupper($billItems[$x]['description']) == $billItems[$x]['description']) ? $this->MAX_CHARACTERS - 10 : $this->MAX_CHARACTERS) : Utilities::justify($billItems[$x]['description'], (strtoupper($billItems[$x]['description']) == $billItems[$x]['description']) ? $this->MAX_CHARACTERS - 10 : $this->MAX_CHARACTERS);

            if($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
            {
                $oneLineDesc = $occupiedRows[0];
                $occupiedRows = new SplFixedArray(1);
                $occupiedRows[0] = $oneLineDesc;
            }

            $rowCount += count($occupiedRows);

            if($rowCount <= $maxRows)
            {
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

                    if($key+1 == $occupiedRows->count() && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID)
                    {
                        $row[self::ROW_BILL_ITEM_ID]    = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT]  = $billItem['uom_symbol'];
                        $row[self::ROW_BILL_ITEM_CONTRACT_AMOUNT]  = self::gridCurrencyRoundingFormat($billItem['total_per_unit']);
                        $row[self::ROW_BILL_ITEM_RATE]  = self::gridCurrencyRoundingFormat($billItem['rate']);
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT]  = self::gridCurrencyRoundingFormat($billItem['qty_per_unit']);
                        $row[self::ROW_CLAIM_WORKDONE]             = array('up_to_date_percentage' => $billItem['up_to_date_percentage'], 'up_to_date_amount' => $billItem['up_to_date_amount'], 'up_to_date_qty' => $billItem['up_to_date_qty']);
                        $row[self::ROW_CLAIM_PREVIOUS]             = array('prev_percentage' => $billItem['prev_percentage'], 'prev_amount' => $billItem['prev_amount']);
                        $row[self::ROW_CLAIM_CURRENT]              = array('current_percentage' => $billItem['current_percentage'], 'current_amount' => $billItem['current_amount']);

                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID] = null;
                        $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//unit
                        $row[self::ROW_BILL_ITEM_RATE] = null;//unit
                        $row[self::ROW_BILL_ITEM_CONTRACT_AMOUNT]  = null;
                        $row[self::ROW_CLAIM_WORKDONE]             = null;
                        $row[self::ROW_CLAIM_PREVIOUS]             = null;
                        $row[self::ROW_CLAIM_CURRENT]              = null;

                        if ( $key+1 == $occupiedRows->count() && $billItem['type'] == BillItem::TYPE_NOID )
                        {
                            $row[self::ROW_BILL_ITEM_UNIT] = $billItem['uom_symbol'];//unit
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

                unset($billItems[$x], $occupiedRows);
            }
            else
            {
                unset($occupiedRows);

                $pageCount++;
                $this->generateBillItemPages($billItems, $elementInfo, $pageCount, $ancestors, $itemPages, true);
                break;
            }
        }
    }

    protected function setOrientationAndSize($orientation = false, $pageFormat = false)
    {
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
    }

    public function setPageFormat( $pageFormat )
    {
        $this->pageFormat = $pageFormat;
    }

    protected function generatePageFormat($format)
    {
        $width = 595;

        $height = 800;

        return $pf = array(
            'page_format' => self::PAGE_FORMAT_A4,
            'minimum-font-size' => $this->fontSize,
            'width' => $width,
            'height' => $height,
            'pdf_margin_top' => 8,
            'pdf_margin_right' => 8,
            'pdf_margin_bottom' => 3,
            'pdf_margin_left' => 8
        );
    }

    public function setMaxCharactersPerLine()
    {
        $this->MAX_CHARACTERS = 45;
    }

    public function getMaxRows()
    {
        return $maxRows = 62;
    }

}