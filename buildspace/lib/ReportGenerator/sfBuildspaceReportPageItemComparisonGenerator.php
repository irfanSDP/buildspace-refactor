<?php 

class sfBuildspaceReportPageItemComparisonGenerator extends sfBuildspaceReportPageGenerator
{
    public $selectedRates = array();
    public $rationalizedRates = array();
    public $selectedElementGrandTotal = array();
    public $rationalizedElementGrandTotal = array();
    public $selectedTenderer = array();

    public function __construct($bill, $element, $tendererIds, $itemIds, $sortingType, $pageTitle, $descriptionFormat = self::DESC_FORMAT_FULL_LINE)
    {
      $this->pdo         = ProjectStructureTable::getInstance()->getConnection()->getDbh();

      $this->bill        = $bill;
      $this->billElement = $element;
      $this->project = $project = $bill->root_id == $bill->id ? $bill : ProjectStructureTable::getInstance()->find($bill->root_id);

      $this->itemIds     = $itemIds;
      $this->sortingType = $sortingType;
      $this->pageTitle   = $pageTitle;
      $this->currency    = $project->MainInformation->Currency;
      $this->selectedTenderer   = $this->getSelectedTenderer();

      $this->descriptionFormat = $descriptionFormat;

      $this->setOrientationAndSize(self::ORIENTATION_LANDSCAPE, self::PAGE_FORMAT_A4);

      $this->elementsOrder  = $this->getElementOrder();
      $this->printSettings  = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings($bill->BillLayoutSetting->id, TRUE);
      $this->fontSize       = $this->printSettings['layoutSetting']['fontSize'];
      $this->fontType       = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
      $this->headSettings   = $this->printSettings['headSettings'];

      $this->selectedRates                 = $this->getSelectedRates();
      $this->selectedNotListedItems        = $this->getSelectedNotListedItems();
      $this->rationalizedRates             = $this->getRationalizedRates();
      $this->rationalizedNotListedItems    = $this->getRationalizedNotListedItems();
      $this->selectedElementGrandTotal     = $this->getSelectedElementGrandTotals();
      $this->rationalizedElementGrandTotal = $this->getRationalizedElementGrandTotals();

      self::setMaxCharactersPerLine();
      /*
      * We use SplFixedArray as row data structure. We can't use associative array with SPlFixedArray so we rely on indexes to set values.
      */
      $row = new SplFixedArray(6);
      $row[0] = -1;//id
      $row[1] = null;//row index
      $row[2] = null;//description
      $row[3] = 0;//level
      $row[4] = self::ROW_TYPE_BLANK;//type
      $row[5] = null;//unit

      $this->defaultRow = $row;
  }

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
      $notListedValues = array(0, 0);

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

              if($notListedItem && (count($this->rationalizedNotListedItems) || count($this->selectedNotListedItems)))
              {
                  $notListedItems = ($this->project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED) ? $this->rationalizedNotListedItems : $this->selectedNotListedItems;

                  $tenderersCount = 1;

                  $newPage = false;

                  if(array_key_exists($billItem['id'], $notListedItems))
                  {
                      $item = $notListedItems[$billItem['id']];

                      $padding = '';
                      $characterToReduce = 0;

                      for($j = 1; $j <= $tenderersCount; $j++)
                      {
                          $characterToReduce+=3;
                          $padding.='&nbsp;&nbsp;&nbsp;';
                      }

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
                              $row[self::ROW_BILL_ITEM_UNIT] = $this->selectedNotListedItems[$billItem['id']]['uom'] ?? null;

                              if($this->project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED)
                              {
                                  $notListedRates[$tenderersCount] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $this->rationalizedRates) ? $this->rationalizedRates[$billItem['id']] : 0);
                              }
                              else
                              {
                                  $notListedRates[$tenderersCount] = self::gridCurrencyRoundingFormat(array_key_exists($billItem['id'], $this->selectedRates) ? $this->selectedRates[$billItem['id']] : 0);
                              }

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

                      unset($item);
                  }

                  if($newPage)
                  {
                      $pageCount++;
                      unset($billItems[$x], $occupiedRows);

                      $this->generateBillItemPages($billItems, $billColumnSettings, $elementInfo, $pageCount, $ancestors, $itemPages, $ratesAfterMarkup, $lumpSumPercents, $itemIncludeStatus, $itemQuantities, true);
                      break;
                  }

                  unset($notListedItems, $notListedItem);
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

  public function getSelectedTenderer()
  {
    $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.shortname, t.original_tender_value AS grand_total 
        FROM ".TenderSettingTable::getInstance()->getTableName()." t 
        JOIN ".CompanyTable::getInstance()->getTableName()." c ON c.id = t.awarded_company_id
        WHERE t.project_structure_id = ".$this->bill->root_id);

    $stmt->execute();

    return $selectedTenderer = $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getSelectedNotListedItems()
  {
    $rates = array();

    $selectedTenderer = $this->getSelectedTenderer();

    if($selectedTenderer && count($this->itemIds))
    {
      $stmt = $this->pdo->prepare("SELECT nl_item.bill_item_id, nl_item.description, nl_item.uom_id, uom.symbol as uom_symbol, COALESCE(nl_qty.final_value, 0) AS final_value, nl_qty.bill_column_setting_id
        FROM ".TenderBillItemNotListedTable::getInstance()->getTableName()." nl_item
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON nl_item.uom_id = uom.id AND uom.deleted_at IS NULL
        LEFT JOIN ".TenderBillItemNotListedQuantityTable::getInstance()->getTableName()." nl_qty ON nl_qty.tender_bill_item_not_listed_id = nl_item.id
        LEFT JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.id = nl_item.tender_company_id
        WHERE nl_item.bill_item_id IN (".implode(',', $this->itemIds).") AND tc.company_id = ".$selectedTenderer['id']);

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

      $rates = $notListedItem;
    }

    return $rates;
  }

  public function getRationalizedNotListedItems()
  {
    $rates = array();

    if(count($this->itemIds))
    {
      $stmt = $this->pdo->prepare("SELECT nl_item.bill_item_id, nl_item.description, nl_item.uom_id, COALESCE(nl_qty.final_value, 0) AS final_value, nl_qty.bill_column_setting_id
        FROM ".TenderBillItemNotListedRationalizedTable::getInstance()->getTableName()." nl_item
        LEFT JOIN ".TenderBillItemNotListedRationalizedQuantityTable::getInstance()->getTableName()." nl_qty ON nl_qty.tender_bill_not_listed_item_rationalized_id = nl_item.id
        WHERE nl_item.bill_item_id IN (".implode(',', $this->itemIds).")");

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
                  'uom_id'      => $quantity['uom_id'],
                  'quantities'  => array(),
                  'include'     => array()
                );
              }

              $notListedItem[$itemId]['quantities'][$quantity['bill_column_setting_id']] = $quantity['final_value'];
              $notListedItem[$itemId]['include'][$quantity['bill_column_setting_id']] = true;
          }
      }

      $rates = $notListedItem;
    }

    return $rates;
  }

  public function getRationalizedRates()
  {
    $rates = array();

    if(count($this->itemIds))
    {
      $stmt = $this->pdo->prepare("SELECT i.id, COALESCE(rate.rate, 0) AS value 
        FROM ".TenderBillItemRationalizedRatesTable::getInstance()->getTableName()." rate
        LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON rate.bill_item_id = i.id
        WHERE i.id IN (".implode(',', $this->itemIds).") AND i.deleted_at IS NULL ORDER BY i.id");

      $stmt->execute();

      $rates = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

      /* Temporary Solution */
      foreach($rates as $itemId => $rate)
      {
        $rates[$itemId] = $rate[0];
      }
    }

    return $rates;
  }

  public function getSelectedRates()
  {
    $rates = array();

    $selectedTenderer = $this->getSelectedTenderer();

    if($selectedTenderer && count($this->itemIds))
    {
      $stmt = $this->pdo->prepare("SELECT i.id, COALESCE(rate.rate, 0) AS value 
        FROM ".TenderBillItemRateTable::getInstance()->getTableName()." rate
        LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON rate.bill_item_id = i.id
        LEFT JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.id = rate.tender_company_id 
        AND tc.project_structure_id = ".$this->bill->root_id."
        WHERE i.id IN (".implode(',', $this->itemIds).") AND tc.company_id = ".$selectedTenderer['id']." 
        AND i.deleted_at IS NULL ORDER BY i.id");

      $stmt->execute();

      $rates = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

      /* Temporary Solution */
      foreach($rates as $itemId => $rate)
      {
        $rates[$itemId] = $rate[0];
      }
    }

    return $rates;
  }

  public function getSelectedElementGrandTotals()
  {
    $result = array();

    $selectedTenderer = $this->getSelectedTenderer();

    if($selectedTenderer && count($this->itemIds))
    {
      $sql = "SELECT e.id, COALESCE(SUM(rate.grand_total),0) AS value 
        FROM ".BillElementTable::getInstance()->getTableName()." e
        LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL
        LEFT JOIN ".TenderBillItemRateTable::getInstance()->getTableName()." rate ON rate.bill_item_id = i.id
        LEFT JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.id = rate.tender_company_id AND tc.project_structure_id = ".$this->bill->root_id."
        WHERE e.project_structure_id = ".$this->bill->id." AND tc.company_id = ".$selectedTenderer['id']."
        AND e.deleted_at IS NULL AND i.id IN (".implode(',', $this->itemIds).") GROUP BY e.id, tc.company_id ORDER BY e.priority ";

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

  public function getRationalizedElementGrandTotals()
  {
    $result = array();

    if($this->project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED && count($this->itemIds))
    {
      $sql = "SELECT e.id, COALESCE(SUM(rate.grand_total),0) AS value 
        FROM ".BillElementTable::getInstance()->getTableName()." e
        LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND i.deleted_at IS NULL
        LEFT JOIN ".TenderBillItemRationalizedRatesTable::getInstance()->getTableName()." rate ON rate.bill_item_id = i.id
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
}