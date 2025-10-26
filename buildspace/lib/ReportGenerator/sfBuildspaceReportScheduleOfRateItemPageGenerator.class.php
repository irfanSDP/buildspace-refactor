<?php

class sfBuildspaceReportScheduleOfRateItemPageGenerator extends sfBuildspaceReportPageGenerator {

    const TOTAL_BILL_ITEM_PROPERTY = 7;

    public $tendererIds;
    public $tenderers;
    public $pageTitle;
    public $sortingType;
    public $itemIds;
    public $fontSize;
    public $contractorRates;
    public $contractorElementGrandTotals;
    public $headSettings;
    public $totalPages;

    public function __construct($bill, $element, $tendererIds, $itemIds, $sortingType, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->bill = $bill;

        // Bill Element is unused.
        $this->billElement = $element;
        $this->project = $bill->root_id == $bill->id ? $bill : ProjectStructureTable::getInstance()->find($bill->root_id);

        $this->itemIds = $itemIds;
        $this->sortingType = $sortingType;
        $this->pageTitle = $pageTitle;
        $this->currency = $this->project->MainInformation->Currency;
        $this->tendererIds = $tendererIds;
        $this->tenderers = $this->getTenderers();
        $this->descriptionFormat = $descriptionFormat;

        $this->setOrientationAndSize();

        $this->elementsOrder = $this->getElementOrder();
        $this->printSettings = ScheduleOfRateBillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->ScheduleOfRateBillLayoutSetting->id, true);
        $this->fontSize = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings = $this->printSettings['headSettings'];

        $this->contractorRates = $this->getContractorRates();

        self::setMaxCharactersPerLine();
    }

    /**
     * Generates the array for the printout.
     *
     * @return array
     */
    public function generatePages()
    {
        $this->billStructure = $billStructure = $this->queryBillStructure();
        $this->totalPages = 0;
        $pages = array();

        $elementIds = ScheduleOfRateBillItemTable::getInstance()->getElementIds($this->itemIds);
        $elementIds = ScheduleOfRateBillElementTable::getInstance()->sortBy($elementIds, 'priority');

        $elementCount = 0;
        foreach($elementIds as $elementId)
        {
            $element = ScheduleOfRateBillElementTable::getInstance()->find($elementId);
            $elementItems = ScheduleOfRateBillItemTable::getInstance()->getMatchingItemsByElementId($elementId, $this->itemIds);

            $elementPages = array();
            $pageNumber = 1;

            $this->addNewPage($elementPages, $pageNumber, $element, $elementItems);

            //put pages under one element
            $pages[ $elementId ] = array(
                'description'   => $element->description,
                'element_count' => ++$elementCount,
                'item_pages'    => SplFixedArray::fromArray($elementPages),
            );
        }

        return $pages;
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
        $this->totalPages++;

        $rowCount = 0;
        $workItemCounter = 0;
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
                if( $item['type'] == ScheduleOfRateBillItem::TYPE_WORK_ITEM )
                {
                    // Item counter is only incremented for work items.
                    $workItemCounter++;
                }

                $this->addItemRows($pageRows, $rowCount, $item, $workItemCounter);

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
            $this->addRow($pageRows, $rowCount, -1, null, $descriptionRow, 0, self::ROW_TYPE_ELEMENT, null, null);
        }
    }

    /**
     * Adds all rows for an item or header.
     *
     * @param $pageRows
     * @param $rowCount
     * @param $item
     * @param $workItemCounter
     */
    public function addItemRows(&$pageRows, &$rowCount, $item, $workItemCounter)
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
            $itemRate = null;

            if( ( $key == $firstKey ) && ( $item['type'] == ScheduleOfRateBillItem::TYPE_WORK_ITEM ) )
            {
                $itemRowIndex = Utilities::generateCharFromNumber($workItemCounter, $this->printSettings['layoutSetting']['includeIandO']);
            }
            if( $key == $lastKey )
            {
                $itemId = $item['id'];
                $itemRate = $item['estimation_rate'];
                $itemUnit = $item['uom_symbol'];
            }

            $this->addRow($pageRows, $rowCount, $itemId, $itemRowIndex, $itemDescription, $itemLevel, $itemType, $itemUnit, $itemRate);
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
     * @param $itemRate
     */
    public function addRow(&$pageRows, &$rowCount, $itemId, $itemRowIndex, $itemDescription, $itemLevel, $itemType, $itemUnit, $itemRate)
    {
        $newRow = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
        $newRow[ self::ROW_BILL_ITEM_ID ] = $itemId;
        $newRow[ self::ROW_BILL_ITEM_ROW_IDX ] = $itemRowIndex;
        $newRow[ self::ROW_BILL_ITEM_DESCRIPTION ] = $itemDescription;
        $newRow[ self::ROW_BILL_ITEM_LEVEL ] = $itemLevel;
        $newRow[ self::ROW_BILL_ITEM_TYPE ] = $itemType;
        $newRow[ self::ROW_BILL_ITEM_UNIT ] = $itemUnit;
        $newRow[ self::ROW_BILL_ITEM_RATE ] = $itemRate;

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
        $this->addRow($pageRows, $rowCount, -1, null, null, 0, self::ROW_TYPE_BLANK, null, null);
    }

    public function getTotalAfterMarkup()
    {
        //no total for schedule of rate
        return array();
    }

    public function getContractorTotals()
    {
        // no totals for schedule of rate, so we return totals instead.
        return $this->getContractorRates();
    }

    /**
     * Returns the tenderers rates for the items.
     * @return array
     */
    public function getContractorRates()
    {
        return ScheduleOfRateBillItemTable::getInstance()->getContractorRates($this->itemIds, $this->tendererIds);
    }

    public function getSelectedTenderer()
    {
        if( ! count($this->tendererIds) )
        {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT companies.id, companies.name, companies.shortname, tender_settings.original_tender_value AS grand_total
                FROM " . TenderSettingTable::getInstance()->getTableName() . " tender_settings
                JOIN " . CompanyTable::getInstance()->getTableName() . " companies ON companies.id = tender_settings.awarded_company_id
                WHERE tender_settings.project_structure_id = " . $this->bill->root_id . " AND companies.id IN (" . implode(',', $this->tendererIds) . ")");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if( empty( $result ) )
        {
            return false;
        }
        else
        {
            $selectedTenderer = $result[0];
        }

        return $selectedTenderer;
    }

    public function getTenderers()
    {
        $tenderers = array();

        if( count($this->tendererIds) )
        {
            $sortOrder = ( $this->sortingType == TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST_TEXT ) ? "asc" : "desc";
            $stmt = $this->pdo->prepare("
                select companies.id, companies.name, companies.shortname, COALESCE(SUM(tender_items.contractor_rate), 0) AS sum_of_rate
                from " . ScheduleOfRateBillElementTable::getInstance()->getTableName() . " elements
                left join " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . " items on items.element_id  = elements.id
                left join " . TenderScheduleOfRateTable::getInstance()->getTableName() . " tender_items on tender_items.schedule_of_rate_bill_item_id = items.id
                left join " . TenderCompanyTable::getInstance()->getTableName() . " tender_companies on tender_companies.id = tender_items.tender_company_id
                left join " . CompanyTable::getInstance()->getTableName() . " companies on companies.id = tender_companies.company_id
                where elements.project_structure_id = " . $this->bill->id . "
                and companies.id in (" . implode(",", $this->tendererIds) . ")
                group by companies.id
                order by sum_of_rate " . $sortOrder
            );

            $stmt->execute();
            $tenderers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $tenderers = $this->markSelectedTenderer($tenderers);
        }

        return $tenderers;
    }

    /**
     * Marks the selected tenderer as 'Selected' and puts it at the front of the array.
     *
     * @param $tenderers
     *
     * @return mixed
     */
    public function markSelectedTenderer($tenderers)
    {
        if( $selectedTenderer = $this->getSelectedTenderer() )
        {
            foreach($tenderers as $key => $tenderer)
            {
                if( $tenderer['id'] == $selectedTenderer['id'] )
                {
                    $tenderers[ $key ]['selected'] = true;

                    $selectedTenderer = array_splice($tenderers, $key, 1)[0];
                    break;
                }
            }

            if( isset( $selectedTenderer ) )
            {
                array_unshift($tenderers, $selectedTenderer);
            }
        }

        return $tenderers;
    }

    public function bottomFooterStyling()
    {
        return '.leftFooter {text-align:left; } ';
    }

    public function getCurrencyFormat()
    {
        return array( $this->printSettings['phrase']['currencyPrefix'] );
    }
}