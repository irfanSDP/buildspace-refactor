<?php

class sfSubPackageItemRateSelectedTendererPageGenerator extends sbSubPackageReportItemRateBaseGenerator {

    use sfBuildspaceReportPageFormat;

    const TOTAL_BILL_ITEM_PROPERTY = 10;
    const ROW_BILL_ITEM_ID = 0;
    const ROW_BILL_ITEM_ROW_IDX = 1;
    const ROW_BILL_ITEM_DESCRIPTION = 2;
    const ROW_BILL_ITEM_LEVEL = 3;
    const ROW_BILL_ITEM_TYPE = 4;
    const ROW_BILL_ITEM_UNIT = 5;
    const ROW_BILL_ITEM_RATE = 6;
    const ROW_BILL_ITEM_SUB_CON_RATE = 7;
    const ROW_BILL_ITEM_DIFF_AMT = 8;
    const ROW_BILL_ITEM_DIFF_PERCENT = 9;

    public $subCon = null;

    public $estimateElementTotal = array();
    public $estimateElementSubConTotal = array();

    public function __construct(SubPackage $subPackage, ProjectStructure $bill, array $elements, array $items, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->subPackage        = $subPackage;
        $this->elements          = $elements;
        $this->bill              = $bill;
        $this->items             = $items;
        $this->currency          = $subPackage->ProjectStructure->MainInformation->Currency->currency_code;
        $this->descriptionFormat = $descriptionFormat;

        $this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, true);
        $this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings  = $this->printSettings['headSettings'];

        $this->setOrientationAndSize(self::ORIENTATION_LANDSCAPE, self::PAGE_FORMAT_A4);

        self::setMaxCharactersPerLine();
    }

    public function generatePages()
    {
        $totalPage    = 0;
        $pages        = array();
        $elementCount = 1;

        if ( count($this->elements) )
        {
            foreach ( $this->elements as $element )
            {
                $this->estimateElementTotal[$element['id']]       = 0;
                $this->estimateElementSubConTotal[$element['id']] = 0;

                if ( isset( $this->items[$element['id']] ) AND count($this->items[$element['id']]) )
                {
                    $itemPages = array();

                    $elementInfo = array(
                        'id'            => $element['id'],
                        'description'   => $element['description'],
                        'element_count' => 1
                    );

                    $this->generateBillItemPages($this->items[$element['id']], $elementInfo, 1, array(), $itemPages);

                    $page = array(
                        'id'            => $element['id'],
                        'description'   => $element['description'],
                        'element_count' => $elementCount,
                        'item_pages'    => SplFixedArray::fromArray($itemPages)
                    );

                    $totalPage += count($itemPages);

                    $pages[$element['id']] = $page;

                    unset( $this->items[$element['id']], $itemPages, $element );
                }

                $elementCount ++;
            }
        }
        else
        {
            $itemPages = array();

            $this->generateBillItemPages(array(), null, 1, array(), $itemPages);

            $page = array(
                'id'            => - 1,
                'description'   => "N/a",
                'element_count' => 1,
                'item_pages'    => SplFixedArray::fromArray($itemPages)
            );

            $totalPage += count($itemPages);

            $pages[0] = $page;
        }

        $this->totalPage = $totalPage;

        return $pages;
    }

    public function generateBillItemPages(Array $billItems, $elementInfo, $pageCount, $ancestors, &$itemPages)
    {
        $itemPages[$pageCount] = array();
        $maxRows               = $this->getMaxRows();
        $ancestors             = ( is_array($ancestors) && count($ancestors) ) ? $ancestors : array();

        $blankRow                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $blankRow[self::ROW_BILL_ITEM_ID]           = - 1;//id
        $blankRow[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
        $blankRow[self::ROW_BILL_ITEM_DESCRIPTION]  = null;//description
        $blankRow[self::ROW_BILL_ITEM_LEVEL]        = 0;//level
        $blankRow[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_BLANK;//type
        $blankRow[self::ROW_BILL_ITEM_UNIT]         = null;//unit
        $blankRow[self::ROW_BILL_ITEM_RATE]         = null;//rate
        $blankRow[self::ROW_BILL_ITEM_SUB_CON_RATE] = null;//quantity per unit
        $blankRow[self::ROW_BILL_ITEM_DIFF_AMT]     = null;//include
        $blankRow[self::ROW_BILL_ITEM_DIFF_PERCENT] = null;

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
        $rowCount = 1;

        $occupiedRows = Utilities::justify($elementInfo['description'], $this->MAX_CHARACTERS);

        if ( $this->descriptionFormat == self::DESC_FORMAT_ONE_LINE )
        {
            $oneLineDesc     = $occupiedRows[0];
            $occupiedRows    = new SplFixedArray(1);
            $occupiedRows[0] = $oneLineDesc;
        }

        foreach ( $occupiedRows as $occupiedRow )
        {
            $row                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
            $row[self::ROW_BILL_ITEM_ID]           = - 1;//id
            $row[self::ROW_BILL_ITEM_ROW_IDX]      = null;//row index
            $row[self::ROW_BILL_ITEM_DESCRIPTION]  = $occupiedRow;//description
            $row[self::ROW_BILL_ITEM_LEVEL]        = 0;//level
            $row[self::ROW_BILL_ITEM_TYPE]         = self::ROW_TYPE_ELEMENT;//type
            $row[self::ROW_BILL_ITEM_UNIT]         = null;//unit
            $row[self::ROW_BILL_ITEM_RATE]         = null;//rate
            $row[self::ROW_BILL_ITEM_SUB_CON_RATE] = null;//quantity per unit
            $row[self::ROW_BILL_ITEM_DIFF_AMT]     = null;//include
            $row[self::ROW_BILL_ITEM_DIFF_PERCENT] = null;

            array_push($itemPages[$pageCount], $row);

            unset( $row );
        }

        //blank row
        array_push($itemPages[$pageCount], $blankRow);

        $rowCount += count($occupiedRows) + 1;//plus one blank row

        foreach ( $ancestors as $k => $row )
        {
            array_push($itemPages[$pageCount], $row);
            $rowCount += 1;
            unset( $row );
        }

        $ancestors = array();

        $itemIndex    = 1;
        $counterIndex = 0;//display item's index in BQ

        foreach ( $billItems as $x => $billItem )
        {
            $occupiedRows = ( $billItems[$x]['type'] == BillItem::TYPE_ITEM_HTML_EDITOR or $billItems[$x]['type'] == BillItem::TYPE_NOID ) ? Utilities::justifyHtmlString($billItems[$x]['description'], $this->MAX_CHARACTERS) : Utilities::justify($billItems[$x]['description'], $this->MAX_CHARACTERS);

            if ( $this->descriptionFormat == self::DESC_FORMAT_ONE_LINE )
            {
                $oneLineDesc     = $occupiedRows[0];
                $occupiedRows    = new SplFixedArray(1);
                $occupiedRows[0] = $oneLineDesc;
            }

            $rowCount += count($occupiedRows);

            if ( $rowCount >= $maxRows )
            {
                unset( $occupiedRows );

                $pageCount ++;
                $this->generateBillItemPages($billItems, $elementInfo, $pageCount, $ancestors, $itemPages, true);
                break;
            }

            foreach ( $occupiedRows as $key => $occupiedRow )
            {
                if ( $key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID )
                {
                    $counterIndex ++;
                }

                $row                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
                $row[self::ROW_BILL_ITEM_ROW_IDX]      = ( $key == 0 && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID ) ? $billItem['bill_ref'] : null;
                $row[self::ROW_BILL_ITEM_DESCRIPTION]  = $occupiedRow;
                $row[self::ROW_BILL_ITEM_LEVEL]        = $billItem['level'];
                $row[self::ROW_BILL_ITEM_TYPE]         = $billItem['type'];
                $row[self::ROW_BILL_ITEM_ID]           = null;
                $row[self::ROW_BILL_ITEM_UNIT]         = null;//unit
                $row[self::ROW_BILL_ITEM_RATE]         = null;//rate
                $row[self::ROW_BILL_ITEM_SUB_CON_RATE] = null;//qty per unit
                $row[self::ROW_BILL_ITEM_DIFF_AMT]     = null;
                $row[self::ROW_BILL_ITEM_DIFF_PERCENT] = null;

                if ( $key + 1 == $occupiedRows->count() && $billItem['type'] != BillItem::TYPE_HEADER && $billItem['type'] != BillItem::TYPE_HEADER_N && $billItem['type'] != BillItem::TYPE_NOID )
                {
                    $row[self::ROW_BILL_ITEM_ID]           = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                    $row[self::ROW_BILL_ITEM_UNIT]         = $billItem['uom_symbol'];
                    $row[self::ROW_BILL_ITEM_RATE]         = $billItem['rate-value'];
                    $row[self::ROW_BILL_ITEM_SUB_CON_RATE] = isset( $billItem[$this->subCon->id . '-rate-value'] ) ? $billItem[$this->subCon->id . '-rate-value'] : null;
                    $row[self::ROW_BILL_ITEM_DIFF_AMT]     = isset( $billItem[$this->subCon->id . '-difference_amount'] ) ? $billItem[$this->subCon->id . '-difference_amount'] : null;
                    $row[self::ROW_BILL_ITEM_DIFF_PERCENT] = isset( $billItem[$this->subCon->id . '-difference_percentage'] ) ? $billItem[$this->subCon->id . '-difference_percentage'] : null;

                    $this->estimateElementTotal[$billItem['element_id']] += $row[self::ROW_BILL_ITEM_RATE];
                    $this->estimateElementSubConTotal[$billItem['element_id']] += $row[self::ROW_BILL_ITEM_SUB_CON_RATE];
                }

                array_push($itemPages[$pageCount], $row);

                unset( $row );
            }

            //blank row
            array_push($itemPages[$pageCount], $blankRow);

            $rowCount ++;//plus one blank row;
            $itemIndex ++;

            unset( $billItems[$x], $occupiedRows );
        }
    }

    public function getMaxRows()
    {
        $pageFormat = $this->getPageFormat();

        switch ($pageFormat['page_format'])
        {
            case self::PAGE_FORMAT_A4:
                if ( $this->orientation == self::ORIENTATION_PORTRAIT )
                {
                    $maxRows = 55;
                }
                else
                {
                    $maxRows = 35;
                }
                break;

            default:
                $maxRows = $this->orientation == self::ORIENTATION_PORTRAIT ? 110 : 55;
                break;
        }

        return $maxRows;
    }

    public function setSubCon($subCon)
    {
        $this->subCon = $subCon;
    }

}