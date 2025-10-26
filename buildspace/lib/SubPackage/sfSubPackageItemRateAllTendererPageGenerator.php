<?php

class sfSubPackageItemRateAllTendererPageGenerator extends sbSubPackageReportItemRateBaseGenerator {

    use sfBuildspaceReportPageFormat;

    const TOTAL_BILL_ITEM_PROPERTY    = 13;

    public $estimateElementTotal = array();
    public $contractorElementTotals = array();

    public function __construct(SubPackage $subPackage, ProjectStructure $bill, array $elements, array $items, $descriptionFormat = self::DESC_FORMAT_FULL_LINE, $itemIds)
    {
        $this->subPackage        = $subPackage;
        $this->elements          = $elements;
        $this->bill              = $bill;
        $this->items             = $items;
        $this->itemIds           = $itemIds;
        $this->currency          = $subPackage->ProjectStructure->MainInformation->Currency->currency_code;
        $this->descriptionFormat = $descriptionFormat;

        $this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, true);
        $this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings  = $this->printSettings['headSettings'];

        $this->estimateElementTotal = SubPackageBillItemRateTable::getElementEstimateTotals($this->subPackage, $this->bill, $this->itemIds);
        $this->contractorElementTotals = SubPackageBillItemRateTable::getElementContractorTotals($this->subPackage, $this->bill, $this->itemIds);

        $this->setOrientationAndSize(self::ORIENTATION_LANDSCAPE, self::PAGE_FORMAT_A4);

        self::setMaxCharactersPerLine();
    }

    public function generatePages()
    {
        $elementsAndItems = array();
        $data = SubPackageTable::getRatesAndTotalBySubPackageAndBillAndItemIds($this->subPackage,$this->bill,$this->itemIds);

        //transform data to match element->items
        foreach($data as $key => $dataItem)
        {
            if($dataItem['type'] == self::ROW_TYPE_ELEMENT)
            {
                $elementsAndItems[$dataItem['element_id']] = array();

                unset($data[$key]);
            }
        }
        foreach($data as $key => $dataItem)
        {
            if( ( $dataItem['type'] != self::ROW_TYPE_ELEMENT ) && array_key_exists($dataItem['element_id'], $elementsAndItems) )
            {
                array_push($elementsAndItems[$dataItem['element_id']], $dataItem);

                unset($data[$key]);
            }
        }

        $pages        = array();
        $elementCount = 0;

        foreach($elementsAndItems as $elementId => $elementItems)
        {
            $element = BillElementTable::getInstance()->find($elementId);

            $elementPages = array();
            $pageNumber = 1;

            $this->addNewPage($elementPages, $pageNumber, $element, $elementItems);

            //put pages under one element
            $pages[ $elementId ] = array(
                'id' => $element->id,
                'description'   => $element->description,
                'element_count' => ++$elementCount,
                'item_pages'    => SplFixedArray::fromArray($elementPages),
            );
        }

        return $pages;
    }

    public function getMaxRows()
    {
        $pageFormat = $this->getPageFormat();

        switch ($pageFormat['page_format'])
        {
            case self::PAGE_FORMAT_A4:
                if ( $this->orientation == self::ORIENTATION_PORTRAIT )
                {
                    if ( count($this->tenderers) )
                    {
                        if ( count($this->tenderers) <= 1 )
                        {
                            $maxRows = 55;
                        }
                        else
                        {
                            $maxRows = 65;
                        }
                    }
                    else
                    {
                        $maxRows = 55;
                    }
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

    public function setSubCons($subCons)
    {
        $this->subCons = $subCons;
    }

    /**
     * Adds a new page.
     *
     * @param $elementPages
     * @param $pageNumber
     * @param $element
     * @param $elementItems
     */
    public function addNewPage(&$elementPages, $pageNumber, $element, $elementItems)
    {
        $this->totalPage++;

        $rowCount = 0;
        $pageRows = array();

        // Starts with a blank row (spacing).
        $this->addBlankRow($pageRows, $rowCount);

        // Add Element description at the top of the page.
        $this->addElementRows($pageRows, $rowCount, $element->description);

        $this->addBlankRow($pageRows, $rowCount);

        foreach($elementItems as $key => $item)
        {
            if( $this->itemCanFitInPage($item, $rowCount) )
            {
                $this->addItemRows($pageRows, $rowCount, $item);

                $this->addBlankRow($pageRows, $rowCount);
            }
            else // If the item cannot fit into the page, we create a new page for all remaning items.
            {
                $remainingElementItems = array_slice($elementItems, $key);

                $this->addNewPage($elementPages, $pageNumber + 1, $element, $remainingElementItems);

                break;
            }
        }

        $elementPages[ $pageNumber ] = $pageRows;
    }

    /**
     * If the item (and the subsequent blank row) can fit into the page, the item is added to the page.
     *
     * @param $item
     * @param $rowCount
     *
     * @return bool
     */
    public function itemCanFitInPage($item, $rowCount)
    {
        $descriptionRows = $this->generateDescriptionRows($item['description']);

        return ( $rowCount + count($descriptionRows) + 1 ) <= $this->getMaxRows();
    }

    /**
     * Adds all rows for an element.
     *
     * @param $pageRows
     * @param $rowCount
     * @param $description
     */
    public function addElementRows(&$pageRows, &$rowCount, $description)
    {
        $descriptionRows = $this->generateDescriptionRows($description);

        foreach($descriptionRows as $descriptionRow)
        {
            $this->addRow($pageRows, $rowCount, -1, null, $descriptionRow, 0, self::ROW_TYPE_ELEMENT, null, null, null, null);
        }
    }

    /**
     * Adds all rows for an item or header.
     *
     * @param $pageRows
     * @param $rowCount
     * @param $item
     */
    public function addItemRows(&$pageRows, &$rowCount, $item)
    {
        $descriptionRows = $this->generateDescriptionRows($item['description']);

        reset($descriptionRows);
        $firstKey = key($descriptionRows);
        end($descriptionRows);
        $lastKey = key($descriptionRows);

        foreach($descriptionRows as $key => $descriptionRow)
        {
            $itemId = -1;
            $itemRowIndex = null;
            $itemDescription = $descriptionRow;
            $itemLevel = $item['level'];
            $itemType = $item['type'];
            $itemUnit = null;
            $itemQuantity = null;
            $itemRate = null;
            $itemTotal = null;

            if( ( $key == $firstKey ) && ( $item['type'] == ScheduleOfRateBillItem::TYPE_WORK_ITEM ) )
            {
                $itemRowIndex = ( $key == 0 && $item['type'] != BillItem::TYPE_HEADER && $item['type'] != BillItem::TYPE_HEADER_N && $item['type'] != BillItem::TYPE_NOID ) ? $item['bill_ref'] : null;
            }
            if( $key == $lastKey )
            {
                $itemId = $item['id'];
                $itemRate = $item['rate-value'];
                $itemUnit = $item['uom_symbol'];
                $itemTotal = $item['total_est_amount'];
                $itemQuantity = $item['total_qty'];
            }

            $this->addRow($pageRows, $rowCount, $itemId, $itemRowIndex, $itemDescription, $itemLevel, $itemType, $itemUnit, $itemQuantity, $itemRate, $itemTotal);
        }
    }

    /**
     * Generates description rows based on the description.
     *
     * @param $description
     *
     * @return SplFixedArray
     */
    public function generateDescriptionRows($description)
    {
        $descriptionRows = Utilities::justify($description, $this->MAX_CHARACTERS);

        if( $this->descriptionFormat == self::DESC_FORMAT_ONE_LINE )
        {
            $oneLineDesc = $descriptionRows[0];
            $descriptionRows = new SplFixedArray(1);
            $descriptionRows[0] = $oneLineDesc;
        }

        return $descriptionRows;
    }

    /**
     * Adds a row.
     *
     * @param $pageRows
     * @param $rowCount
     * @param $itemId
     * @param $itemRowIndex
     * @param $itemDescription
     * @param $itemLevel
     * @param $itemType
     * @param $itemUnit
     * @param $itemQuantity
     * @param $itemRate
     * @param $itemTotal
     */
    public function addRow(&$pageRows, &$rowCount, $itemId, $itemRowIndex, $itemDescription, $itemLevel, $itemType, $itemUnit, $itemQuantity, $itemRate, $itemTotal)
    {
        $newRow = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $newRow[ self::ROW_BILL_ITEM_ID ] = $itemId;
        $newRow[ self::ROW_BILL_ITEM_ROW_IDX ] = $itemRowIndex;
        $newRow[ self::ROW_BILL_ITEM_DESCRIPTION ] = $itemDescription;
        $newRow[ self::ROW_BILL_ITEM_LEVEL ] = $itemLevel;
        $newRow[ self::ROW_BILL_ITEM_TYPE ] = $itemType;
        $newRow[ self::ROW_BILL_ITEM_UNIT ] = $itemUnit;
        $newRow[ self::ROW_BILL_ITEM_QTY_PER_UNIT ] = $itemQuantity;
        $newRow[ self::ROW_BILL_ITEM_RATE ] = $itemRate;
        $newRow[ self::ROW_BILL_ITEM_TOTAL ] = $itemTotal;

        array_push($pageRows, $newRow);

        $rowCount++;

        unset( $newRow );
    }

    /**
     * Adds a blank row.
     *
     * @param $pageRows
     * @param $rowCount
     */
    public function addBlankRow(&$pageRows, &$rowCount)
    {
        $this->addRow($pageRows, $rowCount, -1, null, null, 0, self::ROW_TYPE_BLANK, null, null, null, null);
    }

    public function getContractorRates()
    {
        return SubPackageBillItemRateTable::getContractorRates($this->itemIds);
    }

}