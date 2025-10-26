<?php

class sfCostDataProjectOverallCostingComparisonExcelReportGenerator extends sfCostDataStandardComparisonExcelReportGenerator {

    const ROW_TYPE_PROVISIONAL_SUM_TEXT = 'Provisional Sum';

    protected $summaryValues = array();
    protected $subtitle      = 'Project Overall Costing';

    protected $overallProjectCostingParticularSummary = array();
    protected $costComparisonParticulars              = array();

    protected function getItemValues(CostData $costData)
    {
        $itemIds = array_column($this->items, 'id');

        return CostDataItemTable::getOverallCostingItemValues($costData, $itemIds);
    }

    protected function getColumnValues()
    {
        parent::getColumnValues();

        $itemSums = $this->getTotalSums($this->costData);

        foreach($this->allCostDataValues[$this->costData->id] as $itemId => $values)
        {
            $this->allCostDataValues[$this->costData->id][$itemId]['approved_percentage'] = Utilities::percent($values['approved_cost'], $itemSums['total_approved_sum']);
            $this->allCostDataValues[$this->costData->id][$itemId]['awarded_percentage'] = Utilities::percent($values['awarded_cost'], $itemSums['total_awarded_sum']);
            $this->allCostDataValues[$this->costData->id][$itemId]['adjusted_percentage'] = Utilities::percent($values['adjusted_cost'], $itemSums['total_adjusted_sum']);
            $this->allCostDataValues[$this->costData->id][$itemId]['remarks'] = $this->itemValues[$this->costData->id][ $itemId ]['remarks'];
        }

        foreach($this->arrayOfBenchmarkCostData as $costData)
        {
            $itemSums = $this->getTotalSums($costData);

            foreach($this->allCostDataValues[$costData->id] as $itemId => $values)
            {
                $this->allCostDataValues[$costData->id][$itemId]['approved_percentage'] = Utilities::percent($values['approved_cost'], $itemSums['total_approved_sum']);
                $this->allCostDataValues[$costData->id][$itemId]['awarded_percentage'] = Utilities::percent($values['awarded_cost'], $itemSums['total_awarded_sum']);
                $this->allCostDataValues[$costData->id][$itemId]['adjusted_percentage'] = Utilities::percent($values['adjusted_cost'], $itemSums['total_adjusted_sum']);
                $this->allCostDataValues[$costData->id][$itemId]['remarks'] = $this->itemValues[$costData->id][ $itemId ]['remarks'];
            }
        }
    }

    protected function getTotalSums(CostData $costData)
    {
        $itemIds = array_column($this->items, 'id');

        $itemSums            = CostDataItemTable::getItemSum($costData, $itemIds);
        $provisionalSumTotal = CostDataProvisionalSumItemTable::getTotalSum($costData);

        $totalApprovedSum       = $itemSums['approved_sum'] + $provisionalSumTotal['approved_sum'];
        $totalAwardedSum        = $itemSums['awarded_sum'] + $provisionalSumTotal['awarded_sum'];
        $totalAdjustedSum       = $itemSums['adjusted_sum'] + $provisionalSumTotal['adjusted_sum'];
        $totalVariationOrderSum = $itemSums['variation_order_sum'] + $provisionalSumTotal['variation_order_sum'];

        return array(
            'total_approved_sum'       => $totalApprovedSum,
            'total_awarded_sum'        => $totalAwardedSum,
            'total_adjusted_sum'       => $totalAdjustedSum,
            'total_variation_order_sum'=> $totalVariationOrderSum,
            'approved_sum'        => $itemSums['approved_sum'],
            'awarded_sum'         => $itemSums['awarded_sum'],
            'adjusted_sum'        => $itemSums['adjusted_sum'],
            'variation_order_sum' => $itemSums['variation_order_sum'],
            'approved_provisional_sum' => $provisionalSumTotal['approved_sum'],
            'awarded_provisional_sum'  => $provisionalSumTotal['awarded_sum'],
            'adjusted_provisional_sum' => $provisionalSumTotal['adjusted_sum'],
            'variation_order_provisional_sum' => $provisionalSumTotal['variation_order_sum'],
        );
    }

    protected function defineColumnStructure()
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.description, COALESCE(uom.symbol, '') as uom_symbol, COALESCE(cdp.value, 0) AS value
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON uom.id = p.uom_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            WHERE p.master_cost_data_id = {$this->masterCostData->id}
            AND cd.id = {$this->costData->id}
            AND p.is_used_for_cost_comparison = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $this->costComparisonParticulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->allColumns[] = "{$this->costData->id}.description";

        $this->allColumns[] = "{$this->costData->id}.approved_cost";

        foreach($this->costComparisonParticulars as $particular)
        {
            $this->allColumns[] = "{$this->costData->id}.approved-{$particular['id']}";
        }

        $this->allColumns[] = "{$this->costData->id}.approved_percentage";
        $this->allColumns[] = "{$this->costData->id}.awarded_cost";

        foreach($this->costComparisonParticulars as $particular)
        {
            $this->allColumns[] = "{$this->costData->id}.awarded-{$particular['id']}";
        }

        $this->allColumns[] = "{$this->costData->id}.awarded_percentage";
        $this->allColumns[] = "{$this->costData->id}.adjusted_cost";

        foreach($this->costComparisonParticulars as $particular)
        {
            $this->allColumns[] = "{$this->costData->id}.adjusted-{$particular['id']}";
        }

        $this->allColumns[] = "{$this->costData->id}.adjusted_percentage";
        $this->allColumns[] = "{$this->costData->id}.variation_order_cost";
        $this->allColumns[] = "{$this->costData->id}.remarks";

        foreach($this->arrayOfBenchmarkCostData as $costData)
        {
            $this->allColumns[] = "{$costData->id}.approved_cost";

            foreach($this->costComparisonParticulars as $particular)
            {
                $this->allColumns[] = "{$costData->id}.approved-{$particular['id']}";
            }

            $this->allColumns[] = "{$costData->id}.approved_percentage";
            $this->allColumns[] = "{$costData->id}.awarded_cost";

            foreach($this->costComparisonParticulars as $particular)
            {
                $this->allColumns[] = "{$costData->id}.awarded-{$particular['id']}";
            }

            $this->allColumns[] = "{$costData->id}.awarded_percentage";
            $this->allColumns[] = "{$costData->id}.adjusted_cost";

            foreach($this->costComparisonParticulars as $particular)
            {
                $this->allColumns[] = "{$costData->id}.adjusted-{$particular['id']}";
            }

            $this->allColumns[] = "{$costData->id}.adjusted_percentage";
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

        $budgetColumn = self::COL_NAME_BUDGET;
        if($this->costData->approved_date) $budgetColumn .= " (" . date('d-m-Y', strtotime($this->costData->approved_date)) . ")";
        $contractSumColumn = self::COL_NAME_CONTRACT_SUM;
        if($this->costData->awarded_date) $contractSumColumn .= " (" . date('d-m-Y', strtotime($this->costData->awarded_date)) . ")";
        $adjustedSumColumn = self::COL_NAME_ADJUSTED_SUM;
        if($this->costData->adjusted_date) $adjustedSumColumn .= " (" . date('d-m-Y', strtotime($this->costData->adjusted_date)) . ")";

        $costingColumns = array(self::COL_NAME_AMOUNT, self::COL_NAME_PERCENT);

        $particularColumnHeaders = [];

        foreach($this->costComparisonParticulars as $particular)
        {
            $particularColumnHeaders[] = self::HEADER_TEXT_COST.'/'.$particular['uom_symbol'];
        }

        array_splice($costingColumns, 1, 0, $particularColumnHeaders);

        $headerInfo = $this->addHeaderColumns(
            array($this->costData->name =>
                array(
                    $budgetColumn => $costingColumns,
                    $contractSumColumn => $costingColumns,
                    $adjustedSumColumn => $costingColumns,
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
                        $budgetColumn => $costingColumns,
                        $contractSumColumn => $costingColumns,
                        $adjustedSumColumn => $costingColumns,
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

    protected function addProvisionalSumItem()
    {
        $this->items = array( 'provisional_sum' => array() ) + $this->items;
    }

    protected function setProjectOverallCostingValues()
    {
        $allCostData   = $this->arrayOfBenchmarkCostData;
        $allCostData[] = $this->costData;

        $allCostDataIds = [];
        foreach($allCostData as $costData)
        {
            $allCostDataIds[] = $costData->id;
        }

        $stmt = $this->pdo->prepare("
            SELECT p.id, cd.id as cost_data_id, COALESCE(cdp.value, 0) AS value 
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
            LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
            WHERE p.master_cost_data_id = {$this->masterCostData->id}
            AND cd.id IN (".implode(',', $allCostDataIds).")
            AND p.is_summary_displayed = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $summaryParticularValueRecords = $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

        $particularValues = [];

        foreach($summaryParticularValueRecords as $particularId => $costDataValues)
        {
            $particularValues[$particularId] = [];

            foreach($costDataValues as $costDataValue)
            {
                $particularValues[$particularId][$costDataValue['cost_data_id']] = $costDataValue['value'];
            }
        }

        foreach($allCostData as $costData)
        {
            $sums = $this->getTotalSums($costData);

            $nodelessItemRemarks = CostDataNodelessItemRemarkTable::getRemarks($costData->id);

            $this->allCostDataValues[ $costData->id ]['provisional_sum'] = array(
                'id'                   => 'provisional_sum',
                'description'          => self::ROW_TYPE_PROVISIONAL_SUM_TEXT,
                'approved_cost'        => $sums['approved_provisional_sum'],
                'approved_percentage'  => Utilities::percent($sums['approved_provisional_sum'], $sums['total_approved_sum']),
                'awarded_cost'         => $sums['awarded_provisional_sum'],
                'awarded_percentage'   => Utilities::percent($sums['awarded_provisional_sum'], $sums['total_awarded_sum']),
                'adjusted_cost'        => $sums['adjusted_provisional_sum'],
                'adjusted_percentage'  => Utilities::percent($sums['adjusted_provisional_sum'], $sums['total_adjusted_sum']),
                'variation_order_cost' => $sums['variation_order_provisional_sum'],
                'remarks'              => $nodelessItemRemarks[MasterCostData::ITEM_TYPE_PROVISIONAL_SUM],
            );

            foreach($this->costComparisonParticulars as $particular)
            {
                $this->allCostDataValues[ $costData->id ]['provisional_sum']["approved-{$particular['id']}"] = 0;
                $this->allCostDataValues[ $costData->id ]['provisional_sum']["awarded-{$particular['id']}"] = 0;
                $this->allCostDataValues[ $costData->id ]['provisional_sum']["adjusted-{$particular['id']}"] = 0;
            }

            $overallProjectCostingParticularSummary = CostDataParticularTable::getOverallProjectCostingSummary($costData);

            foreach($particularValues as $particularId => $costDataParticularValues)
            {
                $this->summaryValues[$particularId][ $costData->id ] = array(
                    'approved_cost'        => Utilities::divide($overallProjectCostingParticularSummary[$particularId]['approved_cost'], $costDataParticularValues[$costData->id]),
                    'awarded_cost'         => Utilities::divide($overallProjectCostingParticularSummary[$particularId]['awarded_cost'], $costDataParticularValues[$costData->id]),
                    'adjusted_cost'        => Utilities::divide($overallProjectCostingParticularSummary[$particularId]['adjusted_cost'], $costDataParticularValues[$costData->id]),
                    'variation_order_cost' => Utilities::divide($overallProjectCostingParticularSummary[$particularId]['variation_order_cost'], $costDataParticularValues[$costData->id]),
                );
            }
        }
    }

    public function generateReport()
    {
        $this->defineColumnStructure();
        $this->getItemList();
        $this->getColumnValues();
        $this->startBillCounter();
        $this->createHeader();
        $this->setProjectOverallCostingValues();
        $this->addProvisionalSumItem();
        $this->setBillHeader($this->billHeader, $this->topLeftTitle, $this->subtitle);
        $this->processRows();
        $this->createFooter(true);
        $this->generateExcelFile();
    }

    public function printGrandTotal()
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.description, p.summary_description
            FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
            WHERE p.master_cost_data_id = {$this->masterCostData->id}
            AND p.is_summary_displayed = TRUE
            AND p.deleted_at IS NULL
            ORDER BY p.priority ASC
            ");

        $stmt->execute();

        $particulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $firstGrandTotalRow = $this->currentRow;

        $this->printTotalText();

        $this->printGrandTotalValue($this->getNewLineStyle());

        $this->currentRow++;

        foreach($particulars as $key => $particular)
        {
            $summaryDescription = empty($particular['summary_description']) ? "Total Cost/{$particular['description']}" : $particular['summary_description'];

            $this->printSummaryText($summaryDescription . ":");

            $bottom = ($key == count($particulars) - 1);

            $this->printSummaryRow($particular['id'], $this->getNewLineStyle($bottom));

            $this->currentRow++;
        }

        $this->activeSheet->getStyle($this->colDescription . $firstGrandTotalRow . ':' . $this->colDescription . $this->currentRow)->applyFromArray($this->getTotalStyle());

        $this->currentRow++;
    }

    public function printSummaryText($title)
    {
        $cell = $this->colDescription . $this->currentRow;

        $this->activeSheet->setCellValue($cell, $title);

        $this->activeSheet->getStyle($cell)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ))
        );
    }

    protected function printSummaryRow($rowName, $style)
    {
        $this->currentCol = $this->colDescription;
        $this->currentCol++;
        $firstTotalCol = $this->currentCol;

        $allColumns = $this->allColumns;

        // Remove description column.
        array_shift($allColumns);

        foreach($allColumns as $column)
        {
            $columnInfo = explode('.', $column);
            $costDataId = $columnInfo[0];
            $columnName = $columnInfo[1];

            $value = $this->summaryValues[ $rowName ][ $costDataId ][ $columnName ] ?? 0;

            if( $value == 0 ) $value = null;

            parent::setValue($this->currentCol, $value);
            $this->currentCol++;
        }

        $this->activeSheet->getStyle($firstTotalCol . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($style);
    }

}