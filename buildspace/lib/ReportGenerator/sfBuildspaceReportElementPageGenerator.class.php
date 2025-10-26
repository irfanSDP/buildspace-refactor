<?php

class sfBuildspaceReportElementPageGenerator extends sfBuildspaceBQMasterFunction {

    public $tendererIds;
    public $tenderers;
    public $pageTitle;
    public $sortingType;
    public $elementIds;
    public $fontSize;
    public $contractorElementGrandTotals;
    public $selectedElementTotals;
    public $rationalizedElementTotals;
    public $headSettings;

    public function __construct($bill, $tendererIds, $elementIds, $sortingType, $pageTitle, $desc, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->bill    = $bill;
        $this->project = $project = $bill->root_id == $bill->id ? $bill : ProjectStructureTable::getInstance()->find($bill->root_id);

        $this->elementIds  = $elementIds;
        $this->sortingType = $sortingType;
        $this->pageTitle   = $pageTitle;
        $this->currency    = $project->MainInformation->Currency;
        $this->tendererIds = $tendererIds;
        $this->tenderers   = $this->getTenderers();

        $this->descriptionFormat = $descriptionFormat;

        if ( $sortingType )
        {
            $this->setOrientationAndSize();
        }
        else
        {
            $this->setOrientationAndSize(self::ORIENTATION_LANDSCAPE, self::PAGE_FORMAT_A4);
        }

        $this->elementsOrder = $this->getElementOrder();
        $this->printSettings = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, true);
        $this->fontSize      = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType      = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings  = $this->printSettings['headSettings'];

        $this->contractorElementGrandTotals = $this->getContractorElementGrandTotals();

        $this->selectedTenderer          = $this->getSelectedTenderer();
        $this->selectedElementTotals     = $this->getSelectedElementGrandTotals();
        $this->rationalizedElementTotals = $this->getRationalizedElementGrandTotals();

        self::setMaxCharactersPerLine();
    }

    public function generatePages()
    {
        $elements                = $this->getElements();
        $estimationElementTotals = $this->getEstimateElementGrandTotals();

        $itemPages = array();

        $this->generateBillElementPages($elements, 1, array(), $itemPages, $estimationElementTotals);

        $pages = SplFixedArray::fromArray($itemPages);

        unset( $itemPages, $elements );

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
    public function generateBillElementPages(Array $billElements, $pageCount, $ancestors, &$itemPages, $elementTotals)
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
        $blankRow[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//quantity per unit
        $blankRow[self::ROW_BILL_ITEM_INCLUDE]      = null;//include

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
        $rowCount = 1;

        foreach ( $ancestors as $k => $row )
        {
            array_push($itemPages[$pageCount], $row);
            $rowCount += 1;
            unset( $row );
        }

        $ancestors = array();

        $itemIndex = 1;

        foreach ( $billElements as $x => $billElement )
        {
            $occupiedRows = Utilities::justify($billElements[$x]['description'], $this->MAX_CHARACTERS);

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
                $this->generateBillElementPages($billElements, $pageCount, $ancestors, $itemPages, $elementTotals, true);
                break;
            }

            foreach ( $occupiedRows as $key => $occupiedRow )
            {
                $row                                   = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);
                $row[self::ROW_BILL_ITEM_ROW_IDX]      = ( $key == 0 ) ? $itemIndex : null;
                $row[self::ROW_BILL_ITEM_DESCRIPTION]  = $occupiedRow;
                $row[self::ROW_BILL_ITEM_ID]           = null;
                $row[self::ROW_BILL_ITEM_UNIT]         = null;//unit
                $row[self::ROW_BILL_ITEM_RATE]         = null;//rate
                $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//qty per unit
                $row[self::ROW_BILL_ITEM_INCLUDE]      = true;// include

                if ( $key + 1 == $occupiedRows->count() )
                {
                    $row[self::ROW_BILL_ITEM_ID]   = $billElement['id'];
                    $row[self::ROW_BILL_ITEM_RATE] = self::gridCurrencyRoundingFormat(array_key_exists($billElement['id'], $elementTotals) ? $elementTotals[$billElement['id']] : 0);
                }

                array_push($itemPages[$pageCount], $row);

                unset( $row );
            }

            //blank row
            array_push($itemPages[$pageCount], $blankRow);

            $rowCount ++;//plus one blank row;
            $itemIndex ++;

            unset( $billElements[$x], $occupiedRows );
        }
    }

    protected function setOrientationAndSize($orientation = false, $pageFormat = false)
    {
        if ( $orientation )
        {
            $this->orientation = $orientation;
            $this->setPageFormat($this->generatePageFormat(( $pageFormat ) ? $pageFormat : self::PAGE_FORMAT_A4));
        }
        else
        {
            $count = count($this->tendererIds);

            if ( $count <= 4 )
            {
                $this->orientation = ( $count <= 1 ) ? self::ORIENTATION_PORTRAIT : self::ORIENTATION_LANDSCAPE;
                $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
            }
            else
            {
                $this->orientation = self::ORIENTATION_LANDSCAPE;
                $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A3));
            }
        }
    }

    public function setPageFormat($pageFormat)
    {
        $this->pageFormat = $pageFormat;
    }

    protected function generatePageFormat($format)
    {
        switch (strtoupper($format))
        {
            /*
                *  For now we only handle A4 format. If there's necessity to handle other page
                * format we need to add to this method
                */
            case self::PAGE_FORMAT_A4 :
                $width  = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
                $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
                $pf     = array(
                    'page_format'       => self::PAGE_FORMAT_A4,
                    'minimum-font-size' => $this->fontSize,
                    'width'             => $width,
                    'height'            => $height,
                    'pdf_margin_top'    => 8,
                    'pdf_margin_right'  => 10,
                    'pdf_margin_bottom' => 3,
                    'pdf_margin_left'   => 10
                );
                break;
            case self::PAGE_FORMAT_A3 :
                $width  = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 1000;
                $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 1000 : 800;
                $pf     = array(
                    'page_format'       => self::PAGE_FORMAT_A3,
                    'minimum-font-size' => $this->fontSize,
                    'width'             => $width,
                    'height'            => $height,
                    'pdf_margin_top'    => 8,
                    'pdf_margin_right'  => 10,
                    'pdf_margin_bottom' => 3,
                    'pdf_margin_left'   => 10
                );
                break;
            // DEFAULT ISO A4
            default:
                $width  = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
                $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
                $pf     = array(
                    'page_format'       => self::PAGE_FORMAT_A4,
                    'minimum-font-size' => $this->fontSize,
                    'width'             => $width,
                    'height'            => $height,
                    'pdf_margin_top'    => 8,
                    'pdf_margin_right'  => 10,
                    'pdf_margin_bottom' => 3,
                    'pdf_margin_left'   => 10
                );
        }

        return $pf;
    }

    public function setMaxCharactersPerLine()
    {
        $this->MAX_CHARACTERS = 56;

        if ( $this->fontSize == 10 )
        {
            $this->MAX_CHARACTERS = 64;
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
        }

        return $maxRows;
    }

    public function getElements()
    {
        $elements = array();

        if ( count($this->elementIds) )
        {
            $stmt = $this->pdo->prepare("SELECT e.id, e.description, e.project_structure_id FROM " . BillElementTable::getInstance()->getTableName() . " e
            WHERE e.project_structure_id = " . $this->bill->id . " AND e.id IN (" . implode(',', $this->elementIds) . ") AND e.deleted_at IS NULL ORDER BY e.priority");

            $stmt->execute();

            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $elements;
    }

    public function getSelectedTenderer()
    {
        $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, t.original_tender_value AS grand_total
        FROM " . TenderSettingTable::getInstance()->getTableName() . " t
        JOIN " . CompanyTable::getInstance()->getTableName() . " c ON c.id = t.awarded_company_id
        WHERE t.project_structure_id = " . $this->bill->root_id);

        $stmt->execute();

        return $selectedTenderer = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTenderers()
    {
        $tenderers = array();

        if ( count($this->tendererIds) )
        {
            $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, t.original_tender_value AS grand_total
            FROM " . TenderSettingTable::getInstance()->getTableName() . " t
            JOIN " . CompanyTable::getInstance()->getTableName() . " c ON c.id = t.awarded_company_id
            WHERE t.project_structure_id = " . $this->bill->root_id . " AND c.id IN (" . implode(',', $this->tendererIds) . ")");

            $stmt->execute();
            $selectedTenderer = $stmt->fetch(PDO::FETCH_ASSOC);

            if ( $selectedTenderer )
            {
                $selectedTenderer['selected'] = true;

                array_push($tenderers, $selectedTenderer);
            }

            $companySqlStatement = ( $selectedTenderer['id'] > 0 ) ? "AND c.id <> " . $selectedTenderer['id'] : null;

            $orderStatement = ( $this->sortingType == TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST_TEXT ) ? "ORDER BY grand_total asc" : "ORDER BY grand_total desc";

            if ( count($this->tendererIds) && count($this->elementIds) )
            {
                $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, xref.id AS tender_company_id, xref.show, COALESCE(SUM(r.grand_total), 0) AS grand_total
                FROM " . CompanyTable::getInstance()->getTableName() . " c
                JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
                LEFT JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
                WHERE xref.project_structure_id = " . $this->project->id . "
                AND c.id IN (" . implode(', ', $this->tendererIds) . ") {$companySqlStatement}
                AND c.deleted_at IS NULL GROUP BY c.id, xref.show, xref.id " . $orderStatement);

                $stmt->execute();

                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ( $result as $contractor )
                {
                    array_push($tenderers, $contractor);
                }
            }
        }

        return $tenderers;
    }

    public function getEstimateBillGrandTotal()
    {
        $result = array();

        if ( count($this->elementIds) )
        {
            $stmt = $this->pdo->prepare("SELECT e.project_structure_id AS bill_id, COALESCE(SUM(i.grand_total_after_markup),0) AS value
            FROM " . BillElementTable::getInstance()->getTableName() . " e
            LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            WHERE e.project_structure_id = " . $this->bill->id . " AND e.deleted_at IS NULL
            AND e.id IN (" . implode(',', $this->elementIds) . ") GROUP BY e.project_structure_id");

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $result;
    }

    public function getEstimateElementGrandTotals()
    {
        $result = array();

        if ( count($this->elementIds) )
        {
            $stmt = $this->pdo->prepare("SELECT e.id, COALESCE(SUM(i.grand_total_after_markup),0) AS value
            FROM " . BillElementTable::getInstance()->getTableName() . " e
            LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            WHERE e.project_structure_id = " . $this->bill->id . " AND e.deleted_at IS NULL
            AND e.id IN (" . implode(',', $this->elementIds) . ") GROUP BY e.id ORDER BY e.priority ");

            $stmt->execute();

            $elementGrandTotals = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

            foreach ( $elementGrandTotals as $elementId => $amount )
            {
                $result[$elementId] = $amount[0];
            }
        }

        return $result;
    }

    public function getContractorBillGrandTotals()
    {
        $result = array();

        if ( count($this->tendererIds) && count($this->elementIds) )
        {
            $stmt = $this->pdo->prepare("SELECT e.project_structure_id, tc.company_id, COALESCE(SUM(rate.grand_total),0) AS value
            FROM " . BillElementTable::getInstance()->getTableName() . " e
            LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            LEFT JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " rate ON rate.bill_item_id = i.id
            LEFT JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.id = rate.tender_company_id AND tc.project_structure_id = " . $this->bill->root_id . "
            WHERE e.project_structure_id = " . $this->bill->id . " AND tc.company_id IN (" . implode(',', $this->tendererIds) . ")
            AND e.deleted_at IS NULL AND e.id IN (" . implode(',', $this->elementIds) . ") GROUP BY e.project_structure_id, tc.company_id ORDER BY tc.company_id ");

            $stmt->execute();

            $elementToCompanyTotals = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

            foreach ( $elementToCompanyTotals as $billId => $companies )
            {
                foreach ( $companies as $k => $company )
                {
                    $result[$company['company_id']] = $company['value'];
                }
            }
        }

        return $result;
    }

    public function getContractorElementGrandTotals()
    {
        $result = array();

        if ( count($this->tendererIds) && count($this->elementIds) )
        {
            $stmt = $this->pdo->prepare("SELECT e.id, tc.company_id, COALESCE(SUM(rate.grand_total),0) AS value
            FROM " . BillElementTable::getInstance()->getTableName() . " e
            LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            LEFT JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " rate ON rate.bill_item_id = i.id
            LEFT JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.id = rate.tender_company_id AND tc.project_structure_id = " . $this->bill->root_id . "
            WHERE e.project_structure_id = " . $this->bill->id . " AND tc.company_id IN (" . implode(',', $this->tendererIds) . ")
            AND e.deleted_at IS NULL AND e.id IN (" . implode(',', $this->elementIds) . ") GROUP BY e.id, tc.company_id ORDER BY e.priority ");

            $stmt->execute();

            $elementToCompanyTotals = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

            foreach ( $elementToCompanyTotals as $elementId => $companies )
            {
                $result[$elementId] = array();

                foreach ( $companies as $k => $company )
                {
                    $result[$elementId][$company['company_id']] = $company['value'];
                }
            }
        }

        return $result;
    }

    public function getSelectedBillGrandTotal()
    {
        $result = array();

        $selectedTenderer = $this->getSelectedTenderer();

        if ( $selectedTenderer && count($this->elementIds) )
        {
            $stmt = $this->pdo->prepare("SELECT e.project_structure_id, COALESCE(SUM(rate.grand_total),0) AS value
            FROM " . BillElementTable::getInstance()->getTableName() . " e
            LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            LEFT JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " rate ON rate.bill_item_id = i.id
            LEFT JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.id = rate.tender_company_id AND tc.project_structure_id = " . $this->bill->root_id . "
            WHERE e.project_structure_id = " . $this->bill->id . " AND tc.company_id = " . $selectedTenderer['id'] . "
            AND e.deleted_at IS NULL AND e.id IN (" . implode(',', $this->elementIds) . ") GROUP BY e.project_structure_id, tc.company_id ");

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $result;
    }

    public function getSelectedElementGrandTotals()
    {
        $result = array();

        $selectedTenderer = $this->getSelectedTenderer();

        if ( $selectedTenderer && count($this->elementIds) )
        {
            $stmt = $this->pdo->prepare("SELECT e.id, COALESCE(SUM(rate.grand_total),0) AS value
            FROM " . BillElementTable::getInstance()->getTableName() . " e
            LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            LEFT JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " rate ON rate.bill_item_id = i.id
            LEFT JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.id = rate.tender_company_id AND tc.project_structure_id = " . $this->bill->root_id . "
            WHERE e.project_structure_id = " . $this->bill->id . " AND tc.company_id = " . $selectedTenderer['id'] . "
            AND e.deleted_at IS NULL AND e.id IN (" . implode(',', $this->elementIds) . ") GROUP BY e.id, tc.company_id ORDER BY e.priority ");

            $stmt->execute();

            $elementGrandTotals = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

            foreach ( $elementGrandTotals as $elementId => $amount )
            {
                $result[$elementId] = $amount[0];
            }
        }

        return $result;
    }

    public function getRationalizedBillGrandTotal()
    {
        $result = array();

        if ( $this->project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED && count($this->elementIds) )
        {
            $stmt = $this->pdo->prepare("SELECT e.project_structure_id, COALESCE(SUM(rate.grand_total),0) AS value
            FROM " . BillElementTable::getInstance()->getTableName() . " e
            LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            LEFT JOIN " . TenderBillItemRationalizedRatesTable::getInstance()->getTableName() . " rate ON rate.bill_item_id = i.id
            WHERE e.project_structure_id = " . $this->bill->id . " AND e.deleted_at IS NULL
            AND e.id IN (" . implode(',', $this->elementIds) . ") GROUP BY e.project_structure_id ");

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $result;
    }

    public function getRationalizedElementGrandTotals()
    {
        $result = array();

        if ( $this->project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED && count($this->elementIds) )
        {
            $stmt = $this->pdo->prepare("SELECT e.id, COALESCE(SUM(rate.grand_total),0) AS value
            FROM " . BillElementTable::getInstance()->getTableName() . " e
            LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            LEFT JOIN " . TenderBillItemRationalizedRatesTable::getInstance()->getTableName() . " rate ON rate.bill_item_id = i.id
            WHERE e.project_structure_id = " . $this->bill->id . " AND e.deleted_at IS NULL
            AND e.id IN (" . implode(',', $this->elementIds) . ") GROUP BY e.id ORDER BY e.priority ");

            $stmt->execute();

            $elementGrandTotals = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

            foreach ( $elementGrandTotals as $elementId => $amount )
            {
                $result[$elementId] = $amount[0];
            }
        }

        return $result;
    }

}