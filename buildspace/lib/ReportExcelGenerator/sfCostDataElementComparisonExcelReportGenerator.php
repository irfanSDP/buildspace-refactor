<?php

class sfCostDataElementComparisonExcelReportGenerator extends sfCostDataStandardComparisonExcelReportGenerator {

    const HEADER_TEXT_COST = 'Elemental Cost';

    protected $subtitle = 'Elements';

    protected function addAdditionalColumnGroupsToStructure($costDataId, $category)
    {
        $this->allColumns[] = "{$costDataId}.{$category}_cost";

        foreach($this->particulars as $particular)
        {
            $this->allColumns[] = "{$costDataId}.{$category}-{$particular['id']}";
        }

        $this->allColumns[] = "{$costDataId}.{$category}_percentage";
    }

    protected function defineColumnStructure()
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.description, p.summary_description
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            WHERE p.master_cost_data_id = {$this->masterCostData->id}
            AND p.is_used_for_cost_comparison = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $this->particulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->allColumns[] = "{$this->costData->id}.description";

        $this->addAdditionalColumnGroupsToStructure($this->costData->id, 'approved');
        $this->addAdditionalColumnGroupsToStructure($this->costData->id, 'awarded');
        $this->addAdditionalColumnGroupsToStructure($this->costData->id, 'adjusted');

        $this->allColumns[] = "{$this->costData->id}.variation_order_cost";
        $this->allColumns[] = "{$this->costData->id}.remarks";

        foreach($this->arrayOfBenchmarkCostData as $costData)
        {
            $this->addAdditionalColumnGroupsToStructure($costData->id, 'approved');
            $this->addAdditionalColumnGroupsToStructure($costData->id, 'awarded');
            $this->addAdditionalColumnGroupsToStructure($costData->id, 'adjusted');

            $this->allColumns[] = "{$costData->id}.variation_order_cost";
            $this->allColumns[] = "{$costData->id}.remarks";
        }
    }

    public function createHeader($new = false)
    {
        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);

        $this->currentRow++;
        $firstRow = $this->currentRow;

        // Account for merged header rows
        $this->currentRow++;

        $this->activeSheet->setCellValue($this->colDescription . $firstRow, self::COL_NAME_DESCRIPTION);
        $this->mergeRows($this->colDescription, $firstRow, 2);

        $this->currentCol = $this->colDescription;

        $this->currentCol++;

        $groupColumns = array();

        $groupColumns[] = self::COL_NAME_AMOUNT;

        foreach($this->particulars as $particular)
        {
            $groupColumns[] = self::HEADER_TEXT_COST.'/'.$particular['description'];
        }

        $groupColumns[] = self::COL_NAME_PERCENT;

        $budgetColumn = self::COL_NAME_BUDGET;
        if($this->costData->approved_date) $budgetColumn .= " (" . date('d-m-Y', strtotime($this->costData->approved_date)) . ")";
        $contractSumColumn = self::COL_NAME_CONTRACT_SUM;
        if($this->costData->awarded_date) $contractSumColumn .= " (" . date('d-m-Y', strtotime($this->costData->awarded_date)) . ")";
        $adjustedSumColumn = self::COL_NAME_ADJUSTED_SUM;
        if($this->costData->adjusted_date) $adjustedSumColumn .= " (" . date('d-m-Y', strtotime($this->costData->adjusted_date)) . ")";

        $headerInfo = $this->addHeaderColumns(
            array($this->costData->name =>
                array(
                    $budgetColumn => $groupColumns,
                    $contractSumColumn => $groupColumns,
                    $adjustedSumColumn => $groupColumns,
                    self::COL_NAME_VARIATION_ORDER,
                    self::COL_NAME_REMARKS,
                )
            ), $this->currentCol, $firstRow);

        $this->increment($this->currentCol, $headerInfo['numberOfColumns']);

        foreach($this->arrayOfBenchmarkCostData as $costData)
        {
            $budgetColumn = self::COL_NAME_BUDGET;
            if($costData->approved_date) $budgetColumn .= " (" . date('d-m-Y', strtotime($costData->approved_date)) . ")";
            $contractSumColumn = self::COL_NAME_CONTRACT_SUM;
            if($costData->awarded_date) $contractSumColumn .= " (" . date('d-m-Y', strtotime($costData->awarded_date)) . ")";
            $adjustedSumColumn = self::COL_NAME_ADJUSTED_SUM;
            if($costData->adjusted_date) $adjustedSumColumn .= " (" . date('d-m-Y', strtotime($costData->adjusted_date)) . ")";

            $headerInfo = $this->addHeaderColumns(
                array($costData->name =>
                    array(
                        $budgetColumn => $groupColumns,
                        $contractSumColumn => $groupColumns,
                        $adjustedSumColumn => $groupColumns,
                        self::COL_NAME_VARIATION_ORDER,
                        self::COL_NAME_REMARKS,
                    )
                ), $this->currentCol, $firstRow);

            $this->increment($this->currentCol, $headerInfo['numberOfColumns']);
        }

        $this->currentRow++;

        //Set header styling
        $this->activeSheet->getStyle($this->firstCol . $firstRow . ':' . $this->lastCol . $firstRow)->applyFromArray($this->getColumnHeaderStyle());

        // For merged header rows
        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    }

    protected function getItemValues(CostData $costData)
    {
        $itemIds = array_column($this->items, 'id');

        $parentItem = Doctrine_Core::getTable('MasterCostDataItem')->find($this->parentItemId);

        return CostDataItemTable::getElementValues($costData, $parentItem, $itemIds);
    }
}