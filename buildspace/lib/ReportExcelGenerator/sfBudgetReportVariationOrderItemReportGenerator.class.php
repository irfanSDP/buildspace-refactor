<?php

class sfBudgetReportVariationOrderItemReportGenerator extends sfBuildspaceExcelReportGenerator {

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
    protected $topLeftTitle;

    protected $items = array();

    protected $variationOrder;

    function __construct($project = null, $savePath = null, $filename = null)
    {
        $filename = ( $filename ) ? $filename : $project->title . '-' . $this->subtitle . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->subtitle = "{$project->title}";

        parent::__construct($project, $savePath, $filename, array());
    }

    public function setParameter(VariationOrder $variationOrder)
    {
        $this->variationOrder = $variationOrder;

        $this->topLeftTitle = $variationOrder->description;
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

        $itemRates = BudgetReport::getProjectVariationOrderItemRates($this->project);

        $subConItemRates = BudgetReport::getSubProjectVariationOrderItemRates($this->project);

        $stmt = $pdo->prepare("SELECT i.id, i.description, uom.id AS uom_id, uom.symbol AS uom_symbol, i.type, i.lft, i.level
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo on vo.id = i.variation_order_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom on uom.id = i.uom_id
            WHERE vo.id = {$this->variationOrder->id}
            AND vo.is_approved = true
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subProjects = ProjectStructureTable::getSubProjects($this->project);

        $data = array();

        foreach($items as $key => $item)
        {
            $item['revenue']          = $itemRates[ $item['id'] ]['revenue'] ?? 0;
            $item['progress_revenue'] = $itemRates[ $item['id'] ]['progress_revenue'] ?? 0;
            $item['sub_con_cost']     = $itemRates[ $item['id'] ]['sub_con_cost'] ?? 0;
            $item['progress_cost']    = $itemRates[ $item['id'] ]['progress_cost'] ?? 0;

            if( $item['type'] == VariationOrderItem::TYPE_HEADER )
            {
                $data[] = $item;
                unset( $items[$key] );
                continue;
            }

            foreach($subProjects as $subProject)
            {
                if( ! array_key_exists($item['id'], $subConItemRates) || ! array_key_exists($subProject->id, $subConItemRates[ $item['id'] ]) ) continue;

                $item['sub_con_cost'] += $subConItemRates[ $item['id'] ][ $subProject->id ]['sub_con_cost'];
                $item['progress_cost'] += $subConItemRates[ $item['id'] ][ $subProject->id ]['progress_cost'];
            }

            $data[] = $item;

            unset( $items[$key] );
        }

        $this->items = $data;
    }

    protected function processItem($item)
    {
        $this->newLine();
        $this->newLine();

        $this->itemType = VariationOrderItemTable::getItemTypeText($item['type']);

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