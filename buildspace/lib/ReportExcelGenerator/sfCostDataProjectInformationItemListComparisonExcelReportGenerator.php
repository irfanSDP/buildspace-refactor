<?php

class sfCostDataProjectInformationItemListComparisonExcelReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colDescription = "B";

    const COL_NAME_ITEM = 'Item';

    protected $billHeader   = 'Cost Data Comparison Report';
    protected $topLeftTitle = null;
    protected $subtitle     = 'Project Information';

    protected $benchMarkCostDataIds = [];
    protected $allCostDataIds       = [];
    protected $items                = [];
    protected $values               = [];
    protected $parentId;

    function __construct($costData = null, $savePath = null, $filename = null)
    {
        $filename = ( $filename ) ? $filename : $costData->name . '-' . $this->subtitle . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->costData = $costData;

        $this->masterCostData = $costData->MasterCostData;

        parent::__construct($costData, $savePath, $filename, array());
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

    public function setParameters(array $benchMarkCostDataIds, $parentId)
    {
        $this->benchMarkCostDataIds = $benchMarkCostDataIds;

        foreach($benchMarkCostDataIds as $id)
        {
            $this->arrayOfBenchmarkCostData[] = Doctrine_Core::getTable('CostData')->find($id);
        }

        $this->allCostDataIds = array_merge([$this->costData->id], $this->benchMarkCostDataIds);

        $this->parentId = $parentId;

        if( $parentItem = Doctrine_Core::getTable('MasterCostDataProjectInformation')->find($this->parentId) ) $this->topLeftTitle = $parentItem->description;
    }

    public function createHeader($new = false)
    {
        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);

        $this->currentRow++;

        $this->activeSheet->setCellValue($this->colDescription . $this->currentRow, self::COL_NAME_ITEM);

        $this->currentCol = $this->colDescription;

        $this->currentCol++;

        $this->activeSheet->setCellValue($this->currentCol . $this->currentRow, $this->costData->name);

        $this->activeSheet->getColumnDimension($this->currentCol)->setWidth(45);

        foreach($this->arrayOfBenchmarkCostData as $costData)
        {
            $this->currentCol++;

            $this->activeSheet->setCellValue($this->currentCol . $this->currentRow, $costData->name);

            $this->activeSheet->getColumnDimension($this->currentCol)->setWidth(45);
        }

        //Set header styling
        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colDescription;
        $this->lastCol    = $this->colDescription;

        $numberOfColumnsToAdd = count($this->allCostDataIds);

        self::increment($this->lastCol, $numberOfColumnsToAdd);
    }

    protected function processRows()
    {
        $this->itemType = null; // For description row styling.

        foreach($this->items as $itemId => $item)
        {
            $this->newLine();
            $this->newLine();

            $this->currentCol = $this->colDescription;

            $this->activeSheet->setCellValue($this->currentCol . $this->currentRow, $item['description']);

            $this->activeSheet->getStyle($this->currentCol . $this->currentRow)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'wrapText'   => true
                )
            ));

            $this->currentCol++;

            $this->activeSheet->setCellValue($this->currentCol . $this->currentRow, $this->values[$item['id']][$this->costData->id] ?? '');
            $this->activeSheet->getStyle($this->currentCol . $this->currentRow)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'wrapText'   => true
                )
            ));

            $this->currentCol++;

            foreach($this->benchMarkCostDataIds as $costDataId)
            {
                $this->activeSheet->setCellValue($this->currentCol . $this->currentRow, $this->values[$item['id']][$costDataId] ?? '');
                $this->activeSheet->getStyle($this->currentCol . $this->currentRow)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'wrapText'   => true
                    )
                ));

                $this->currentCol++;
            }
        }

        $this->newLine(true);
    }

    public function generateReport()
    {
        $this->startBillCounter();
        $this->createHeader();
        $this->setBillHeader($this->billHeader, $this->topLeftTitle, $this->subtitle);
        $this->getItems();
        $this->getValues();
        $this->processRows();
        $this->generateExcelFile();
    }

    protected function getItems()
    {
        $stmt = $this->pdo->prepare("
            SELECT mi.id, mi.description
            FROM " . MasterCostDataProjectInformationTable::getInstance()->getTableName() . " mi
            WHERE mi.master_cost_data_id = {$this->masterCostData->id}
            AND mi.deleted_at IS NULL
            AND mi.level = 2
            AND mi.parent_id = {$this->parentId}
            ORDER BY priority;
            ");

        $stmt->execute();

        $this->items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getValues()
    {
        $questionMarks = '(' . implode(',', array_fill(0, count($this->allCostDataIds), '?')) . ')';

        $stmt = $this->pdo->prepare("
            SELECT mi.id, i.cost_data_id, i.description
            FROM " . CostDataProjectInformationTable::getInstance()->getTableName() . " i
            JOIN " . MasterCostDataProjectInformationTable::getInstance()->getTableName() . " mi on mi.id = i.master_cost_data_project_information_id
            WHERE mi.master_cost_data_id = {$this->masterCostData->id}
            AND i.cost_data_id IN {$questionMarks}
            AND mi.parent_id = {$this->parentId}
            AND mi.deleted_at IS NULL
            ");

        $stmt->execute($this->allCostDataIds);

        $costDataProjectInformation = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

        foreach($costDataProjectInformation as $masterInfoId => $costDataMasterInfo)
        {
            $this->values[$masterInfoId] = Utilities::getKeyPairFromAttributes($costDataMasterInfo, 'cost_data_id', 'description');
        }
    }
}