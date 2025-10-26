<?php

class sfBudgetReportBillReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colDescription     = "B";
    public $colRevenue         = "C";
    public $colBudget          = "D";
    public $colSubConBudget    = "E";
    public $colSubConCost      = "F";
    public $colProgressRevenue = "G";
    public $colProgressCost    = "H";

    const COL_NAME_REVENUE          = 'Revenue';
    const COL_NAME_BUDGET           = 'Budget';
    const COL_NAME_SUB_CON_BUDGET   = 'Sub Con Budget';
    const COL_NAME_SUB_CON_COST     = 'Sub Con Cost';
    const COL_NAME_PROGRESS_REVENUE = 'Progress Revenue';
    const COL_NAME_PROGRESS_COST    = 'Progress Cost';

    protected $billHeader   = 'Budget Report';
    protected $subtitle;
    protected $topLeftTitle;

    protected $items = array();

    function __construct($project = null, $savePath = null, $filename = null)
    {
        $filename = ( $filename ) ? $filename : $project->title . '-' . $this->subtitle . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->subtitle = "{$project->title}";

        parent::__construct($project, $savePath, $filename, array());
    }

    public function setParameter(ProjectStructure $bill)
    {
        $this->bill = $bill;

        $this->topLeftTitle = $bill->title;
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colDescription;
        $this->lastCol    = $this->colDescription;

        self::increment($this->lastCol, 6);
    }

    protected function getItems()
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $subProjects         = ProjectStructureTable::getSubProjects($this->project);
        $billItemRates       = BudgetReport::getProjectItemRates($this->project);
        $subConBillItemRates = BudgetReport::getSubProjectItemRates($this->project);

        $stmt = $pdo->prepare("SELECT i.id as item_id, e.id as element_id, i.type
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b on b.id = e.project_structure_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p on p.id = b.root_id
            WHERE p.id = {$this->project->id}
            AND p.deleted_at IS NULL
            AND b.deleted_at IS NULL
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            AND i.project_revision_deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT e.id, e.description, e.priority
            FROM " . BillElementTable::getInstance()->getTableName() . " e
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b on b.id = e.project_structure_id
            WHERE b.id = {$this->bill->id}
            AND b.deleted_at IS NULL
            AND e.deleted_at IS NULL
            ORDER BY e.priority ASC;
        ");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array();

        foreach($elements as $elementKey => $element)
        {
            $element['revenue']             = 0;
            $element['budget']              = 0;
            $element['sub_con_budget']      = 0;
            $element['sub_con_cost']        = 0;
            $element['progress_revenue']    = 0;
            $element['progress_cost']       = 0;

            foreach($billItems as $billItemKey => $billItem)
            {
                $billItemId = $billItem['item_id'];
                $elementId  = $billItem['element_id'];

                if( $elementId != $element['id'] ) continue;

                $element['revenue'] += $billItemRates[ $billItemId ]['revenue'];
                $element['budget'] += $billItemRates[ $billItemId ]['budget'];
                $element['sub_con_budget'] += $billItemRates[ $billItemId ]['sub_con_budget'];
                $element['sub_con_cost'] += $billItemRates[ $billItemId ]['sub_con_cost'];
                $element['progress_revenue'] += $billItemRates[ $billItemId ]['progress_revenue'];
                $element['progress_cost'] += $billItemRates[ $billItemId ]['progress_cost'];

                foreach($subProjects as $subProject)
                {
                    if( ! array_key_exists($billItemId, $subConBillItemRates) || ! array_key_exists($subProject->id, $subConBillItemRates[ $billItemId ]) ) continue;

                    $element['revenue'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['revenue'];
                    $element['budget'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['budget'];
                    $element['sub_con_budget'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['sub_con_budget'];
                    $element['sub_con_cost'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['sub_con_cost'];
                    $element['progress_revenue'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['progress_revenue'];
                    $element['progress_cost'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['progress_cost'];
                }

                unset( $billItems[ $billItemKey ] );
            }

            $data[] = $element;

            unset( $elements[$elementKey] );
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

        $value = $item['budget'];

        if( $value == 0 ) $value = null;

        parent::setValue($this->colBudget, $value);

        $value = $item['sub_con_budget'];

        if( $value == 0 ) $value = null;

        parent::setValue($this->colSubConBudget, $value);

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
                SELf::COL_NAME_BUDGET,
                SELf::COL_NAME_SUB_CON_BUDGET,
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