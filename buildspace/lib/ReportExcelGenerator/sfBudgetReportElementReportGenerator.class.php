<?php

class sfBudgetReportElementReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colDescription      = "B";
    public $colContractQuantity = "C";
    public $colProgressQuantity = "D";
    public $colContractRate     = "E";
    public $colBudgetRate       = "F";
    public $colRevenue          = "G";
    public $colBudget           = "H";
    public $colSubConBudget     = "I";
    public $colSubConCost       = "J";
    public $colProgressRevenue  = "K";
    public $colProgressCost     = "L";

    const COL_NAME_CONTRACT_QUANTITY = 'Contract Qty';
    const COL_NAME_PROGRESS_QUANTITY = 'Progress Qty';
    const COL_NAME_CONTRACT_RATE     = 'Contract Rate';
    const COL_NAME_BUDGET_RATE       = 'Budget Rate';
    const COL_NAME_REVENUE           = 'Revenue';
    const COL_NAME_BUDGET            = 'Budget';
    const COL_NAME_SUB_CON_BUDGET    = 'Sub Con Budget';
    const COL_NAME_SUB_CON_COST      = 'Sub Con Cost';
    const COL_NAME_PROGRESS_REVENUE  = 'Progress Revenue';
    const COL_NAME_PROGRESS_COST     = 'Progress Cost';

    protected $billHeader   = 'Budget Report';
    protected $subtitle;
    protected $topLeftTitle;

    protected $items = array();
    protected $element;

    function __construct($project = null, $savePath = null, $filename = null)
    {
        $filename = ( $filename ) ? $filename : $project->title . '-' . $this->subtitle . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->subtitle = "{$project->title}";

        parent::__construct($project, $savePath, $filename, array());
    }

    public function setParameter(BillElement $element)
    {
        $this->element = $element;

        $this->topLeftTitle = $element->description;
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colDescription;
        $this->lastCol    = $this->colDescription;

        self::increment($this->lastCol, 10);
    }

    protected function getItems()
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $billItemRates = BudgetReport::getProjectItemRates($this->project);

        $subConBillItemRates = BudgetReport::getSubProjectItemRates($this->project);

        $stmt = $pdo->prepare("SELECT i.id, i.description, uom.id AS uom_id, uom.symbol AS uom_symbol, i.type, i.lft, i.level
            FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom on uom.id = i.uom_id
            WHERE e.id = {$this->element->id}
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            AND i.project_revision_deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level ASC;");

        $stmt->execute();

        $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subProjects = ProjectStructureTable::getSubProjects($this->project);

        $data = array();

        foreach($billItems as $key => $billItem)
        {
            $billItem['total_quantity']   = $billItemRates[ $billItem['id'] ]['total_quantity'];
            $billItem['up_to_date_qty']   = $billItemRates[ $billItem['id'] ]['up_to_date_qty'];
            $billItem['rate']             = $billItemRates[ $billItem['id'] ]['rate'];
            $billItem['budget_rate']      = $billItemRates[ $billItem['id'] ]['budget_rate'];
            $billItem['revenue']          = $billItemRates[ $billItem['id'] ]['revenue'];
            $billItem['budget']           = $billItemRates[ $billItem['id'] ]['budget'];
            $billItem['progress_revenue'] = $billItemRates[ $billItem['id'] ]['progress_revenue'];
            $billItem['sub_con_quantity'] = $billItemRates[ $billItem['id'] ]['sub_con_quantity'];
            $billItem['sub_con_rate']     = $billItemRates[ $billItem['id'] ]['sub_con_rate'];
            $billItem['sub_con_budget']   = $billItemRates[ $billItem['id'] ]['sub_con_budget'];
            $billItem['sub_con_cost']     = $billItemRates[ $billItem['id'] ]['sub_con_cost'];
            $billItem['progress_cost']    = $billItemRates[ $billItem['id'] ]['progress_cost'];

            if( $billItem['type'] == BillItem::TYPE_HEADER || $billItem['type'] == BillItem::TYPE_HEADER_N )
            {
                $data[] = $billItem;
                unset( $billItems[$key] );
                continue;
            }

            foreach($subProjects as $subProject)
            {
                if( ! array_key_exists($billItem['id'], $subConBillItemRates) || ! array_key_exists($subProject->id, $subConBillItemRates[ $billItem['id'] ]) ) continue;

                $billItem['sub_con_budget'] += $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['sub_con_budget'];
                $billItem['sub_con_cost'] += $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['sub_con_cost'];
                $billItem['progress_cost'] += $subConBillItemRates[ $billItem['id'] ][ $subProject->id ]['progress_cost'];
            }

            $data[] = $billItem;

            unset( $billItems[$key] );
        }

        $this->items = $data;
    }

    protected function processItem($item)
    {
        $this->newLine();
        $this->newLine();

        $this->itemType = BillItemTable::getItemTypeText($item['type']);

        $this->currentCol = $this->colDescription;
        $this->activeSheet->setCellValue($this->currentCol . $this->currentRow, $item['description']);
        $this->setItemStyle();

        $value = $item['total_quantity'];

        if( $value == 0 ) $value = null;

        parent::setValue($this->colContractQuantity, $value);

        $value = $item['up_to_date_qty'];

        if( $value == 0 ) $value = null;

        parent::setValue($this->colProgressQuantity, $value);

        $value = $item['rate'];

        if( $value == 0 ) $value = null;

        parent::setValue($this->colContractRate, $value);

        $value = $item['budget_rate'];

        if( $value == 0 ) $value = null;

        parent::setValue($this->colBudgetRate, $value);

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
                SELf::COL_NAME_CONTRACT_QUANTITY,
                SELf::COL_NAME_PROGRESS_QUANTITY,
                SELf::COL_NAME_CONTRACT_RATE,
                SELf::COL_NAME_BUDGET_RATE,
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