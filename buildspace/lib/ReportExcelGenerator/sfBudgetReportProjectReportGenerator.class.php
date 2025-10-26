<?php

class sfBudgetReportProjectReportGenerator extends sfBuildspaceExcelReportGenerator {

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

    protected $items  = array();
    protected $voItem = array();

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

        self::increment($this->lastCol, 6);
    }

    protected function getItems()
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $subProjects         = ProjectStructureTable::getSubProjects($this->project);
        $billItemRates       = BudgetReport::getProjectItemRates($this->project);
        $subConBillItemRates = BudgetReport::getSubProjectItemRates($this->project);

        $stmt = $pdo->prepare("SELECT i.id as item_id, b.id as bill_id, i.type
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

        $tenderAlternative = $this->project->getAwardedTenderAlternative();

        $tenderAlternativeJoinSql = "";
        $tenderAlternativeWhereSql = "";

        if($tenderAlternative)
        {
            $tenderAlternativeJoinSql = " JOIN ".TenderAlternativeTable::getInstance()->getTableName()." ta ON ta.project_structure_id = p.id
                JOIN ".TenderAlternativeBillTable::getInstance()->getTableName()." tax ON tax.tender_alternative_id = ta.id AND tax.project_structure_id = b.id ";

            $tenderAlternativeWhereSql = " AND ta.id = ".$tenderAlternative->id." AND ta.deleted_at IS NULL AND ta.project_revision_deleted_at IS NULL ";
        }

        $stmt = $pdo->prepare("SELECT b.id, b.title as description, b.type, b.level
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " b
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p on p.id = b.root_id
            ".$tenderAlternativeJoinSql."
            WHERE p.id = {$this->project->id}
            AND b.id != p.id
            AND b.type = " . ProjectStructure::TYPE_BILL . "
            ".$tenderAlternativeWhereSql."
            AND p.deleted_at IS NULL
            AND b.deleted_at IS NULL
            ORDER BY b.priority, b.lft, b.level ASC;");

        $stmt->execute();

        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array();

        foreach($bills as $billKey => $bill)
        {
            $bill['revenue']             = 0;
            $bill['budget']              = 0;
            $bill['sub_con_budget']      = 0;
            $bill['sub_con_cost']        = 0;
            $bill['progress_revenue']    = 0;
            $bill['progress_cost']       = 0;

            foreach($billItems as $billItemKey => $billItem)
            {
                $billItemId = $billItem['item_id'];
                $billId     = $billItem['bill_id'];

                if( $billId != $bill['id'] ) continue;

                $bill['revenue'] += $billItemRates[ $billItemId ]['revenue'];
                $bill['budget'] += $billItemRates[ $billItemId ]['budget'];
                $bill['sub_con_budget'] += $billItemRates[ $billItemId ]['sub_con_budget'];
                $bill['sub_con_cost'] += $billItemRates[ $billItemId ]['sub_con_cost'];
                $bill['progress_revenue'] += $billItemRates[ $billItemId ]['progress_revenue'];
                $bill['progress_cost'] += $billItemRates[ $billItemId ]['progress_cost'];

                $itemIsTagged = false;

                foreach($subProjects as $subProject)
                {
                    if( ! array_key_exists($billItemId, $subConBillItemRates) || ! array_key_exists($subProject->id, $subConBillItemRates[ $billItemId ]) ) continue;

                    $bill['revenue'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['revenue'];
                    $bill['budget'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['budget'];
                    $bill['sub_con_budget'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['sub_con_budget'];
                    $bill['sub_con_cost'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['sub_con_cost'];
                    $bill['progress_revenue'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['progress_revenue'];
                    $bill['progress_cost'] += $subConBillItemRates[ $billItemId ][ $subProject->id ]['progress_cost'];
                }

                unset( $billItems[ $billItemKey ] );
            }

            unset( $bills[ $billKey ] );

            $data[] = $bill;
        }

        $this->items = $data;

        $variationOrderItemRates       = BudgetReport::getProjectVariationOrderItemRates($this->project);
        $subConVariationOrderItemRates = BudgetReport::getSubProjectVariationOrderItemRates($this->project);

        $stmt = $pdo->prepare("SELECT i.id, i.type
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

        $variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $variationOrderData = array(
            'revenue'             => 0,
            'sub_con_cost'        => 0,
            'progress_revenue'    => 0,
            'progress_cost'       => 0,
        );

        foreach($variationOrderItems as $key => $item)
        {
            $itemId = $item['id'];

            $variationOrderData['revenue'] += $variationOrderItemRates[ $itemId ]['revenue'] ?? 0;
            $variationOrderData['sub_con_cost'] += $variationOrderItemRates[ $itemId ]['sub_con_cost'] ?? 0;
            $variationOrderData['progress_revenue'] += $variationOrderItemRates[ $itemId ]['progress_revenue'] ?? 0;
            $variationOrderData['progress_cost'] += $variationOrderItemRates[ $itemId ]['progress_cost'] ?? 0;

            $itemIsTagged = false;

            foreach($subProjects as $subProject)
            {
                if( ! array_key_exists($itemId, $subConVariationOrderItemRates) || ! array_key_exists($subProject->id, $subConVariationOrderItemRates[ $itemId ]) ) continue;

                $variationOrderData['revenue'] += $subConVariationOrderItemRates[ $itemId ][ $subProject->id ]['revenue'];
                $variationOrderData['sub_con_cost'] += $subConVariationOrderItemRates[ $itemId ][ $subProject->id ]['sub_con_cost'];
                $variationOrderData['progress_revenue'] += $subConVariationOrderItemRates[ $itemId ][ $subProject->id ]['progress_revenue'];
                $variationOrderData['progress_cost'] += $subConVariationOrderItemRates[ $itemId ][ $subProject->id ]['progress_cost'];
            }

            unset( $variationOrderItems[ $key ] );
        }

        $this->voItem = [
            'id'                  => PostContractClaim::TYPE_VARIATION_ORDER_TEXT,
            'description'         => PostContractClaim::TYPE_VARIATION_ORDER_TEXT,
            'type'                => PostContractClaim::TYPE_VARIATION_ORDER,
            'level'               => 0,
            'revenue'             => $variationOrderData['revenue'],
            'budget'              => 0,
            'sub_con_budget'      => 0,
            'sub_con_cost'        => $variationOrderData['sub_con_cost'],
            'progress_revenue'    => $variationOrderData['progress_revenue'],
            'progress_cost'       => $variationOrderData['progress_cost'],
        ];
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

        $this->processItem($this->voItem);

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