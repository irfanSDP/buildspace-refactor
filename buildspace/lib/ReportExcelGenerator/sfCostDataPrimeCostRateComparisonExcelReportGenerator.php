<?php

class sfCostDataPrimeCostRateComparisonExcelReportGenerator extends sfCostDataStandardComparisonExcelReportGenerator {

    protected $subtitle         = 'Project Rates Analysis';
    protected $level            = 1;
    protected $showBrandColumns = false;
    protected $firstTotalColumn;

    protected $particulars = [];
    protected $particularValues = [];

    public $colUnit = "C";

    const COL_NAME_TOTAL_UNITS          = 'Total Units';
    const COL_NAME_AMOUNT_PER_UNIT      = 'Amount/Unit';
    const COL_NAME_PRIME_COST_UNIT_RATE = 'PC Unit Rate';
    const COL_NAME_BRAND                = 'Brand';

    const ROW_TOTAL_AVERAGE_COST_PER_TYPE = 'Avg Cost/Type';

    public function setParameters(array $benchMarkCostDataIds, $parentItemId)
    {
        foreach($benchMarkCostDataIds as $id)
        {
            $this->arrayOfBenchmarkCostData[] = Doctrine_Core::getTable('CostData')->find($id);
        }

        $this->parentItemId = $parentItemId;

        if( $parentItem = Doctrine_Core::getTable('MasterCostDataPrimeCostRate')->find($this->parentItemId) ) $this->topLeftTitle = $parentItem->description;
    }

    protected function defineColumnStructure()
    {
        $this->allColumns[] = "{$this->costData->id}.description";

        if( $this->level == 3 ) $this->allColumns[] = "{$this->costData->id}.uom_symbol";

        $this->allColumns[] = "{$this->costData->id}.units";
        $this->addAdditionalColumnGroupsToStructure($this->costData->id, 'approved');
        $this->addAdditionalColumnGroupsToStructure($this->costData->id, 'awarded');

        foreach($this->arrayOfBenchmarkCostData as $costData)
        {
            $this->allColumns[] = "{$costData->id}.units";
            $this->addAdditionalColumnGroupsToStructure($costData->id, 'approved');
            $this->addAdditionalColumnGroupsToStructure($costData->id, 'awarded');
        }
    }

    public function setLevel($level)
    {
        $this->level = $level;

        if( $level == 3 ) $this->showBrandColumns = true;
    }

    protected function addAdditionalColumnGroupsToStructure($costDataId, $category)
    {
        $this->allColumns[] = "{$costDataId}.{$category}_value";
        $this->allColumns[] = "{$costDataId}.{$category}_total";

        if( $this->showBrandColumns ) $this->allColumns[] = "{$costDataId}.{$category}_brand";
    }

    protected function getItemValues(CostData $costData)
    {
        $itemIds = array_column($this->items, 'id');

        return CostDataPrimeCostRateTable::getRecordValues($costData, $itemIds);
    }

    protected function getItemList()
    {
        $this->items = CostDataPrimeCostRateTable::getItemList($this->costData, $this->parentItemId);
    }

    public function createHeader($new = false)
    {
        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);

        if( $this->level == 3 ) $this->activeSheet->getColumnDimension($this->colUnit)->setWidth(12);

        $this->currentRow++;
        $firstRow = $this->currentRow;

        // Account for merged header rows
        $this->currentRow++;
        $this->currentRow++;

        $this->activeSheet->setCellValue($this->colDescription . $firstRow, self::COL_NAME_DESCRIPTION);
        $this->mergeRows($this->colDescription, $firstRow, 2);

        $this->currentCol = $this->colDescription;

        $this->currentCol++;

        if( $this->level == 3 )
        {
            $this->activeSheet->setCellValue($this->colUnit . $firstRow, self::COL_NAME_UNIT);
            $this->mergeRows($this->colUnit, $firstRow, 2);

            $this->currentCol = $this->colUnit;

            $this->currentCol++;
        }

        $groupColumns = array(
            $this->showBrandColumns ? self::COL_NAME_PRIME_COST_UNIT_RATE : self::COL_NAME_AMOUNT_PER_UNIT,
            self::COL_NAME_TOTAL_AMOUNT,
        );

        if($this->showBrandColumns) $groupColumns[] = self::COL_NAME_BRAND;

        $budgetColumn = self::COL_NAME_BUDGET;
        if($this->costData->approved_date) $budgetColumn .= " (" . date('d-m-Y', strtotime($this->costData->approved_date)) . ")";
        $contractSumColumn = self::COL_NAME_CONTRACT_SUM;
        if($this->costData->awarded_date) $contractSumColumn .= " (" . date('d-m-Y', strtotime($this->costData->awarded_date)) . ")";

        $headerInfo = $this->addHeaderColumns(
            array(
                $this->costData->name => array(
                    $this->showBrandColumns ? self::COL_NAME_QTY : self::COL_NAME_TOTAL_UNITS,
                    $budgetColumn => $groupColumns,
                    $contractSumColumn => $groupColumns,
                )
            ), $this->currentCol, $firstRow);

        $secondHeaderRow = $firstRow;
        $secondHeaderRow++;

        $this->mergeRows($this->currentCol, $secondHeaderRow);

        $this->increment($this->currentCol, $headerInfo['numberOfColumns']);

        foreach($this->arrayOfBenchmarkCostData as $costData)
        {
            $budgetColumn = self::COL_NAME_BUDGET;
            if($costData->approved_date) $budgetColumn .= " (" . date('d-m-Y', strtotime($costData->approved_date)) . ")";
            $contractSumColumn = self::COL_NAME_CONTRACT_SUM;
            if($costData->awarded_date) $contractSumColumn .= " (" . date('d-m-Y', strtotime($costData->awarded_date)) . ")";

            $headerInfo = $this->addHeaderColumns(
                array(
                    $costData->name => array(
                        $this->showBrandColumns ? self::COL_NAME_QTY : self::COL_NAME_TOTAL_UNITS,
                        $budgetColumn => $groupColumns,
                        $contractSumColumn => $groupColumns,
                    )
                ), $this->currentCol, $firstRow);

            $this->mergeRows($this->currentCol, $secondHeaderRow);

            $this->increment($this->currentCol, $headerInfo['numberOfColumns']);
        }

        //Set header styling
        $this->activeSheet->getStyle($this->firstCol . $firstRow . ':' . $this->lastCol . $firstRow)->applyFromArray($this->getColumnHeaderStyle());

        // For merged header rows
        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    }

    protected function addCostDataHeaderGroup($firstRow, $label)
    {
        $groupStartCol = ++$this->currentCol;

        $this->activeSheet->setCellValue($this->currentCol . $firstRow, $label);

        $this->activeSheet->setCellValue($this->currentCol . self::getNextRow($firstRow), self::COL_NAME_AMOUNT);

        $this->activeSheet->getColumnDimension($this->currentCol)->setWidth(15);

        if( $this->showBrandColumns )
        {
            $this->currentCol++;

            $this->activeSheet->setCellValue($this->currentCol . $firstRow, $label);

            $this->activeSheet->setCellValue($this->currentCol . self::getNextRow($firstRow), self::COL_NAME_BRAND);

            $this->activeSheet->getColumnDimension($this->currentCol)->setWidth(15);

            $this->activeSheet->mergeCells("{$groupStartCol}{$firstRow}:{$this->currentCol}{$firstRow}");
        }
    }

    public function printGrandTotal()
    {
        $firstGrandTotalRow = $this->currentRow;

        $this->firstTotalColumn = ( $this->level != 3 ) ? $this->colDescription : $this->colUnit;
        $this->firstTotalColumn++;

        $this->printSummaryText(self::ROW_TOTAL . ":");

        $this->printSummaryTotalRow($this->getNewLineStyle(( $this->level != 2 )));

        $this->currentRow++;

        if($this->level == 2)
        {
            $this->printSummaryText(self::ROW_TOTAL_AVERAGE_COST_PER_TYPE . ":");

            $this->printSummaryCostPerTypeRow($this->getNewLineStyle());

            $this->currentRow++;

            $this->calculateParticularValues();

            foreach($this->particulars as $key => $particular)
            {
                $text = empty($particular['summary_description']) ? "Total Cost/{$particular['description']}" : $particular['summary_description'];

                $this->printSummaryText("{$text}:");

                $isLastRow = ($key == count($this->particulars) - 1) ? true : false;

                $this->printParticularSummaryRow($particular['id'], $this->getNewLineStyle($isLastRow));

                if(!$isLastRow) $this->currentRow++;
            }
        }

        $this->activeSheet->getStyle($this->firstTotalColumn . $firstGrandTotalRow . ':' . $this->firstTotalColumn . $this->currentRow)->applyFromArray($this->getTotalStyle());

        $this->currentRow++;
    }

    public function printSummaryText($title)
    {
        $cell = $this->getPreviousColumn($this->firstTotalColumn) . $this->currentRow;

        $this->activeSheet->setCellValue($cell, $title);

        $this->activeSheet->getStyle($cell)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ))
        );
    }

    protected function printSummaryTotalRow($style)
    {
        $this->currentCol = $this->firstTotalColumn;

        $allColumns = $this->allColumns;

        // Remove description column.
        array_shift($allColumns);

        $relevantColumns = array(
            'approved_total',
            'awarded_total',
        );

        if( $this->level == 2 ) $relevantColumns[] = 'units';

        foreach($allColumns as $column)
        {
            $columnInfo = explode('.', $column);
            $costDataId = $columnInfo[0];
            $columnName = $columnInfo[1];

            if( ! in_array($columnName, $relevantColumns) )
            {
                $this->currentCol++;
                continue;
            }

            $value = $this->sums[ $costDataId ][ $columnName ] ?? 0;

            if( $value == 0 ) $value = null;

            parent::setValue($this->currentCol, $value);

            $this->currentCol++;
        }

        $this->activeSheet->getStyle($this->firstTotalColumn . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($style);
    }

    protected function printSummaryCostPerTypeRow($style)
    {
        $this->currentCol = $this->firstTotalColumn;

        $allColumns = $this->allColumns;

        // Remove description column.
        array_shift($allColumns);

        $relevantColumns = array(
            'approved_total',
            'awarded_total',
        );

        foreach($allColumns as $column)
        {
            $columnInfo = explode('.', $column);
            $costDataId = $columnInfo[0];
            $columnName = $columnInfo[1];

            if( ! in_array($columnName, $relevantColumns) )
            {
                $this->currentCol++;
                continue;
            }

            $value = $this->sums[ $costDataId ][ $columnName ] ?? 0;

            $value = Utilities::divide($value, $this->sums[ $costDataId ][ 'units' ]);

            if( $value == 0 ) $value = null;

            parent::setValue($this->currentCol, $value);

            $this->currentCol++;
        }

        $this->activeSheet->getStyle($this->firstTotalColumn . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($style);
    }

    protected function printParticularSummaryRow($particularId, $style)
    {
        $this->currentCol = $this->firstTotalColumn;

        $allColumns = $this->allColumns;

        // Remove description column.
        array_shift($allColumns);

        $relevantColumns = array(
            'approved_total',
            'awarded_total',
        );

        foreach($allColumns as $column)
        {
            $columnInfo = explode('.', $column);
            $costDataId = $columnInfo[0];
            $columnName = $columnInfo[1];

            if( ! in_array($columnName, $relevantColumns) )
            {
                $this->currentCol++;
                continue;
            }

            $value = $this->sums[ $costDataId ][ $columnName ] ?? 0;

            $value = Utilities::divide($value, $this->particularValues[$particularId][$costDataId]);

            if( $value == 0 ) $value = null;

            parent::setValue($this->currentCol, $value);

            $this->currentCol++;
        }

        $this->activeSheet->getStyle($this->firstTotalColumn . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($style);
    }

    public function generateReport()
    {
        if( $this->showBrandColumns )
        {
            $this->textColumns[] = 'approved_brand';
            $this->textColumns[] = 'awarded_brand';
        }

        $this->defineColumnStructure();
        $this->getItemList();
        $this->getColumnValues();
        $this->startBillCounter();
        $this->createHeader();
        $this->setBillHeader($this->billHeader, $this->topLeftTitle, $this->subtitle);
        $this->processRows();
        $this->createFooter(true);
        $this->generateExcelFile();
    }

    protected function calculateParticularValues()
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.description, p.summary_description
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            WHERE p.master_cost_data_id = {$this->masterCostData->id}
            AND p.is_prime_cost_rate_summary_displayed = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $this->particulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->pdo->prepare("
            SELECT p.id, cd.id as cost_data_id, COALESCE(cdp.value, 0) AS value 
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            WHERE p.master_cost_data_id = {$this->masterCostData->id}
            AND p.is_prime_cost_rate_summary_displayed = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $particularValueRecords = $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

        $this->particularValues = [];

        foreach($particularValueRecords as $particularId => $costDataValues)
        {
            $this->particularValues[$particularId] = [];

            foreach($costDataValues as $costDataValue)
            {
                $this->particularValues[$particularId][$costDataValue['cost_data_id']] = $costDataValue['value'];
            }
        }
    }
}