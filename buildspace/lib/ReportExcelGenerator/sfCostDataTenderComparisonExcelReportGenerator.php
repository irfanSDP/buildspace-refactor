<?php

class sfCostDataTenderComparisonExcelReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colDescription = "B";

    const ROW_TOTAL = 'Total';
    const ROW_TOTAL_COST_PER_GROSS_FLOOR_AREA    = 'Total Cost/GFA';
    const ROW_TOTAL_COST_PER_NETT_FLOOR_AREA     = 'Total Cost/NFA';
    const ROW_AVERAGE_CONSTRUCTION_COST_PER_UNIT = 'Average Construction Cost/Unit';

    public $project;

    protected $title;
    protected $costData;
    protected $masterCostData;
    protected $parentItemId;
    protected $tenderCompanies                     = array();
    protected $items                               = array();
    protected $allColumns                          = array();
    protected $billHeader                          = 'Cost Data Tender Comparison Report';
    protected $topLeftTitle                        = 'Project Overall Costing';
    protected $subtitle                            = null;
    protected $itemValues                          = array();
    protected $sums                                = array();
    protected $addProjectOverallCostingSummaryRows = false;

    function __construct(CostData $costData = null, ProjectStructure $project, $savePath = null, $filename = null)
    {
        $filename = ( $filename ) ? $filename : $costData->name . '-' . $this->subtitle . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->costData       = $costData;
        $this->masterCostData = $costData->MasterCostData;
        $this->project        = $project;

        $this->subtitle = "{$this->project->title}";

        parent::__construct($project, $savePath, $filename, array());
    }

    public function setTitle($title = null)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Text' ));

        $invalidTitleChars = array( '[', ']', '*', '/', '\\', '?', ':' );

        if( $title )
        {
            return $this->activeSheet->setTitle(truncate_text(str_replace($invalidTitleChars, "_", $title)));
        }

        return $this->activeSheet->setTitle(truncate_text(str_replace($invalidTitleChars, "_", $this->costData->name . '-' . date('dmY H_i_s'))));
    }

    public function setParameters($parentItemId, $level)
    {
        $this->parentItemId = $parentItemId;

        if( $parentItem = Doctrine_Core::getTable('MasterCostDataItem')->find($this->parentItemId) ) $this->topLeftTitle = $parentItem->description;

        if( $level == MasterCostDataItem::ITEM_LEVEL_PROJECT_OVERALL_COSTING) $this->addProjectOverallCostingSummaryRows = true;
    }

    protected function getTenderCompanies()
    {
        $stmt = $this->pdo->prepare("SELECT c.id, c.name
            FROM " . TenderCompanyTable::getInstance()->getTableName() . " tc
            JOIN " . CompanyTable::getInstance()->getTableName() . " c on c.id = tc.company_id
            WHERE tc.project_structure_id = :project_structure_id");

        $stmt->execute(array(
            'project_structure_id' => $this->project->id
        ));

        $this->tenderCompanies = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    protected function getItemList()
    {
        $this->items = CostDataItemTable::getItemList($this->costData, $this->parentItemId);
    }

    protected function getItemValues()
    {
        $itemIds = array_column($this->items, 'id');

        $this->itemValues = TenderComparison::getCostDataItemTendererRates($this->costData, $itemIds, $this->project);
    }

    protected function defineColumnStructure()
    {
        $this->allColumns[] = "description";

        foreach($this->tenderCompanies as $id => $name)
        {
            $this->allColumns[] = "{$id}.amount";
        }
    }

    public function createHeader($new = false)
    {
        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);

        $this->activeSheet->setCellValue($this->colDescription . $this->currentRow, self::COL_NAME_DESCRIPTION);

        $this->currentCol = $this->colDescription;

        foreach($this->tenderCompanies as $id => $name)
        {
            $this->activeSheet->setCellValue(++$this->currentCol . $this->currentRow, $name);
        }

        $this->lastCol = $this->currentCol;

        //Set header styling
        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colDescription;
        $this->lastCol    = $this->colDescription;

        $numberOfColumnsToAdd = count($this->allColumns) - 1; // Minus the description column (start column is already the description column).

        self::increment($this->lastCol, $numberOfColumnsToAdd);
    }

    protected function processRows()
    {
        $this->itemType = null; // For description row styling.

        foreach($this->items as $item)
        {
            $this->newLine();
            $this->newLine();

            $this->currentCol = $this->colDescription;

            $this->activeSheet->setCellValue($this->currentCol . $this->currentRow, $item['description']);

            $this->setItemStyle();

            $this->currentCol++;

            foreach($this->tenderCompanies as $tenderCompanyId => $name)
            {
                $value = $this->itemValues[$item['id']][$tenderCompanyId] ?? 0;

                if( $value == 0 ) $value = null;

                parent::setValue($this->currentCol, $value);

                if( ! isset( $this->sums[ $tenderCompanyId ] ) ) $this->sums[ $tenderCompanyId ] = 0;

                $this->sums[ $tenderCompanyId ] += $value ?? 0;

                $this->currentCol++;
            }
        }
    }

    public function generateReport()
    {
        $this->getTenderCompanies();
        $this->getItemList();
        $this->getItemValues();
        $this->defineColumnStructure();
        $this->startBillCounter();
        $this->createHeader();
        $this->setBillHeader($this->billHeader, $this->topLeftTitle, $this->subtitle);
        $this->processRows();
        $this->createFooter(true);
        $this->generateExcelFile();
    }

    public function createFooter($printGrandTotal = false)
    {
        $this->newLine(true);

        $this->currentRow++;

        if( $printGrandTotal )
        {
            $this->printGrandTotal();
        }

        $this->currentRow += 2;
    }

    public function printGrandTotal()
    {
        $firstGrandTotalRow = $this->currentRow;

        $this->activeSheet->getStyle($this->colDescription . $this->currentRow)->applyFromArray($this->getTotalStyle());

        $this->printTotalText();

        $this->printGrandTotalValue($this->getNewLineStyle(!$this->addProjectOverallCostingSummaryRows));

        $this->currentRow++;

        if($this->addProjectOverallCostingSummaryRows)
        {
            $stmt = $this->pdo->prepare("
                SELECT p.id, p.description, p.summary_description, COALESCE(cdp.value, 0) AS value FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " p
                JOIN " . CostDataTable::getInstance()->getTableName() . " cd on cd.master_cost_data_id = p.master_cost_data_id
                LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " cdp on cdp.master_cost_data_particular_id = p.id AND cdp.cost_data_id = cd.id
                WHERE cd.id = {$this->costData->id}
                AND p.is_summary_displayed = TRUE
                AND p.deleted_at IS NULL
                ORDER BY p.priority ASC
                ");

            $stmt->execute();

            $summaryParticulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($summaryParticulars as $key => $particular)
            {
                $summaryDescription = empty($particular['summary_description']) ? "Total Cost/{$particular['description']}" : $particular['summary_description'];

                $this->printSummaryText($summaryDescription . ":");

                $bottom = ($key == count($summaryParticulars) - 1);

                $this->printSummaryRow($particular['value'], $this->getNewLineStyle($bottom));

                $this->currentRow++;
            }

            $this->activeSheet->getStyle($this->colDescription . $firstGrandTotalRow . ':' . $this->colDescription . $this->currentRow)->applyFromArray($this->getTotalStyle());

            $this->currentRow++;
        }
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

    protected function printSummaryRow($particularValue, $style)
    {
        $this->currentCol = $this->colDescription;
        $this->currentCol++;

        $firstTotalCol = $this->currentCol;

        foreach($this->tenderCompanies as $id => $name)
        {
            $value = $this->sums[ $id ] ?? 0;

            $value = Utilities::divide($value, $particularValue);

            if( $value == 0 ) $value = null;

            parent::setValue($this->currentCol, $value);
            $this->currentCol++;
        }

        $this->activeSheet->getStyle($firstTotalCol . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($style);
    }

    public function printGrandTotalValue($style)
    {
        $this->currentCol = $this->colDescription;
        $this->currentCol++;

        $firstTotalCol = $this->currentCol;

        foreach($this->tenderCompanies as $id => $name)
        {
            $value = $this->sums[ $id ] ?? 0;

            if( $value == 0 ) $value = null;

            parent::setValue($this->currentCol, $value);
            $this->currentCol++;
        }

        $this->activeSheet->getStyle($firstTotalCol . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($style);
    }

}