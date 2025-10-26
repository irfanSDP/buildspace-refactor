<?php

class sfLocationProgressClaimReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colFirstTrade = "B";
    public $tradeColumns = array();
    public $colFirstLocation;
    public $locationColumns = array();
    public $colBillItem;
    public $colUnit;
    public $colProratedQty;
    public $colPreviousClaimPercentage;
    public $colPreviousClaimQuantity;
    public $colCurrentClaimPercentage;
    public $colCurrentClaimQuantity;
    public $colUpToDateClaimPercentage;
    public $colUpToDateClaimQuantity;

    protected $tradeLevels = array();
    protected $locationLevels = array();
    protected $items = array();

    const COL_NAME_TRADE            = 'Trade';
    const COL_NAME_ELEMENT          = 'Element';
    const COL_NAME_SUB_ELEMENT      = 'Sub Element';
    const COL_NAME_LOCATION         = 'Location';
    const COL_NAME_BILL_ITEM        = 'Bill Item';
    const COL_NAME_PRORATED_QTY     = 'Prorated Qty';
    const COL_NAME_PREVIOUS_CLAIM   = 'Previous Claim';
    const COL_NAME_CURRENT_CLAIM    = 'Current Claim';
    const COL_NAME_UP_TO_DATE_CLAIM = 'Up To Date Claim';

    protected $requestParams = array();

    function __construct($project = null, $savePath = null, $filename = null)
    {
        $filename = ( $filename ) ? $filename : $this->bill->title . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        parent::__construct($project, $savePath, $filename, array());
    }

    public function createHeader($new = false)
    {
        $this->currentRow++;
        $firstHeaderRow = $this->currentRow;

        $count = 0;
        foreach($this->tradeColumns as $tradeColumn)
        {
            $columnNameMap = array(
                0 => self::COL_NAME_TRADE,
                1 => self::COL_NAME_ELEMENT,
                2 => self::COL_NAME_SUB_ELEMENT,
            );
            $this->activeSheet->setCellValue($tradeColumn . $this->currentRow, $columnNameMap[$count++]);
            $this->mergeRows($tradeColumn, $this->currentRow);
        }

        $count = 0;
        foreach($this->locationColumns as $locationColumn)
        {
            $this->activeSheet->setCellValue($locationColumn . $this->currentRow, self::COL_NAME_LOCATION . " " . ++$count);
            $this->mergeRows($locationColumn, $this->currentRow);
        }

        $this->activeSheet->setCellValue($this->colBillItem . $this->currentRow, self::COL_NAME_BILL_ITEM);
        $this->mergeRows($this->colBillItem, $this->currentRow);

        $this->activeSheet->setCellValue($this->colUnit . $this->currentRow, self::COL_NAME_UNIT);
        $this->mergeRows($this->colUnit, $this->currentRow);

        $this->activeSheet->setCellValue($this->colProratedQty . $this->currentRow, self::COL_NAME_PRORATED_QTY);
        $this->mergeRows($this->colProratedQty, $this->currentRow);

        $this->activeSheet->setCellValue($this->colPreviousClaimPercentage . $this->currentRow, self::COL_NAME_PREVIOUS_CLAIM);
        $this->mergeColumns($this->colPreviousClaimPercentage, $this->currentRow);

        $this->activeSheet->setCellValue($this->colCurrentClaimPercentage . $this->currentRow, self::COL_NAME_CURRENT_CLAIM);
        $this->mergeColumns($this->colCurrentClaimPercentage, $this->currentRow);

        $this->activeSheet->setCellValue($this->colUpToDateClaimPercentage . $this->currentRow, self::COL_NAME_UP_TO_DATE_CLAIM);
        $this->mergeColumns($this->colUpToDateClaimPercentage, $this->currentRow);

        // Second header row.
        $this->currentRow++;

        $this->activeSheet->setCellValue($this->colPreviousClaimPercentage . $this->currentRow, self::COL_NAME_PERCENT);
        $this->activeSheet->setCellValue($this->colPreviousClaimQuantity . $this->currentRow, self::COL_NAME_QTY);

        $this->activeSheet->setCellValue($this->colCurrentClaimPercentage . $this->currentRow, self::COL_NAME_PERCENT);
        $this->activeSheet->setCellValue($this->colCurrentClaimQuantity . $this->currentRow, self::COL_NAME_QTY);

        $this->activeSheet->setCellValue($this->colUpToDateClaimPercentage . $this->currentRow, self::COL_NAME_PERCENT);
        $this->activeSheet->setCellValue($this->colUpToDateClaimQuantity . $this->currentRow, self::COL_NAME_QTY);

        //Set header styling
        $this->activeSheet->getStyle($this->firstCol . $firstHeaderRow . ':' . $this->lastCol . $firstHeaderRow)->applyFromArray($this->getColumnHeaderStyle());
        // For merged header rows
        $this->activeSheet->getStyle($this->firstCol . $this->getNextRow($firstHeaderRow) . ':' . $this->lastCol . $this->getNextRow($firstHeaderRow))->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);

        foreach($this->tradeColumns as $tradeColumn)
        {
            $this->activeSheet->getColumnDimension($tradeColumn)->setWidth(25);
        }
        foreach($this->locationColumns as $locationColumn)
        {
            $this->activeSheet->getColumnDimension($locationColumn)->setWidth(25);
        }

        $this->activeSheet->getColumnDimension($this->colBillItem)->setWidth(45);
        $this->activeSheet->getColumnDimension($this->colUnit)->setWidth(13);
        $this->activeSheet->getColumnDimension($this->colProratedQty)->setWidth(15);
        $this->activeSheet->getColumnDimension($this->colPreviousClaimPercentage)->setWidth(15);
        $this->activeSheet->getColumnDimension($this->colPreviousClaimQuantity)->setWidth(15);
        $this->activeSheet->getColumnDimension($this->colCurrentClaimPercentage)->setWidth(15);
        $this->activeSheet->getColumnDimension($this->colCurrentClaimQuantity)->setWidth(15);
        $this->activeSheet->getColumnDimension($this->colUpToDateClaimPercentage)->setWidth(15);
        $this->activeSheet->getColumnDimension($this->colUpToDateClaimQuantity)->setWidth(15);
    }

    public function setParameters($requestParams)
    {
        $this->requestParams = $requestParams;
    }

    public function startBillCounter()
    {
        $this->currentRow       = $this->startRow;
        $this->firstCol         = $this->colFirstTrade;

        foreach($this->items as $item)
        {
            foreach($item as $field => $value)
            {
                if( strpos($field, '-predefined_location_code') !== false )
                {
                    $index = str_replace('-predefined_location_code', '', $field);
                    $this->tradeLevels[$index] = $index;
                }

                if( strpos($field, '-project_structure_location_code') !== false )
                {
                    $index = str_replace('-project_structure_location_code', '', $field);
                    $this->locationLevels[$index] = $index;
                }
            }
        }

        $tradeColumnPointer = $this->colFirstTrade;

        $this->colFirstLocation = $this->colFirstTrade;
        foreach($this->tradeLevels as $index)
        {
            $this->tradeColumns[] = $tradeColumnPointer;
            $tradeColumnPointer++;

            $this->colFirstLocation++;
        }
        sort($this->tradeColumns);

        $locationColumnPointer = $this->colFirstLocation;

        $this->colBillItem = $this->colFirstLocation;
        foreach($this->locationLevels as $index)
        {
            $this->locationColumns[] = $locationColumnPointer;
            $locationColumnPointer++;

            $this->colBillItem++;
        }
        sort($this->locationColumns);

        $this->colUnit = $this->colBillItem;
        $this->colUnit++;

        $this->colProratedQty = $this->colUnit;
        $this->colProratedQty++;

        $this->colPreviousClaimPercentage = $this->colProratedQty;
        $this->colPreviousClaimPercentage++;

        $this->colPreviousClaimQuantity = $this->colPreviousClaimPercentage;
        $this->colPreviousClaimQuantity++;

        $this->colCurrentClaimPercentage = $this->colPreviousClaimQuantity;
        $this->colCurrentClaimPercentage++;

        $this->colCurrentClaimQuantity = $this->colCurrentClaimPercentage;
        $this->colCurrentClaimQuantity++;

        $this->colUpToDateClaimPercentage = $this->colCurrentClaimQuantity;
        $this->colUpToDateClaimPercentage++;

        $this->colUpToDateClaimQuantity = $this->colUpToDateClaimPercentage;
        $this->colUpToDateClaimQuantity++;

        $this->lastCol = $this->colUpToDateClaimQuantity;
    }

    public function process($items, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
    {
        $this->items = $items;
        $this->createSheet($header, $topLeftTitle, $subTitle);

        $this->createHeader();

        foreach($items as $key => $item)
        {
            $this->newLine(!isset($items[$key+1]));

            foreach($item as $field => $value)
            {
                if( strpos($field, '-predefined_location_code') !== false )
                {
                    $index = str_replace('-predefined_location_code', '', $field);
                    if(isset($this->tradeColumns[$index])) $this->setRegularValue($this->tradeColumns[$index], $value);
                }

                if( strpos($field, '-project_structure_location_code') !== false )
                {
                    $index = str_replace('-project_structure_location_code', '', $field);
                    if(isset($this->locationColumns[$index])) $this->setRegularValue($this->locationColumns[$index], $value);
                }
            }

            $this->setDescriptionValue($this->colBillItem, $item['description']);

            $this->setUnit($item['uom']);
            $this->setNormalQtyValue($this->colProratedQty, $item['prorated_qty']);
            $this->setValue($this->colPreviousClaimPercentage, $item['previous_percentage']);
            $this->setNormalQtyValue($this->colPreviousClaimQuantity, $item['previous_quantity']);
            $this->setValue($this->colCurrentClaimPercentage, $item['current_percentage']);
            $this->setNormalQtyValue($this->colCurrentClaimQuantity, $item['current_quantity']);
            $this->setValue($this->colUpToDateClaimPercentage, $item['up_to_date_percentage']);
            $this->setNormalQtyValue($this->colUpToDateClaimQuantity, $item['up_to_date_quantity']);
        }

        //write to Excel File
        $this->fileInfo = $this->writeExcel();
    }
}