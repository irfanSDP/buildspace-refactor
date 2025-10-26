<?php

class sfBuildspaceReportBillPageGenerator extends sfBuildspaceBQMasterFunction
{
    public $tendererIds;
    public $tenderers;
    public $pageTitle;
    public $sortingType;
    public $billIds;
    public $fontSize;
    public $contractorBillGrandTotals;
    public $selectedBillTotals;
    public $rationalizedBillTotals;
    public $headSettings;

    public function __construct($project, $tendererIds, $billIds, $sortingType, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
        $this->pdo         = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $this->project     = $project;
        $this->billIds     = $billIds;
        $this->sortingType = $sortingType;
        $this->pageTitle   = $pageTitle;
        $this->currency    = $project->MainInformation->Currency;
        $this->tendererIds = $tendererIds;
        $this->tenderers   = $this->getTenderers();

        $this->descriptionFormat = $descriptionFormat;

        if($sortingType)
        {
            $this->setOrientationAndSize();
        }
        else
        {
            $this->setOrientationAndSize(self::ORIENTATION_LANDSCAPE, self::PAGE_FORMAT_A4);
        }

        $this->printSettings  = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, TRUE);
        $this->fontSize       = $this->printSettings['layoutSetting']['fontSize'];
        $this->fontType       = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
        $this->headSettings   = $this->printSettings['headSettings'];

        $this->contractorBillGrandTotals = $this->getContractorBillGrandTotals();

        $this->selectedTenderer   = $this->getSelectedTenderer();
        $this->selectedBillTotals = $this->getSelectedBillGrandTotals();
        $this->rationalizedBillTotals = $this->getRationalizedBillGrandTotals();

        self::setMaxCharactersPerLine();
    }

    public function generatePages()
    {
        $bills                 = $this->getBills();
        $estimationBillTotals  = $this->getEstimateBillGrandTotals();
        $pageNumberDescription    = 'Page No. ';
        $pages                    = array();

        $itemPages = array();

        $this->generateBillPages($bills, 1, array(), $itemPages, $estimationBillTotals);

        $pages = SplFixedArray::fromArray($itemPages);

        unset($itemPages, $bills);

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

    public function generateBillPages(Array $billBills, $pageCount, $ancestors, &$itemPages, $billTotals, $newPage = false)
    {
        $itemPages[$pageCount] = array();
        $layoutSettings        = $this->printSettings['layoutSetting'];
        $maxRows               = $this->getMaxRows();
        $ancestors = (is_array($ancestors) && count($ancestors)) ? $ancestors : array();

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

        //blank row
        array_push($itemPages[$pageCount], $blankRow);//starts with a blank row
        $rowCount = 1;

        foreach($ancestors as $k => $row)
        {
            array_push($itemPages[$pageCount], $row);
            $rowCount += 1;
            unset($row);
        }

        $ancestors = array();

        $itemIndex    = 1;

        foreach($billBills as $x => $billBill)
        {
            $occupiedRows = Utilities::justify($billBills[$x]['description'], $this->MAX_CHARACTERS);

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
                    $row = new SplFixedArray(self::TOTAL_BILL_ITEM_PROPERTY);

                    $row[self::ROW_BILL_ITEM_ROW_IDX] = ($key == 0) ? $itemIndex : null;
                    $row[self::ROW_BILL_ITEM_DESCRIPTION] = $occupiedRow;

                    if($key+1 == $occupiedRows->count())
                    {
                        $row[self::ROW_BILL_ITEM_ID] = $billBill['id'];
                        $row[self::ROW_BILL_ITEM_RATE] = self::gridCurrencyRoundingFormat(array_key_exists($billBill['id'], $billTotals) ? $billTotals[$billBill['id']] : 0);
                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID] = null;
                        $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
                        $row[self::ROW_BILL_ITEM_RATE] = null;//rate
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = null;//qty per unit
                        $row[self::ROW_BILL_ITEM_INCLUDE] = true;// include
                    }

                    array_push($itemPages[$pageCount], $row);

                    unset($row);
                }

                //blank row
                array_push($itemPages[$pageCount], $blankRow);

                $rowCount++;//plus one blank row;
                $itemIndex++;
                $newPage = false;

                unset($billBills[$x], $occupiedRows);
            }
            else
            {
                unset($occupiedRows);

                $pageCount++;
                $this->generateBillPages($billBills, $pageCount, $ancestors, $itemPages, $billTotals, true);
                break;
            }
        }
    }

    protected function setOrientationAndSize($orientation = false, $pageFormat = false)
    {
      if($orientation)
      {
        $this->orientation = $orientation;
        $this->setPageFormat($this->generatePageFormat( ($pageFormat) ? $pageFormat : self::PAGE_FORMAT_A4 ));
      }
      else
      {
        $count = count($this->tendererIds);

        if($count <= 4)
        {
            $this->orientation = ($count <= 1) ? self::ORIENTATION_PORTRAIT : self::ORIENTATION_LANDSCAPE;
            $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
        }
        else
        {
            $this->orientation = self::ORIENTATION_LANDSCAPE;
            $this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A3));
        }
      }
    }

    public function setPageFormat( $pageFormat )
    {
        $this->pageFormat = $pageFormat;
    }

    protected function generatePageFormat($format)
    {
        switch(strtoupper($format))
        {
           /*
            *  For now we only handle A4 format. If there's necessity to handle other page
            * format we need to add to this method
            */
           case self::PAGE_FORMAT_A4 :
               $width = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
               $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
               $pf = array(
                   'page_format' => self::PAGE_FORMAT_A4,
                   'minimum-font-size' => $this->fontSize,
                   'width' => $width,
                   'height' => $height,
                   'pdf_margin_top' => 8,
                   'pdf_margin_right' => 10,
                   'pdf_margin_bottom' => 1,
                   'pdf_margin_left' => 10
               );
               break;
            case self::PAGE_FORMAT_A3 :
               $width = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 1000;
               $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 1000 : 800;
               $pf = array(
                   'page_format' => self::PAGE_FORMAT_A3,
                   'minimum-font-size' => $this->fontSize,
                   'width' => $width,
                   'height' => $height,
                   'pdf_margin_top' => 8,
                   'pdf_margin_right' => 10,
                   'pdf_margin_bottom' => 1,
                   'pdf_margin_left' => 10
               );
               break;
           // DEFAULT ISO A4
           default:
               $width = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
               $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
               $pf = array(
                   'page_format' => self::PAGE_FORMAT_A4,
                   'minimum-font-size' => $this->fontSize,
                   'width' => $width,
                   'height' => $height,
                   'pdf_margin_top' => 8,
                   'pdf_margin_right' => 10,
                   'pdf_margin_bottom' => 3,
                   'pdf_margin_left' => 10
               );
       }
       return $pf;
    }

    public function setMaxCharactersPerLine()
    {
      if ( $this->fontSize == 10 )
      {
          $this->MAX_CHARACTERS = 64;
      }
      else
      {
          $this->MAX_CHARACTERS = 56;
      }
    }

    public function getMaxRows()
    {
        $pageFormat = $this->getPageFormat();

        switch($pageFormat['page_format'])
        {
            case self::PAGE_FORMAT_A4:
                if($this->orientation == self::ORIENTATION_PORTRAIT)
                {
                  if(count($this->tenderers))
                  {
                    if(count($this->tenderers) <= 1)
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

    public function getBills()
    {
      $bills = array();

      if(count($this->billIds))
      {
        $stmt = $this->pdo->prepare("SELECT p.id, p.title AS description, p.type, p.lft
            FROM ".ProjectStructureTable::getInstance()->getTableName()." p
            WHERE p.id IN (".implode(',', $this->billIds).") AND p.type = ".ProjectStructure::TYPE_BILL."
            AND p.deleted_at IS NULL ORDER BY p.lft ASC");

        $stmt->execute();

        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

      return $bills;
    }

    public function getSelectedTenderer()
    {
      $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, t.original_tender_value AS grand_total
          FROM ".TenderSettingTable::getInstance()->getTableName()." t
          JOIN ".CompanyTable::getInstance()->getTableName()." c ON c.id = t.awarded_company_id
          WHERE t.project_structure_id = ".$this->project->id);

      $stmt->execute();

      return $selectedTenderer = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTenderers()
    {
        $tenderers = array();

        if(count($this->tendererIds))
        {
            $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, t.original_tender_value AS grand_total
                FROM ".TenderSettingTable::getInstance()->getTableName()." t
                JOIN ".CompanyTable::getInstance()->getTableName()." c ON c.id = t.awarded_company_id
                WHERE t.project_structure_id = ".$this->project->id." AND c.id IN (".implode(',', $this->tendererIds).")");

            $stmt->execute();
            $selectedTenderer = $stmt->fetch(PDO::FETCH_ASSOC);

            if($selectedTenderer)
            {
                $selectedTenderer['selected'] = true;

                array_push($tenderers, $selectedTenderer);
            }

            $companySqlStatement = ($selectedTenderer['id'] > 0) ? "AND c.id <> ".$selectedTenderer['id'] : null;

            $orderStatement = ($this->sortingType == TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST_TEXT) ? "ORDER BY grand_total asc" : "ORDER BY grand_total desc";

            if(count($this->tendererIds))
            {
                $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, xref.id AS tender_company_id, xref.show, COALESCE(SUM(r.grand_total), 0) AS grand_total
                FROM ".CompanyTable::getInstance()->getTableName()." c
                JOIN ".TenderCompanyTable::getInstance()->getTableName()." xref ON xref.company_id = c.id
                LEFT JOIN ".TenderBillElementGrandTotalTable::getInstance()->getTableName()." r ON r.tender_company_id = xref.id
                WHERE xref.project_structure_id = ".$this->project->id."
                AND c.id IN (".implode(', ', $this->tendererIds).") {$companySqlStatement}
                AND c.deleted_at IS NULL GROUP BY c.id, xref.show, xref.id ".$orderStatement);

                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach($result as $contractor)
                {
                    array_push($tenderers, $contractor);
                }
            }
        }

        return $tenderers;
    }

    public function getEstimateBillGrandTotals()
    {
      $result = array();

      if(count($this->billIds))
      {
        $sql = "SELECT p.id, COALESCE(SUM(i.grand_total_after_markup),0) AS value
          FROM ".ProjectStructureTable::getInstance()->getTableName()." p
          LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = p.id
          LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
          WHERE p.root_id = ".$this->project->id." AND e.deleted_at IS NULL
          AND p.id IN (".implode(',', $this->billIds).") GROUP BY p.id ORDER BY p.id ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $billGrandTotals = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        foreach($billGrandTotals as $billId => $amount)
        {
          $result[$billId] = $amount[0];
        }
      }

      return $result;
    }

    public function getEstimateProjectGrandTotal()
    {
      $result = array();

      if(count($this->billIds))
      {
        $sql = "SELECT p.root_id AS project_id, COALESCE(SUM(i.grand_total_after_markup),0) AS value
          FROM ".ProjectStructureTable::getInstance()->getTableName()." p
          LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = p.id
          LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
          WHERE p.root_id = ".$this->project->id." AND e.deleted_at IS NULL AND p.id IN (".implode(',', $this->billIds).") GROUP BY p.root_id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
      }

      return $result;
    }

    public function getContractorBillGrandTotals()
    {
      $result = array();

      if(count($this->tendererIds) && count($this->billIds))
      {
        $sql = "SELECT p.id, tc.company_id, COALESCE(SUM(rate.grand_total),0) AS value
          FROM ".ProjectStructureTable::getInstance()->getTableName()." p
          LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = p.id
          LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
          LEFT JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." rate ON rate.bill_item_id = i.id
          LEFT JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.id = rate.tender_company_id AND tc.project_structure_id = ".$this->project->id."
          WHERE p.root_id = ".$this->project->id." AND tc.company_id IN (".implode(',', $this->tendererIds).")
          AND e.deleted_at IS NULL AND p.id IN (".implode(',', $this->billIds).") GROUP BY p.id, tc.company_id ORDER BY p.id ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $billToCompanyTotals = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);



        foreach($billToCompanyTotals as $billId => $companies)
        {
          $result[$billId] = array();

          foreach($companies as $k => $company)
          {
            $result[$billId][$company['company_id']] = $company['value'];
          }
        }
      }

      return $result;
    }

    public function getContractorProjectGrandTotals()
    {
      $result = array();

      if(count($this->tendererIds) && count($this->billIds))
      {
        $sql = "SELECT p.root_id, tc.company_id, COALESCE(SUM(rate.grand_total),0) AS value
          FROM ".ProjectStructureTable::getInstance()->getTableName()." p
          LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = p.id
          LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
          LEFT JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." rate ON rate.bill_item_id = i.id
          LEFT JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.id = rate.tender_company_id AND tc.project_structure_id = ".$this->project->id."
          WHERE p.root_id = ".$this->project->id." AND tc.company_id IN (".implode(',', $this->tendererIds).")
          AND e.deleted_at IS NULL AND p.id IN (".implode(',', $this->billIds).") GROUP BY p.root_id, tc.company_id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $billToCompanyTotals = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

        foreach($billToCompanyTotals as $projectId => $companies)
        {
          $result = array();

          foreach($companies as $k => $company)
          {
            $result[$company['company_id']] = $company['value'];
          }
        }
      }

      return $result;
    }

    public function getSelectedProjectGrandTotal()
    {
      $result = array();

      $selectedTenderer = $this->getSelectedTenderer();

      if($selectedTenderer && count($this->billIds))
      {
        $sql = "SELECT p.root_id AS project_id, COALESCE(SUM(rate.grand_total),0) AS value
          FROM ".ProjectStructureTable::getInstance()->getTableName()." p
          LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = p.id
          LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
          LEFT JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." rate ON rate.bill_item_id = i.id
          LEFT JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.id = rate.tender_company_id AND tc.project_structure_id = ".$this->project->id."
          WHERE p.root_id = ".$this->project->id." AND tc.company_id = ".$selectedTenderer['id']."
          AND e.deleted_at IS NULL AND p.id IN (".implode(',', $this->billIds).") GROUP BY p.root_id ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
      }

      return $result;
    }

    public function getSelectedBillGrandTotals()
    {
      $result = array();

      $selectedTenderer = $this->getSelectedTenderer();

      if($selectedTenderer && count($this->billIds))
      {
        $sql = "SELECT p.id, COALESCE(SUM(rate.grand_total),0) AS value
          FROM ".ProjectStructureTable::getInstance()->getTableName()." p
          LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = p.id
          LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
          LEFT JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." rate ON rate.bill_item_id = i.id
          LEFT JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.id = rate.tender_company_id AND tc.project_structure_id = ".$this->project->id."
          WHERE p.root_id = ".$this->project->id." AND tc.company_id = ".$selectedTenderer['id']."
          AND e.deleted_at IS NULL AND p.id IN (".implode(',', $this->billIds).") GROUP BY p.id, tc.company_id ORDER BY p.id ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $billGrandTotals = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        foreach($billGrandTotals as $billId => $amount)
        {
            $result[$billId] = $amount[0];
        }
      }

      return $result;
    }

    public function getRationalizedProjectGrandTotal()
    {
      $result = array();

      if($this->project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED && count($this->billIds))
      {
        $sql = "SELECT p.root_id, COALESCE(SUM(rate.grand_total),0) AS value
          FROM ".ProjectStructureTable::getInstance()->getTableName()." p
          LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = p.id
          LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
          LEFT JOIN ".TenderBillItemRationalizedRatesTable::getInstance()->getTableName()." rate ON rate.bill_item_id = i.id
          WHERE p.root_id = ".$this->project->id." AND e.deleted_at IS NULL
          AND p.id IN (".implode(',', $this->billIds).") GROUP BY p.root_id";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

      return $result;
    }

    public function getRationalizedBillGrandTotals()
    {
      $result = array();

      if($this->project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED && count($this->billIds))
      {
        $sql = "SELECT p.id, COALESCE(SUM(rate.grand_total),0) AS value
          FROM ".ProjectStructureTable::getInstance()->getTableName()." p
          LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = p.id
          LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
          LEFT JOIN ".TenderBillItemRationalizedRatesTable::getInstance()->getTableName()." rate ON rate.bill_item_id = i.id
          WHERE p.root_id = ".$this->project->id." AND e.deleted_at IS NULL
          AND p.id IN (".implode(',', $this->billIds).") GROUP BY p.id ORDER BY p.id ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $billGrandTotals = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        foreach($billGrandTotals as $billId => $amount)
        {
            $result[$billId] = $amount[0];
        }
      }

      return $result;
    }
}