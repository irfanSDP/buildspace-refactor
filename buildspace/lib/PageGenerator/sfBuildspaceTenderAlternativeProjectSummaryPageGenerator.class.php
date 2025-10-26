<?php

class sfBuildspaceTenderAlternativeProjectSummaryGenerator
{
    protected $tenderAlternative;
    protected $pdo;
    protected $summaryItems;
    protected $includeCarriedForwardRow;
    protected $tenderCompany;
    protected $withNotListedItem;
    protected $withPrice = true;

    public $DEFAULT_MAX_ROWS = 48;

    public $MAX_ROWS = 48;
    public $ADDITIONAL_DESC_MAX_ROWS = 3;
    public $ADDITIONAL_DESC_MAX_CHARACTERS = 85;

    const MAX_CHARACTERS = 43;
    const PROJECT_TITLE_MAX_CHARACTERS = 75;

    const TOTAL_SUMMARY_ITEM_PROPERTY               = 7;
    const SUMMARY_ITEM_PROPERTY_CHAR_REF            = 0;
    const SUMMARY_ITEM_PROPERTY_TITLE               = 1;
    const SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE = 2;
    const SUMMARY_ITEM_PROPERTY_TOTAL_AMOUNT        = 3;
    const SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD       = 4;
    const SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC     = 5;
    const SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE  = 6;

    public function __construct(TenderAlternative $tenderAlternative, $includeCarriedForwardRow = true, $tenderCompany=null, $withNotListedItem=true)
    {
        $this->tenderAlternative = $tenderAlternative;

        $this->pdo = TenderAlternativeTable::getInstance()->getConnection()->getDbh();

        $this->includeCarriedForwardRow = $includeCarriedForwardRow;

        $this->tenderCompany = $tenderCompany;

        $this->withNotListedItem = $withNotListedItem;

        if($tenderCompany instanceof TenderCompany)
        {
            $this->summaryItems = $this->queryTenderCompanySummaryItems();
        }
        else
        {
            $this->summaryItems = $this->queryPreTenderSummaryItems();
        }

        $this->determineMaxRows();
    }

    public function queryPreTenderSummaryItems()
    {
        $tenderAlternative = $this->tenderAlternative;
        $project = $this->tenderAlternative->ProjectStructure;
        $linkedBillIds = [];

        foreach($tenderAlternative->Bills as $bill)
        {
            $linkedBillIds[] = $bill->project_structure_id;
        }

        $records = [];

        if(!empty($linkedBillIds))
        {
            $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.title, p.type, p.priority, p.lft, p.level, style.reference_char, style.is_bold, style.is_italic, style.is_underline
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " i
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p
            ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
            JOIN " . TenderAlternativeBillTable::getInstance()->getTableName() . " x ON i.id = x.project_structure_id
            JOIN " . TenderAlternativeTable::getInstance()->getTableName() . " ta ON ta.id = x.tender_alternative_id
            LEFT JOIN " . ProjectSummaryBillStyleTable::getInstance()->getTableName() . " style ON (p.id = style.project_structure_id)
            WHERE ta.id = " . $tenderAlternative->id . "  AND p.root_id = ".$tenderAlternative->project_structure_id." AND i.id IN (".implode(',', $linkedBillIds).")
            AND i.root_id = p.root_id AND i.type = ".ProjectStructure::TYPE_BILL."
            AND i.type <> " . ProjectStructure::TYPE_ROOT . " AND i.type <> " . ProjectStructure::TYPE_LEVEL . "
            AND p.deleted_at IS NULL AND i.deleted_at IS NULL AND ta.deleted_at IS NULL
            ORDER BY p.lft");
            
            $stmt->execute();

            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($project);

        $items = [];

        foreach($records as $key => $record)
        {
            if($record['type'] == ProjectStructure::TYPE_ROOT)
                continue;

            $record['page'] = null;
            $record['amount'] = $record['type'] == (ProjectStructure::TYPE_BILL && array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;

            if($record['type'] == ProjectStructure::TYPE_BILL)
            {
                $bill = ProjectStructureTable::getInstance()->find($record['id']);

                $bqPageGenerator = new sfBuildspaceBQPageGenerator($bill, null);
                $pages = $bqPageGenerator->generatePages();

                $record['page'] = $bqPageGenerator->getSummaryPageNumberingPrefix(count($pages['summary_pages']));//get last page;

                unset($bill, $bqPageGenerator);
            }

            $items[] = $record;

            unset($records[$key], $record);
        }

        return $items;
    }

    public function queryTenderCompanySummaryItems()
    {
        if(!$this->tenderCompany instanceof TenderCompany)
            return false;

        $tenderAlternative = $this->tenderAlternative;
        $project = $this->tenderAlternative->ProjectStructure;

        $whereClause = ($this->withNotListedItem) ? '' : 'AND i.type <> '.BillItem::TYPE_ITEM_NOT_LISTED;

        $stmt = $this->pdo->prepare("SELECT b.id, ROUND(COALESCE(SUM(rate.grand_total) ,0),2) AS total
            FROM ".BillItemTable::getInstance()->getTableName()." i
            JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." rate ON rate.bill_item_id = i.id AND rate.tender_company_id = ".$this->tenderCompany->id."
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.id = i.element_id AND e.deleted_at IS NULL
            JOIN ".ProjectStructureTable::getInstance()->getTableName()." b ON b.id = e.project_structure_id
            JOIN ".TenderAlternativeBillTable::getInstance()->getTableName()." x ON x.project_structure_id  = b.id 
            JOIN ".TenderAlternativeTable::getInstance()->getTableName()." ta ON ta.id = x.tender_alternative_id
            WHERE i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND e.deleted_at IS NULL
            AND ta.id = ".$tenderAlternative->id." AND ta.deleted_at IS NULL
            AND b.root_id = ".$tenderAlternative->project_structure_id."
            ".$whereClause."
            GROUP BY b.id");

        $stmt->execute();

        $billTotals = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $linkedBillIds = [];

        foreach($tenderAlternative->Bills as $bill)
        {
            $linkedBillIds[] = $bill->project_structure_id;
        }

        $records = [];

        if(!empty($linkedBillIds))
        {
            $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.title, p.type, p.priority, p.lft, p.level, style.reference_char, style.is_bold, style.is_italic, style.is_underline
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " i
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p
            ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
            JOIN " . TenderAlternativeBillTable::getInstance()->getTableName() . " x ON i.id = x.project_structure_id
            JOIN " . TenderAlternativeTable::getInstance()->getTableName() . " ta ON ta.id = x.tender_alternative_id
            LEFT JOIN " . ProjectSummaryBillStyleTable::getInstance()->getTableName() . " style ON (p.id = style.project_structure_id)
            WHERE ta.id = " . $tenderAlternative->id . " AND p.root_id = ".$tenderAlternative->project_structure_id." AND i.id IN (".implode(',', $linkedBillIds).")
            AND i.root_id = p.root_id AND i.type = ".ProjectStructure::TYPE_BILL."
            AND i.type <> " . ProjectStructure::TYPE_ROOT . " AND i.type <> " . ProjectStructure::TYPE_LEVEL . "
            AND p.deleted_at IS NULL AND i.deleted_at IS NULL AND ta.deleted_at IS NULL
            ORDER BY p.lft");
            
            $stmt->execute();

            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $items = [];

        foreach($records as $key => $record)
        {
            if($record['type'] == ProjectStructure::TYPE_ROOT)
                continue;
            
            $record['page'] = null;
            $record['amount'] = ($record['type'] == ProjectStructure::TYPE_BILL and array_key_exists($record['id'], $billTotals)) ? $billTotals[$record['id']] : 0;

            if($record['type'] == ProjectStructure::TYPE_BILL)
            {
                $bill = ProjectStructureTable::getInstance()->find($record['id']);

                $bqPageGenerator = new sfBuildspaceBQPageGenerator($bill, null);
                $pages = $bqPageGenerator->generatePages();

                $record['page'] = $bqPageGenerator->getSummaryPageNumberingPrefix(count($pages['summary_pages']));//get last page;

                unset($bill, $bqPageGenerator);
            }

            $items[] = $record;

            unset($records[$key], $record);
        }

        unset($billTotals);

        return $items;

    }

    public function generatePage()
    {
        $project             = $this->tenderAlternative->ProjectStructure;
        $itemPages           = array();
        $sumAmountPages      = array();
        $descriptionRow      = array();
        $descriptionRowCount = 0;

        list($headerRowCount, $headerRows) = $this->generateHeader();

        if ( $project->ProjectSummaryGeneralSetting->include_additional_description )
        {
            list($descriptionRowCount, $descriptionRow) = $this->generateAdditionalDescription();
        }

        $this->generateSummaryItemPages($this->summaryItems, 0, $itemPages, $sumAmountPages, false, $headerRowCount, $descriptionRowCount, $project->ProjectSummaryGeneralSetting->continued_from_previous_page_text);

        $includeTax = $project->ProjectSummaryGeneralSetting->include_tax;
        $taxPercentage = $project->ProjectSummaryGeneralSetting->tax_percentage;

        $sumAmount = end($sumAmountPages);

        if($includeTax)
        {
            $sumAmount += $sumAmount * ($taxPercentage / 100);
        }

        $sumAmount = Utilities::getRoundedAmount($project->MainInformation->eproject_origin_id, $sumAmount);

        $descriptionRowPrice = $this->generateTenderAmountText($sumAmount);

        return array(
            'header'                => SplFixedArray::fromArray($headerRows),
            'additional_desc'       => $descriptionRow,
            'additional_desc_price' => $descriptionRowPrice,
            'summary_items'         => SplFixedArray::fromArray($itemPages),
            'sum_amount_pages'      => SplFixedArray::fromArray($sumAmountPages)
        );
    }

    protected function generateTenderAmountText($sum)
    {
        $project = $this->tenderAlternative->ProjectStructure;

        function addRow(&$rows, $item)
        {
            $row = new SplFixedArray(1);
            $row[0] = $item;
            array_push($rows, $row);
        }

        function addEmptyRow(&$rows)
        {
            addRow($rows, NULL);    // empty line
        }

        function addEmptyRows($numberOfRowsToAdd, &$rows)
        {
            for ($i = 0; $i < $numberOfRowsToAdd; $i++) {
                addEmptyRow($rows); // empty lines
            }
        }

        $numToTextConverter = new NumberToTextConverter();
        $text = $numToTextConverter->customisedToCurrency($sum, $project->MainInformation->Currency->currency_name);

        $wrappedText = wordwrap($text, $this->ADDITIONAL_DESC_MAX_CHARACTERS, "!");

        $textRows = explode("!", $wrappedText);

        $descriptionRowPrice = array();

        addEmptyRow($descriptionRowPrice);      // first row blank

        // if there is an overflow of description text, put empty lines instead
        if( (count($textRows) > $this->ADDITIONAL_DESC_MAX_ROWS) )
        {
            addEmptyRows($this->ADDITIONAL_DESC_MAX_ROWS, $descriptionRowPrice);
        }
        else
        {
            foreach($textRows as $textRow)
            {
                addRow($descriptionRowPrice, $textRow); // filled lines
            }
            // adds empty lines up to maximum number of rows
            if( ($numberOfEmptyLinesToAdd = $this->ADDITIONAL_DESC_MAX_ROWS - count($textRows)) > 0 )
            {
                addEmptyRows($numberOfEmptyLinesToAdd, $descriptionRowPrice);   // empty lines
            }
        }
        addEmptyRow($descriptionRowPrice);  // last row blank

        return $descriptionRowPrice;
    }

    protected function generateHeader()
    {
        $project = $this->tenderAlternative->ProjectStructure;
        $occupiedRows = Utilities::justify($project->ProjectSummaryGeneralSetting->project_title, self::PROJECT_TITLE_MAX_CHARACTERS);

        $header = array();

        foreach($occupiedRows as $occupiedRow)
        {
            $row = new SplFixedArray(1);
            $row[0] = $occupiedRow;

            array_push($header, $row);
        }

        $rowCount = count($occupiedRows);

        if(strlen($project->ProjectSummaryGeneralSetting->project_title) > 0)
        {
            if ( $project->ProjectSummaryGeneralSetting->include_state_and_country )
            {
                $row = new SplFixedArray(1);
                $row[0] = strtoupper($project->MainInformation->Subregions->name).", ".strtoupper($project->MainInformation->Regions->country);

                array_push($header, $row);

                $rowCount +=1;
            }

            $blankRow = new SplFixedArray(1);
            $blankRow[0] = null;

            //blank row
            array_push($header, $blankRow);//starts with a blank row

            $rowCount += 1;
        }

        if(strlen($project->ProjectSummaryGeneralSetting->summary_title) > 0)
        {
            $occupiedRows = Utilities::justify($project->ProjectSummaryGeneralSetting->summary_title, 85);

            foreach($occupiedRows as $occupiedRow)
            {
                $row = new SplFixedArray(1);
                $row[0] = $occupiedRow;

                array_push($header, $row);
            }

            $rowCount += count($occupiedRows);

            $blankRow = new SplFixedArray(1);
            $blankRow[0] = null;

            //blank row
            array_push($header, $blankRow);//starts with a blank row

            $rowCount += 1;
        }

        if(strlen($this->tenderAlternative->title) > 0)
        {
            $occupiedRows = Utilities::justify($this->tenderAlternative->title, 85);

            foreach($occupiedRows as $occupiedRow)
            {
                $row = new SplFixedArray(1);
                $row[0] = $occupiedRow;

                array_push($header, $row);
            }

            $rowCount += count($occupiedRows);

            $blankRow = new SplFixedArray(1);
            $blankRow[0] = null;

            //blank row
            array_push($header, $blankRow);//starts with a blank row

            $rowCount += 1;
        }

        return array($rowCount, $header);
    }

    protected function generateAdditionalDescription()
    {
        $project             = $this->tenderAlternative->ProjectStructure;
        $descriptionRowCount = 0;
        $rowCount            = 0;
        $header              = array();
        $blankRow            = new SplFixedArray(1);
        $blankRow[0]         = NULL;
        $rowCount += 1;

        //blank row
        array_push($header, $blankRow);//starts with a blank row

        $occupiedRows = Utilities::justify($project->ProjectSummaryGeneralSetting->additional_description, $this->ADDITIONAL_DESC_MAX_CHARACTERS);

        foreach($occupiedRows as $occupiedRow)
        {
            $row = new SplFixedArray(1);
            $row[0] = $occupiedRow;

            array_push($header, $row);

            $descriptionRowCount++;
        }

        // generate left over row
        if ( $descriptionRowCount < $this->ADDITIONAL_DESC_MAX_ROWS )
        {
            while ($descriptionRowCount < $this->ADDITIONAL_DESC_MAX_ROWS)
            {
                array_push($header, $blankRow);//starts with a blank row

                $rowCount += 1;

                $descriptionRowCount++;
            }
        }

        $rowCount += count($occupiedRows);
        $rowCount += 1;

        //blank row
        array_push($header, $blankRow);//starts with a blank row

        return array($rowCount, $header);
    }

    protected function generateSummaryItemPages(Array $summaryItems, $pageCount, &$itemPages, &$sumAmountPages, $newPage=false, $headerRowCount=1, $descriptionRowCount = 1, $continuedFromPreviousText=null)
    {
        $itemPages[$pageCount] = array();
        $sumAmountPages[$pageCount] = 0;
        $sumAmount = $pageCount > 0 ? $sumAmountPages[$pageCount-1] : 0;

        $blankRow = new SplFixedArray(self::TOTAL_SUMMARY_ITEM_PROPERTY);
        $blankRow[self::SUMMARY_ITEM_PROPERTY_CHAR_REF] = null;//character reference
        $blankRow[self::SUMMARY_ITEM_PROPERTY_TITLE] = null;//title
        $blankRow[self::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE] = null;//last page summary
        $blankRow[self::SUMMARY_ITEM_PROPERTY_TOTAL_AMOUNT] = null;//total amount for bill
        $blankRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD] = null;
        $blankRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC] = null;
        $blankRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE] = null;

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row

        $rowCount = 1 + $headerRowCount + $descriptionRowCount;

        foreach($summaryItems as $x => $summaryItem)
        {
            if($newPage and $pageCount > 0 and $this->includeCarriedForwardRow)
            {
                $continueFromPreviousPageRow = new SplFixedArray(self::TOTAL_SUMMARY_ITEM_PROPERTY);
                $continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_CHAR_REF] = null;
                $continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_TITLE] = $continuedFromPreviousText;
                $continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE] = null;
                $continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_TOTAL_AMOUNT] = $sumAmountPages[$pageCount-1];//total amount from previous sumAmountPages
                $continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD] = true;
                $continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC] = null;
                $continueFromPreviousPageRow[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE] = true;

                array_push($itemPages[$pageCount], $continueFromPreviousPageRow);

                //blank row
                array_push($itemPages[$pageCount], $blankRow);

                $rowCount += 2;
            }

            $occupiedRows = Utilities::justify($summaryItem['title'], self::MAX_CHARACTERS);
            $rowCount += count($occupiedRows);

            if($rowCount <= $this->MAX_ROWS)
            {
                foreach($occupiedRows as $key => $occupiedRow)
                {
                    $row = new SplFixedArray(self::TOTAL_SUMMARY_ITEM_PROPERTY);
                    $row[self::SUMMARY_ITEM_PROPERTY_CHAR_REF] = $key == 0 ? $summaryItem['reference_char'] : null;//character reference
                    $row[self::SUMMARY_ITEM_PROPERTY_TITLE] = $occupiedRow;//title
                    $row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD] = $summaryItem['is_bold'];
                    $row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC] = $summaryItem['is_italic'];
                    $row[self::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE] = $summaryItem['is_underline'];
                    $row[self::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE] = null;//last page summary
                    $row[self::SUMMARY_ITEM_PROPERTY_TOTAL_AMOUNT] = null;//total amount for bill

                    if($key+1 == $occupiedRows->count() and $summaryItem['type'] == ProjectStructure::TYPE_BILL)
                    {
                        $row[self::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE] = $summaryItem['page'];//last page summary
                        $row[self::SUMMARY_ITEM_PROPERTY_TOTAL_AMOUNT] = $summaryItem['amount'] == 0 ? null : $summaryItem['amount'];//total amount for bill
                    }

                    array_push($itemPages[$pageCount], $row);
                }

                //blank row
                array_push($itemPages[$pageCount], $blankRow);

                $rowCount++;//plus one blank row;

                $sumAmount += $summaryItem['amount'];

                $sumAmountPages[$pageCount] = $sumAmount;//always update to the total sum of amount for each items

                $newPage = false;

                unset($summaryItems[$x], $row);
            }
            else
            {
                $pageCount++;
                $this->generateSummaryItemPages($summaryItems, $pageCount, $itemPages, $sumAmountPages, true, $headerRowCount, $descriptionRowCount, $continuedFromPreviousText);
                break;
            }
        }
    }

    protected function determineMaxRows()
    {
        $project = $this->tenderAlternative->ProjectStructure;

        if( $project->ProjectSummaryGeneralSetting->include_state_and_country )
        {
            $this->MAX_ROWS = $this->MAX_ROWS + 1;
        }

        if( $this->withPrice || $project->ProjectSummaryGeneralSetting->include_additional_description )
        {
            $this->MAX_ROWS = $this->MAX_ROWS - 3 - $this->ADDITIONAL_DESC_MAX_ROWS;
        }
    }

    public function setParameters($withPrice)
    {
        $this->withPrice = $withPrice;
    }

}
