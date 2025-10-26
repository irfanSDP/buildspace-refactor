<?php

use sfBuildspaceScheduleOfRateItemCostAnalysisGenerator as ReportGenerator;

class sfSorItemCostAnalysisReportGenerator extends sfBuildspaceExcelReportGenerator {
    public $colUnit           = "D";
    public $colTotalQty       = "E";
    public $colRate           = "F";
    public $colProfitRate     = "G";
    public $colProfitTotal    = "H";
    public $colProfit2Rate    = "I";
    public $colProfit2Total   = "J";
    public $colNoBuildUpRate  = "K";
    public $colNoBuildUpTotal = "L";

    public $currentTradeId;

    protected $scheduleOfRateItemCosts;
    protected $scheduleOfRateTradeResourceTotals;

    private $resourceColumns = array();
    private $hasScheduleOfRateNoBuildUp = false;
    private $hasBillMarkup = false;

    private $itemStartRowIndex = null;
    private $itemEndRowIndex = null;

    function __construct($project = null, $savePath = null, $filename = null, $printSettings)
    {
        $filename = ( $filename ) ? $filename : $this->bill->title . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        parent::__construct($project, $savePath, $filename, $printSettings);
    }

    public function setParameters($scheduleOfRateItemCosts, $scheduleOfRateTradeResourceTotals)
    {
        $this->scheduleOfRateItemCosts           = $scheduleOfRateItemCosts;
        $this->scheduleOfRateTradeResourceTotals = $scheduleOfRateTradeResourceTotals;
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colItem;
        $this->lastCol    = $this->colRate;

        $this->currentElementNo = 0;
        $this->columnSetting    = null;
    }

    public function createHeader($new = false)
    {
        $this->currentRow++;
        $this->headerRow    = $this->currentRow;
        $this->itemStartRow = $this->getNextRow($this->getNextRow($this->headerRow));

        //set default column
        $this->activeSheet->setCellValue($this->colItem . $this->currentRow, self::COL_NAME_NO);
        $this->activeSheet->setCellValue($this->colDescription . $this->currentRow, self::COL_NAME_DESCRIPTION);
        $this->activeSheet->setCellValue($this->colUnit . $this->currentRow, self::COL_NAME_UNIT);
        $this->activeSheet->setCellValue($this->colTotalQty . $this->currentRow, self::COL_NAME_TOTAL_QTY);
        $this->activeSheet->setCellValue($this->colRate . $this->currentRow, self::COL_NAME_RATE);

        $this->mergeRows($this->colItem, $this->currentRow);
        $this->mergeRows($this->colDescription, $this->currentRow);
        $this->mergeRows($this->colUnit, $this->currentRow);
        $this->mergeRows($this->colTotalQty, $this->currentRow);
        $this->mergeRows($this->colRate, $this->currentRow);

        //Set header styling
        $this->activeSheet->getStyle($this->colItem . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getColumnHeaderStyle());
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colItem)->setWidth(9);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
        $this->activeSheet->getColumnDimension($this->colUnit)->setWidth(10);
        $this->activeSheet->getColumnDimension($this->colRate)->setWidth(15);
        $this->activeSheet->getColumnDimension($this->colTotalQty)->setWidth(15);

        $lengthAndMap = $this->addHeaderColumns(array(
            self::COL_NAME_PROFIT => array(
                self::COL_NAME_RATE,
                self::COL_NAME_TOTAL )
        ), $this->getNextColumn($this->colRate), $this->currentRow);

        // Update lastCol.
        $this->increment($this->lastCol, $lengthAndMap['numberOfColumns']);

        $this->currentRow++;

        foreach($this->scheduleOfRateItemCosts as $tradeId => $scheduleOfRateItemCost)
        {
            if($this->currentTradeId == $tradeId)
            {
                foreach($scheduleOfRateItemCost as $item)
                {
                    if(array_key_exists('noBuildUp', $item))
                    {
                        $this->hasScheduleOfRateNoBuildUp = true;
                        break 2;
                    }
                }

                $this->hasScheduleOfRateNoBuildUp = false;
            }
        }

        foreach($this->scheduleOfRateItemCosts as $tradeId => $scheduleOfRateItemCost)
        {
            if($this->currentTradeId == $tradeId)
            {
                foreach($scheduleOfRateItemCost as $item)
                {
                    if(array_key_exists('profitFromBillMarkup', $item))
                    {
                        $this->hasBillMarkup = true;
                        break 2;
                    }
                }

                $this->hasBillMarkup = false;
            }
        }

        if(!$this->hasBillMarkup && $this->hasScheduleOfRateNoBuildUp)
        {
            $this->colNoBuildUpRate = 'I';
            $this->colNoBuildUpTotal = 'J';
        }
    }

    public function process($pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
    {
        $this->setExcelParameter($lock, $withoutCents);

        $this->totalPage = $totalPage;

        $description   = '';
        $char          = '';
        $prevItemType  = '';
        $prevItemLevel = 0;

        $this->createSheet($header, $subTitle, $topLeftTitle);

        $pageNo = 1;

        foreach($pages as $key => $page)
        {
            $this->currentTradeId = $key;

            for($i = 1; $i <= $page['item_pages']->count(); $i++)
            {
                if( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $this->createNewPage($pageNo, false, 0);

                    $itemPage = $page['item_pages']->offsetGet($i);

                    foreach($itemPage as $item)
                    {
                        $itemType = $item[ ReportGenerator::ROW_BILL_ITEM_TYPE ];

                        switch($itemType)
                        {
                            case self::ROW_TYPE_BLANK:

                                if( $description != '' && $prevItemType != '' )
                                {
                                    if( $prevItemType == ScheduleOfRateItem::TYPE_HEADER )
                                    {
                                        $this->newItem();

                                        if( strpos($description, $this->printSettings['layoutSetting']['contdPrefix']) !== false )
                                        {
                                            $this->setItemHead($description, $prevItemType, $prevItemLevel);
                                        }
                                        else
                                        {
                                            $this->setItemHead($description, $prevItemType, $prevItemLevel, true);
                                        }
                                    }

                                    $description = '';
                                }
                                break;
                            case self::ROW_TYPE_ELEMENT:

                                if( strpos($item[ ReportGenerator::ROW_BILL_ITEM_DESCRIPTION ], $this->printSettings['layoutSetting']['contdPrefix']) !== false )
                                {
                                    $this->setElementTitle($this->printSettings['layoutSetting']['contdPrefix']);
                                }
                                else
                                {
                                    $this->setElement(array( 'description' => $item[ ReportGenerator::ROW_BILL_ITEM_DESCRIPTION ] ));
                                }

                                break;
                            case ScheduleOfRateItem::TYPE_HEADER:

                                $description .= $item[ ReportGenerator::ROW_BILL_ITEM_DESCRIPTION ] . "\n";
                                $prevItemType  = $item[ ReportGenerator::ROW_BILL_ITEM_TYPE ];
                                $prevItemLevel = $item[ ReportGenerator::ROW_BILL_ITEM_LEVEL ];

                                break;
                            default:

                                $description .= $item[ ReportGenerator::ROW_BILL_ITEM_DESCRIPTION ] . "\n";
                                $char .= $item[ ReportGenerator::ROW_BILL_ITEM_ROW_IDX ];

                                if( $item[ ReportGenerator::ROW_BILL_ITEM_ID ] )
                                {
                                    $this->newItem();

                                    $this->setItem($description, $itemType, $item[ ReportGenerator::ROW_BILL_ITEM_LEVEL ], $char);

                                    $this->processItems($item);

                                    $description = '';

                                    $char = '';
                                }

                                break;
                        }
                    }

                    // Apply cell style for all rows.
                    // Done here because new resource columns may have been added and earlier items would not get the style for all columns.
                    $this->activeSheet->getStyle($this->firstCol . $this->itemStartRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getNewLineStyle());

                    $this->createFooter(true);

                    $pageNo++;
                }
            }
        }
        //write to Excel File
        $this->fileInfo = $this->writeExcel();
    }

    public function printGrandTotal()
    {
        $this->activeSheet->getStyle($this->colRate . $this->currentRow)->applyFromArray($this->getTotalStyle());

        $this->printTotalText();

        $this->printGrandTotalValue($this->getNewLineStyle(true));

        $this->currentRow++;
    }

    public function printTotalText($title = false)
    {
        $this->activeSheet->setCellValue($this->colRate . $this->currentRow, ( $title ) ? $title : "Total:");
    }

    public function printGrandTotalValue($style)
    {
        parent::setValue($this->colProfitRate, "=SUM(".$this->colProfitTotal.$this->itemStartRowIndex.":".$this->colProfitTotal.$this->itemEndRowIndex.")");
        $this->mergeColumns($this->colProfitRate, $this->currentRow);

        foreach($this->resourceColumns as $resourceName => $columnMap)
        {
            $this->currentCol = $columnMap[ self::COL_NAME_RATE ];

            parent::setValue($columnMap[ self::COL_NAME_RATE ], "=SUM(".$columnMap[ self::COL_NAME_TOTAL].$this->itemStartRowIndex.":".$columnMap[ self::COL_NAME_TOTAL].$this->itemEndRowIndex.")");

            $this->mergeColumns($columnMap[ self::COL_NAME_RATE ], $this->currentRow);

            $this->currentCol = $columnMap[ self::COL_NAME_WASTAGE_RATE ];

            parent::setValue($columnMap[ self::COL_NAME_WASTAGE_RATE ], "=SUM(".$columnMap[ self::COL_NAME_WASTAGE_TOTAL].$this->itemStartRowIndex.":".$columnMap[ self::COL_NAME_WASTAGE_TOTAL].$this->itemEndRowIndex.")");

            $this->mergeColumns($columnMap[ self::COL_NAME_WASTAGE_RATE ], $this->currentRow);
        }

        $this->activeSheet->getStyle($this->colProfitRate . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($style);

        if($this->hasBillMarkup)
        {
            parent::setValue($this->colProfit2Rate, "=SUM(".$this->colProfit2Total.$this->itemStartRowIndex.":".$this->colProfit2Total.$this->itemEndRowIndex.")");
            $this->mergeColumns($this->colProfit2Rate, $this->currentRow);
        }

        if($this->hasScheduleOfRateNoBuildUp)
        {
            parent::setValue($this->colNoBuildUpRate, "=SUM(".$this->colNoBuildUpTotal.$this->itemStartRowIndex.":".$this->colNoBuildUpTotal.$this->itemEndRowIndex.")");
            $this->mergeColumns($this->colNoBuildUpRate, $this->currentRow);
        }
    }

    public function createNewPage($pageNo = null, $printGrandTotal = false, $printFooter = false)
    {
        $this->resetResources();

        $this->itemStartRowIndex = null;
        $this->itemEndRowIndex = null;

        parent::createNewPage($pageNo, $printGrandTotal, $printFooter);

        if($this->hasBillMarkup)
        {
            $lengthAndMap = $this->addHeaderColumns(array(
                self::COL_NAME_PROFIT_BILL_MARKUP => array(
                    self::COL_NAME_RATE,
                    self::COL_NAME_TOTAL
                )
            ), $this->getNextColumn($this->lastCol), $this->headerRow);

            // Update lastCol.
            $this->increment($this->lastCol, $lengthAndMap['numberOfColumns']);
        }

        if($this->hasScheduleOfRateNoBuildUp)
        {
            if($this->hasScheduleOfRateNoBuildUp)
            {
                $lengthAndMap = $this->addHeaderColumns(array(
                    self::COL_NAME_SOR_NO_BUILD_UP => array(
                        self::COL_NAME_RATE,
                        self::COL_NAME_TOTAL
                    )
                ), $this->getNextColumn($this->lastCol), $this->headerRow);

                // Update lastCol.
                $this->increment($this->lastCol, $lengthAndMap['numberOfColumns']);
            }
        }
    }

    private function resetResources()
    {
        $this->lastCol         = $this->colRate;
        $this->resourceColumns = array();
    }

    public function processItems($item)
    {
        if(is_null($this->itemStartRowIndex))
            $this->itemStartRowIndex = $this->currentRow;

        $this->itemEndRowIndex = $this->currentRow;

        $multi = ( $item[ ReportGenerator::ROW_BILL_ITEM_MULTI_RATE ] ) ? true : false;
        $rate  = ( $multi ) ? "MULTI" : $item[ ReportGenerator::ROW_BILL_ITEM_RATE ];

        parent::setValue($this->colRate, $rate);

        parent::setUnit($item[ ReportGenerator::ROW_BILL_ITEM_UNIT ]);

        parent::setNormalQtyValue($this->colTotalQty, $item[ ReportGenerator::ROW_BILL_ITEM_TOTAL_QTY ]);

        $profitRate = $this->scheduleOfRateItemCosts[ $this->currentTradeId ][ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]['profit']['rate'];

        $profitRate = is_array($profitRate) ? 'MULTI' : $profitRate;

        parent::setValue($this->colProfitRate, $profitRate);

        if($profitRate != "MULTI"){
            parent::setValue($this->colProfitTotal,  "=".$this->colTotalQty.$this->currentRow."*".$this->colProfitRate.$this->currentRow);
        }else{
            parent::setValue($this->colProfitTotal, $this->scheduleOfRateItemCosts[ $this->currentTradeId ][ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]['profit']['total']);
        }

        if($this->hasBillMarkup && array_key_exists('profitFromBillMarkup',  $this->scheduleOfRateItemCosts[ $this->currentTradeId ][ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]))
        {
            parent::setValue($this->colProfit2Total,  $this->scheduleOfRateItemCosts[ $this->currentTradeId ][ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]['profitFromBillMarkup']);
        }

        if($this->hasScheduleOfRateNoBuildUp && array_key_exists('noBuildUp',  $this->scheduleOfRateItemCosts[ $this->currentTradeId ][ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]))
        {
            parent::setValue($this->colNoBuildUpRate, $rate);
            parent::setValue($this->colNoBuildUpTotal,  $this->scheduleOfRateItemCosts[ $this->currentTradeId ][ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]['noBuildUp']['total']);
        }

        $this->addRelevantResourceColumns($item);
    }

    protected function addRelevantResourceColumns($item)
    {
        $sum = array();

        foreach($this->scheduleOfRateItemCosts[ $this->currentTradeId ][ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]['resources'] as $resourceName => $resourceRates)
        {
            if( ! array_key_exists($resourceName, $this->resourceColumns) )
            {
                $lengthAndMap = $this->addHeaderColumns(array(
                    $resourceName => array(
                        self::COL_NAME_RATE,
                        self::COL_NAME_TOTAL,
                        self::COL_NAME_WASTAGE_RATE,
                        self::COL_NAME_WASTAGE_TOTAL
                    )
                ), $this->getNextColumn($this->lastCol), $this->headerRow);

                $this->resourceColumns = array_merge($this->resourceColumns, $lengthAndMap['map']);

                // Update lastCol.
                $this->increment($this->lastCol, $lengthAndMap['numberOfColumns']);
            }

            // Add values to the columns.
            $resourceRate = $resourceRates['rate'];
            $resourceRate = is_array($resourceRate) ? 'MULTI' : $resourceRate;

            $wastageRate = $resourceRates['wastageRate'];
            $wastageRate = is_array($wastageRate) ? 'MULTI' : $wastageRate;

            parent::setValue($this->resourceColumns[ $resourceName ][ self::COL_NAME_RATE ], $resourceRate);

            if($resourceRate != "MULTI"){
                parent::setValue($this->resourceColumns[ $resourceName ][ self::COL_NAME_TOTAL ],  "=".$this->colTotalQty.$this->currentRow."*".$this->resourceColumns[ $resourceName ][ self::COL_NAME_RATE ].$this->currentRow);
            }else{
                parent::setValue($this->resourceColumns[ $resourceName ][ self::COL_NAME_TOTAL ], $resourceRates['total']);
            }

            parent::setValue($this->resourceColumns[ $resourceName ][ self::COL_NAME_WASTAGE_RATE ], $wastageRate);

            if($wastageRate != "MULTI"){
                parent::setValue($this->resourceColumns[ $resourceName ][ self::COL_NAME_WASTAGE_TOTAL ],  "=".$this->colTotalQty.$this->currentRow."*".$this->resourceColumns[ $resourceName ][ self::COL_NAME_WASTAGE_RATE ].$this->currentRow);
            }else{
                parent::setValue($this->resourceColumns[ $resourceName ][ self::COL_NAME_WASTAGE_TOTAL ], $resourceRates['wastageTotal']);
            }
            $sum[] = $this->resourceColumns[ $resourceName ][ self::COL_NAME_RATE ].$this->currentRow;
            $sum[] = $this->resourceColumns[ $resourceName ][ self::COL_NAME_WASTAGE_RATE ].$this->currentRow;
        }

        if(parent::getValue($this->colRate) != "MULTI" &&  parent::getValue($this->colProfitRate) != "MULTI" && !empty($sum))
        {
            parent::setValue($this->colRate, '=SUM('.$this->colProfitRate.$this->currentRow.','.implode(",", $sum).')');
        }

    }

}