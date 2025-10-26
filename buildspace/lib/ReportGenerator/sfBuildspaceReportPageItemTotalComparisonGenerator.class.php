<?php 

class sfBuildspaceReportPageItemTotalComparisonGenerator extends sfBuildspaceReportPageItemComparisonGenerator
{
    public $selectedRates = array();
    public $rationalizedRates = array();
    public $selectedTenderer = array();

  public function getRatesAfterMarkup()
  {
      $result = array();

      if(count($this->itemIds))
      {
        $stmt = $this->pdo->prepare("SELECT i.id, COALESCE(i.grand_total_after_markup ,0) AS value
            FROM ".BillItemTable::getInstance()->getTableName()." i
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            WHERE e.project_structure_id = ".$this->bill->id." AND i.id IN (".implode(',', $this->itemIds).") 
            AND i.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

        foreach($result as $itemId => $value)
        {
          $result[$itemId] = $value[0];
        }
      }

      return $result;
  }

  public function getRationalizedRates()
  {
    $rates = array();

    if(count($this->itemIds))
    {
      $stmt = $this->pdo->prepare("SELECT i.id, COALESCE(rate.grand_total, 0) AS value 
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
      $stmt = $this->pdo->prepare("SELECT i.id, COALESCE(rate.grand_total, 0) AS value 
        FROM ".TenderBillItemRateTable::getInstance()->getTableName()." rate
        LEFT JOIN ".BillItemTable::getInstance()->getTableName()." i ON rate.bill_item_id = i.id
        LEFT JOIN ".TenderCompanyTable::getInstance()->getTableName()." tc ON tc.id = rate.tender_company_id
        WHERE i.id IN (".implode(',', $this->itemIds).") AND tc.company_id = ".$selectedTenderer['id']." AND i.deleted_at IS NULL ORDER BY i.id");

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
}