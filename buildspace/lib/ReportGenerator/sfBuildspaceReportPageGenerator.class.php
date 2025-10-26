<?php

class sfBuildspaceReportPageGenerator extends sfBuildspaceBQMasterFunction
{
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
        $this->contractorElementGrandTotals = $this->getContractorElementGrandTotals();

        self::setMaxCharactersPerLine();
    }

    public function generatePages()
    {
        $this->billStructure = $billStructure = $this->queryBillStructure();
        $estimationRates       = $this->getRatesAfterMarkup();
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

                    $this->generateBillItemPages($element['items'], $billColumnSettings, $elementInfo, 1, array(), $itemPages, $estimationRates, $lumpSumPercents, $itemIncludeStatus, $itemQuantities);

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
            $this->generateBillItemPages(array(), $billColumnSettings, null, 1, array(), $itemPages, $estimationRates, $lumpSumPercents, $itemIncludeStatus, $itemQuantities);

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

    public function generateBillItemPages(Array $billItems, Array $billColumnSettings, $elementInfo, $pageCount, $ancestors, &$itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, $newPage = false)
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

        //generate TendererNotlistedVal
        $notListedValues = array(0);

        if(count($this->tenderers))
        {
            foreach($this->tenderers as $tenderer)
            {
                $notListedValues[] = 0;
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

        foreach($billItems as $x => $billItem)
        {
            $occupiedRows = ($billItems[$x]['type'] == BillItem::TYPE_ITEM_HTML_EDITOR or $billItems[$x]['type'] == BillItem::TYPE_NOID) ? Utilities::justifyHtmlString($billItems[$x]['description'], $this->MAX_CHARACTERS) : Utilities::justify($billItems[$x]['description'], $this->MAX_CHARACTERS);

            if($this->descriptionFormat == self::DESC_FORMAT_ONE_LINE)
            {
                $oneLineDesc = $occupiedRows[0];
                $occupiedRows = new SplFixedArray(1);
                $occupiedRows[0] = $oneLineDesc;
            }

            $notListedItem = ($billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED) ? true : false;

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
                        $row[self::ROW_BILL_ITEM_ID] = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                        $row[self::ROW_BILL_ITEM_UNIT] = $billItem['uom'];

                        if($billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED)
                        {
                            $notListedRates = $notListedValues;

                            $notListedRates[0] =  self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $ratesAfterMarkup) ? $ratesAfterMarkup[$billItem['id']] : 0);

                            $row[self::ROW_BILL_ITEM_RATE] = $notListedRates;
                        }
                        else
                        {
                            $row[self::ROW_BILL_ITEM_RATE] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $ratesAfterMarkup) ? $ratesAfterMarkup[$billItem['id']] : 0);
                        }

                        $quantityPerUnit = array();
                        $includeStatus = null;

                        foreach($billColumnSettings as $billColumnSetting)
                        {
                            $itemQuantity = array_key_exists($billItem['id'], $itemQuantities[$billColumnSetting['id']]) ? $itemQuantities[$billColumnSetting['id']][$billItem['id']][0] : 0;

                            $quantityPerUnit[$billColumnSetting['id']] = $itemQuantity;

                            $includeStatus[$billColumnSetting['id']] = array_key_exists($billItem['id'], $itemIncludeStatus[$billColumnSetting['id']]) ? $itemIncludeStatus[$billColumnSetting['id']][$billItem['id']] : true;
                        }

                        $row[self::ROW_BILL_ITEM_INCLUDE]      = $includeStatus;
                        $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $quantityPerUnit;
                    }
                    else
                    {
                        $row[self::ROW_BILL_ITEM_ID] = null;
                        $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
                        $row[self::ROW_BILL_ITEM_RATE] = null;//rate
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

                                  $row[self::ROW_BILL_ITEM_ID] = $billItem['id'];//only work item will have id set so we can use it to display rates and quantities
                                  $row[self::ROW_BILL_ITEM_UNIT] = $this->tenderersNotListedItem[$tenderer['id']][$billItem['id']]['uom'] ?? null;

                                  $notListedRates[$tenderersCount] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $this->contractorRates[$tenderer['id']]) ? $this->contractorRates[$tenderer['id']][$billItem['id']] : 0);

                                  $row[self::ROW_BILL_ITEM_RATE] = $notListedRates;
                                  $row[self::ROW_BILL_ITEM_QTY_PER_UNIT] = $item['quantities'];
                                  $row[self::ROW_BILL_ITEM_INCLUDE] = $item['include'];
                              }
                              else
                              {
                                  $row[self::ROW_BILL_ITEM_ID] = null;
                                  $row[self::ROW_BILL_ITEM_UNIT] = null;//unit
                                  $row[self::ROW_BILL_ITEM_RATE] = null;//rate
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

                    $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                    break;
                  }

                  unset($notListedItem);
                }

                unset($billItems[$x], $occupiedRows);
            }
            else
            {
                unset($occupiedRows);

                $pageCount++;
                $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
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
                   'pdf_margin_bottom' => 1,
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
                    AND c.deleted_at IS NULL AND c.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL ORDER BY p.element_id, p.priority, p.lft, p.level ASC";

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

    public function getTenderers()
    {
        $tenderers = array();

        if(count($this->tendererIds))
        {
            $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, t.original_tender_value AS grand_total
                FROM ".TenderSettingTable::getInstance()->getTableName()." t
                JOIN ".CompanyTable::getInstance()->getTableName()." c ON c.id = t.awarded_company_id
                WHERE t.project_structure_id = ".$this->bill->root_id." AND c.id IN (".implode(',', $this->tendererIds).")");

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

    public function getRatesAfterMarkup()
    {
        $elementMarkupResults = array();
        $rateInfoColumns = array();

        if($this->bill->BillMarkupSetting->element_markup_enabled)
        {
            $stmt = $this->pdo->prepare("SELECT e.id, COALESCE(c.final_value, 0) as value FROM ".BillElementFormulatedColumnTable::getInstance()->getTableName()." c
                JOIN ".BillElementTable::getInstance()->getTableName()." e ON c.relation_id = e.id
                WHERE e.project_structure_id = ".$this->bill->id." AND c.column_name = '".BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE."'
                AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

            $stmt->execute();

            $elementMarkupResults = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
        }

        if(count($this->itemIds))
        {
          $stmt = $this->pdo->prepare("SELECT c.relation_id, i.element_id, c.column_name, COALESCE(c.final_value, 0) AS value FROM ".BillItemFormulatedColumnTable::getInstance()->getTableName()." c
              JOIN ".BillItemTable::getInstance()->getTableName()." i ON c.relation_id = i.id
              JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
              WHERE e.project_structure_id = ".$this->bill->id." AND (c.column_name = '".BillItem::FORMULATED_COLUMN_RATE."' OR c.column_name = '".BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE."')
              AND c.deleted_at IS NULL AND i.id IN (".implode(',', $this->itemIds).") AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

          $stmt->execute();

          $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

          foreach($columns as $column)
          {
              $markupSettingsInfo = array(
                  'bill_markup_enabled'       => $this->bill->BillMarkupSetting->bill_markup_enabled,
                  'bill_markup_percentage'    => $this->bill->BillMarkupSetting->bill_markup_percentage,
                  'element_markup_enabled'    => $this->bill->BillMarkupSetting->element_markup_enabled,
                  'element_markup_percentage' => array_key_exists($column['element_id'], $elementMarkupResults) ? $elementMarkupResults[$column['element_id']][0] : 0,
                  'item_markup_enabled'       => $this->bill->BillMarkupSetting->item_markup_enabled,
                  'rounding_type'             => $this->bill->BillMarkupSetting->rounding_type
              );

              $rateInfoColumns[$column['relation_id']]['markup_setting_info'] = $markupSettingsInfo;
              $rateInfoColumns[$column['relation_id']][$column['column_name']] = $column['value'];

              unset($column);
          }
        }

        $result = array();

        foreach($rateInfoColumns as $itemId => $column)
        {
            $markupPercentage = array_key_exists(BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE, $column) ? $column[BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE] : 0;
            $rate = array_key_exists(BillItem::FORMULATED_COLUMN_RATE, $column) ? $column[BillItem::FORMULATED_COLUMN_RATE] : 0;

            $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $markupPercentage, $column['markup_setting_info']);

            $result[$itemId] = number_format($rateAfterMarkup, 2, '.', '');
        }

        unset($columns, $rateInfoColumns);

        return $result;
    }

    public function getItemQuantities()
    {
        $implodedItemIds = null;
        $result = array();

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

        return $result;
    }

    public function getLumpSumPercent()
    {
        $result = array();

        if(count($this->itemIds))
        {
          $stmt = $this->pdo->prepare("SELECT i.id, c.percentage FROM ".BillItemLumpSumPercentageTable::getInstance()->getTableName()." c
              JOIN ".BillItemTable::getInstance()->getTableName()." i ON (c.bill_item_id = i.id AND i.type = ".BillItem::TYPE_ITEM_LUMP_SUM_PERCENT.")
              WHERE c.deleted_at IS NULL AND i.deleted_at IS NULL AND i.id IN (".implode(',', $this->itemIds).") ORDER BY i.id");

          $stmt->execute();

          $result = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
        }

        return $result;
    }

    public function getEstimateElementGrandTotals()
    {
      $result = array();

      if(count($this->itemIds))
      {
        $sql = "SELECT e.id, COALESCE(SUM(i.grand_total_after_markup),0) AS value
          FROM ".BillElementTable::getInstance()->getTableName()." e
          LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL
          WHERE e.project_structure_id = ".$this->bill->id." AND e.deleted_at IS NULL
          AND i.id IN (".implode(',', $this->itemIds).") GROUP BY e.id ORDER BY e.priority ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $elementGrandTotals = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        foreach($elementGrandTotals as $elementId => $amount)
        {
          $result[$elementId] = $amount[0];
        }
      }

      return $result;
    }

    public function getTendersNotListedItem()
    {
        $result = array();

        if(count($this->tendererIds))
        {
          foreach($this->tendererIds as $k => $companyId)
          {
            $stmt = $this->pdo->prepare("SELECT nl_item.bill_item_id, nl_item.description, nl_item.uom_id, uom.symbol as uom_symbol, COALESCE(nl_qty.final_value, 0) AS final_value, nl_qty.bill_column_setting_id, c.name as tenderer
              FROM ".TenderBillItemNotListedTable::getInstance()->getTableName()." nl_item
              LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON nl_item.uom_id = uom.id AND uom.deleted_at IS NULL
              LEFT JOIN ".TenderBillItemNotListedQuantityTable::getInstance()->getTableName()." nl_qty ON nl_qty.tender_bill_item_not_listed_id = nl_item.id
              LEFT JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.id = nl_item.tender_company_id
              LEFT JOIN " . CompanyTable::getInstance()->getTableName(). " c ON  c.id = tc.company_id AND c.deleted_at IS NULL
              WHERE nl_item.bill_item_id IN (".implode(',', $this->itemIds).") AND tc.company_id = ".$companyId);

            $stmt->execute();

            $notListedItem = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

            /* Temporary Solution */
            foreach($notListedItem as $itemId => $itemQuantities)
            {
                $notListedItem[$itemId] = null;

                foreach($itemQuantities as $k => $quantity)
                {
                    if(!is_array($notListedItem[$itemId]))
                    {
                      $notListedItem[$itemId] = array(
                        'description' => $quantity['description'],
                        'tenderer'    => $quantity['tenderer'],
                        'uom_id'      => $quantity['uom_id'],
                        'uom'         => $quantity['uom_symbol'],
                        'quantities'  => array(),
                        'include'     => array()
                      );
                    }

                    $notListedItem[$itemId]['quantities'][$quantity['bill_column_setting_id']] = $quantity['final_value'];
                    $notListedItem[$itemId]['include'][$quantity['bill_column_setting_id']] = true;
                }
            }

            $result[$companyId] = $notListedItem;

          }
        }

        return $result;
    }

    public function getContractorElementGrandTotals()
    {
      $result = array();

      if(count($this->tendererIds) && count($this->itemIds))
      {
        $sql = "SELECT e.id, tc.company_id, COALESCE(SUM(rate.grand_total),0) AS value
          FROM ".BillElementTable::getInstance()->getTableName()." e
          LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL
          LEFT JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." rate ON rate.bill_item_id = i.id
          LEFT JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.id = rate.tender_company_id AND tc.project_structure_id = ".$this->bill->root_id."
          WHERE e.project_structure_id = ".$this->bill->id." AND tc.company_id IN (".implode(',', $this->tendererIds).")
          AND e.deleted_at IS NULL AND i.id IN (".implode(',', $this->itemIds).") GROUP BY e.id, tc.company_id ORDER BY e.priority ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $elementToCompanyTotals = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

        foreach($elementToCompanyTotals as $elementId => $companies)
        {
          $result[$elementId] = array();

          foreach($companies as $k => $company)
          {
            $result[$elementId][$company['company_id']] = $company['value'];
          }
        }
      }

      return $result;
    }

    public function getContractorRates()
    {
      $result = array();

      if(count($this->tendererIds) && count($this->itemIds))
      {
        foreach($this->tendererIds as $k => $companyId)
        {
          $stmt = $this->pdo->prepare("SELECT i.id, COALESCE(rate.rate, 0) AS value
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

    public function getItemIncludeStatus()
    {
        $implodedItemIds = null;
        $result = array();

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

        foreach($this->bill->BillColumnSettings->toArray() as $column)
        {
            if ( ! empty($implodedItemIds) )
            {
                $stmt = $this->pdo->prepare("SELECT r.bill_item_id, r.include FROM ".BillItemTypeReferenceTable::getInstance()->getTableName()." r
                WHERE r.bill_item_id IN (".$implodedItemIds.") AND r.bill_column_setting_id = ".$column['id']."
                AND r.deleted_at IS NULL");

                $stmt->execute();

                $includeStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $result[$column['id']] = $includeStatus;
            }
            else
            {
                $result[$column['id']] = null;
            }
        }

        return $result;
    }
}