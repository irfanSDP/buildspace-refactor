<?php

class sfCostDataWorkCategoryComparisonExcelReportGenerator extends sfCostDataStandardComparisonExcelReportGenerator {

    protected $subtitle    = 'Work Categories';
    protected $particulars = [];
    protected $sums        = [];

    protected function defineColumnStructure()
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.description, p.summary_description
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . MasterCostDataItemParticularTable::getInstance()->getTableName() . " ip on ip.master_cost_data_particular_id = p.id
            WHERE p.master_cost_data_id = {$this->masterCostData->id}
            AND ip.master_cost_data_item_id = {$this->parentItemId}
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

    protected function addAdditionalColumnGroupsToStructure($costDataId, $category)
    {
        $this->allColumns[] = "{$costDataId}.{$category}_cost";

        foreach($this->particulars as $particular)
        {
            $this->allColumns[] = "{$costDataId}.{$category}_column-{$particular['id']}";
        }

        $this->allColumns[] = "{$costDataId}.{$category}_percentage";
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

        $secondHeaderRow      = $this->getNextRow($firstRow);
        $lastHeaderRow        = $this->getNextRow($secondHeaderRow);
        $variationOrderColumn = $this->getPreviousColumn($this->currentCol,2);
        $remarksColumn        = $this->getPreviousColumn($this->currentCol,1);

        $this->activeSheet->mergeCells("{$variationOrderColumn}{$secondHeaderRow}:{$variationOrderColumn}{$lastHeaderRow}");
        $this->activeSheet->mergeCells("{$remarksColumn}{$secondHeaderRow}:{$remarksColumn}{$lastHeaderRow}");

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

            $variationOrderColumn = $this->getPreviousColumn($this->currentCol,2);
            $remarksColumn        = $this->getPreviousColumn($this->currentCol,1);

            $this->activeSheet->mergeCells("{$variationOrderColumn}{$secondHeaderRow}:{$variationOrderColumn}{$lastHeaderRow}");
            $this->activeSheet->mergeCells("{$remarksColumn}{$secondHeaderRow}:{$remarksColumn}{$lastHeaderRow}");
        }

        $this->currentRow++;

        //Set header styling
        $this->activeSheet->getStyle($this->firstCol . $firstRow . ':' . $this->lastCol . $firstRow)->applyFromArray($this->getColumnHeaderStyle());

        // For merged header rows
        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    }

    protected function getItemSums()
    {
        $itemIds = array_column($this->items, 'id');

        $this->sums[$this->costData->id] = CostDataItemTable::getItemSum($this->costData, $itemIds);

        foreach($this->arrayOfBenchmarkCostData as $costData)
        {
            $this->sums[$costData->id] = CostDataItemTable::getItemSum($costData, $itemIds);
        }
    }

    protected function getItemValues(CostData $costData)
    {
        $itemIds = array_column($this->items, 'id');

        return CostDataItemTable::getWorkCategoryValues($costData, $itemIds);
    }

    protected function getColumnValues()
    {
        $this->items = Utilities::setAttributeAsKey($this->items, 'id', false);

        $this->itemValues[$this->costData->id] = $this->getItemValues($this->costData);

        foreach($this->arrayOfBenchmarkCostData as $costData)
        {
            $this->allCostDataValues[ $costData->id ] = array();

            $values = $this->itemValues[$costData->id] = $this->getItemValues($costData);

            foreach($this->items as $itemId => $item)
            {
                $this->allCostDataValues[ $costData->id ][ $itemId ] = $values[ $itemId ];
                $this->allCostDataValues[ $costData->id ][ $itemId ]['approved_percentage'] = Utilities::percent($values[$itemId]['approved_cost'], $this->sums[$costData->id]['approved_sum']);
                $this->allCostDataValues[ $costData->id ][ $itemId ]['awarded_percentage'] = Utilities::percent($values[$itemId]['awarded_cost'], $this->sums[$costData->id]['awarded_sum']);
                $this->allCostDataValues[ $costData->id ][ $itemId ]['adjusted_percentage'] = Utilities::percent($values[$itemId]['adjusted_cost'], $this->sums[$costData->id]['adjusted_sum']);
                $this->allCostDataValues[ $costData->id ][ $itemId ]['remarks'] = $this->itemValues[$costData->id][ $itemId ]['remarks'];
            }
        }

        foreach($this->itemValues[$this->costData->id] as $itemId => $itemValues)
        {
            $this->itemValues[$this->costData->id][ $itemId ]['description'] = $this->items[ $itemId ]['description'];
            $this->itemValues[$this->costData->id][ $itemId ]['approved_percentage'] = Utilities::percent($itemValues['approved_cost'], $this->sums[$this->costData->id]['adjusted_sum']);
            $this->itemValues[$this->costData->id][ $itemId ]['awarded_percentage'] = Utilities::percent($itemValues['awarded_cost'], $this->sums[$this->costData->id]['adjusted_sum']);
            $this->itemValues[$this->costData->id][ $itemId ]['adjusted_percentage'] = Utilities::percent($itemValues['adjusted_cost'], $this->sums[$this->costData->id]['adjusted_sum']);
            $this->itemValues[$this->costData->id][ $itemId ]['remarks'] = $this->itemValues[$this->costData->id][ $itemId ]['remarks'];
        }

        $this->allCostDataValues[ $this->costData->id ] = $this->itemValues[$this->costData->id];
    }
}