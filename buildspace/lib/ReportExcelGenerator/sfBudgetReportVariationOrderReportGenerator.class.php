<?php

class sfBudgetReportVariationOrderReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colDescription     = "B";
    public $colRevenue         = "C";
    public $colSubConCost      = "D";
    public $colProgressRevenue = "E";
    public $colProgressCost    = "F";

    const COL_NAME_REVENUE          = 'Revenue';
    const COL_NAME_SUB_CON_COST     = 'Sub Con Cost';
    const COL_NAME_PROGRESS_REVENUE = 'Progress Revenue';
    const COL_NAME_PROGRESS_COST    = 'Progress Cost';

    protected $billHeader   = 'Budget Report';
    protected $subtitle;
    protected $topLeftTitle = 'Variation Orders';

    protected $items = array();

    function __construct($project = null, $savePath = null, $filename = null)
    {
        $filename = ( $filename ) ? $filename : $project->title . '-' . $this->subtitle . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->subtitle = "{$project->title}";

        parent::__construct($project, $savePath, $filename, array());
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colDescription;
        $this->lastCol    = $this->colDescription;

        self::increment($this->lastCol, 4);
    }

    protected function getItems()
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $subProjects     = ProjectStructureTable::getSubProjects($this->project);
        $itemRates       = BudgetReport::getProjectVariationOrderItemRates($this->project);
        $subConItemRates = BudgetReport::getSubProjectVariationOrderItemRates($this->project);

        $stmt = $pdo->prepare("SELECT i.id as item_id, vo.id as variation_order_id, i.type
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo on vo.id = i.variation_order_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p on p.id = vo.project_structure_id
            WHERE p.id = {$this->project->id}
            AND vo.is_approved = true
            AND p.deleted_at IS NULL
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT vo.id, vo.description, vo.priority
            FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p on p.id = vo.project_structure_id
            WHERE p.id = {$this->project->id}
            AND vo.is_approved = true
            AND p.deleted_at IS NULL
            AND vo.deleted_at IS NULL
            ORDER BY vo.priority ASC;
        ");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array();

        foreach($records as $recordKey => $record)
        {
            $record['revenue']             = 0;
            $record['sub_con_cost']        = 0;
            $record['progress_revenue']    = 0;
            $record['progress_cost']       = 0;

            foreach($items as $itemKey => $item)
            {
                $itemId = $item['item_id'];
                $variationOrderId  = $item['variation_order_id'];

                if( $variationOrderId != $record['id'] ) continue;

                $record['revenue'] += $itemRates[ $itemId ]['revenue'] ?? 0;
                $record['sub_con_cost'] += $itemRates[ $itemId ]['sub_con_cost'] ?? 0;
                $record['progress_revenue'] += $itemRates[ $itemId ]['progress_revenue'] ?? 0;
                $record['progress_cost'] += $itemRates[ $itemId ]['progress_cost'] ?? 0;

                foreach($subProjects as $subProject)
                {
                    if( ! array_key_exists($itemId, $subConItemRates) || ! array_key_exists($subProject->id, $subConItemRates[ $itemId ]) ) continue;

                    $record['revenue'] += $subConItemRates[ $itemId ][ $subProject->id ]['revenue'];
                    $record['sub_con_cost'] += $subConItemRates[ $itemId ][ $subProject->id ]['sub_con_cost'];
                    $record['progress_revenue'] += $subConItemRates[ $itemId ][ $subProject->id ]['progress_revenue'];
                    $record['progress_cost'] += $subConItemRates[ $itemId ][ $subProject->id ]['progress_cost'];
                }

                unset( $items[ $itemKey ] );
            }

            $data[] = $record;

            unset( $records[$recordKey] );
        }

        $this->items = $data;
    }

    protected function processItem($item)
    {
        $this->newLine();
        $this->newLine();

        $this->currentCol = $this->colDescription;
        $this->activeSheet->setCellValue($this->currentCol . $this->currentRow, $item['description']);
        $this->setItemStyle();

        $value = $item['revenue'];

        if( $value == 0 ) $value = null;

        parent::setValue($this->colRevenue, $value);

        $value = $item['sub_con_cost'];

        if( $value == 0 ) $value = null;

        parent::setValue($this->colSubConCost, $value);

        $value = $item['progress_revenue'];

        if( $value == 0 ) $value = null;

        parent::setValue($this->colProgressRevenue, $value);

        $value = $item['progress_cost'];

        if( $value == 0 ) $value = null;

        parent::setValue($this->colProgressCost, $value);
    }

    protected function processRows()
    {
        $this->getItems();

        $this->itemType = null; // For description row styling.

        foreach($this->items as $item)
        {
            $this->processItem($item);
        }

        $this->newLine(true);
    }

    public function generateReport()
    {
        $this->startBillCounter();
        $this->createHeader();
        $this->setBillHeader($this->billHeader, $this->topLeftTitle, $this->subtitle);
        $this->processRows();
        $this->generateExcelFile();
    }

    public function createHeader($new = false)
    {
        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);

        $this->currentRow++;
        $firstRow = $this->currentRow;

        $this->activeSheet->setCellValue($this->colDescription . $firstRow, self::COL_NAME_DESCRIPTION);

        $this->currentCol = $this->colDescription;

        $this->currentCol++;

        $headerInfo = $this->addHeaderColumns(
            array(
                SELf::COL_NAME_REVENUE,
                SELf::COL_NAME_SUB_CON_COST,
                SELf::COL_NAME_PROGRESS_COST,
                SELf::COL_NAME_PROGRESS_REVENUE,
            ), $this->currentCol, $firstRow);

        //Set header styling
        $this->activeSheet->getStyle($this->firstCol . $firstRow . ':' . $this->lastCol . $firstRow)->applyFromArray($this->getColumnHeaderStyle());

        // For merged header rows
        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    }
}