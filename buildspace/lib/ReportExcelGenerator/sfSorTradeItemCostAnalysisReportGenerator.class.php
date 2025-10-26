<?php

use sfBuildspaceScheduleOfRateTradeItemCostAnalysisGenerator as ReportGenerator;

class sfSorTradeItemCostAnalysisReportGenerator extends sfBuildspaceExcelReportGenerator {
    public $colUnit         = "D";
    public $colTotalQty     = "E";
    public $colRate         = "F";
    public $colProfitRate   = "G";
    public $colProfitTotal  = "H";
    public $colProfit2Rate  = "I";
    public $colProfit2Total = "J";

    public $scheduleOfRateTrade;
    public $billElementIdToDescription;
    public $tradeIdToDescription;

    private $billItemResourceRates = array();
    private $scheduleOfRatesNoBuildUp = array();
    private $profitFromBillMarkup = array();
    private $titleHeaderRow;
    private $resourceColumns = array();
    private $hasBillMarkup = false;

    function __construct($project = null, ScheduleOfRateTrade $scheduleOfRateTrade, Array $billItemResourceRates, Array $profitFromBillMarkup, Array $scheduleOfRatesNoBuildUp, $savePath = null, $filename = null, $printSettings)
    {
        $filename = ( $filename ) ? $filename : $this->bill->title . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->billItemResourceRates = $billItemResourceRates;

        $this->scheduleOfRatesNoBuildUp = $scheduleOfRatesNoBuildUp;

        $this->scheduleOfRateTrade = $scheduleOfRateTrade;

        $this->profitFromBillMarkup = $profitFromBillMarkup;

        parent::__construct($project, $savePath, $filename, $printSettings);
    }

    public function setHeaderParameter($billElementIdToDescription, $tradeIdToDescription)
    {
        $this->billElementIdToDescription = $billElementIdToDescription;

        $this->tradeIdToDescription = $tradeIdToDescription;
    }

    public function setParameters($billItemResourceRates)
    {
        $this->billItemResourceRates = $billItemResourceRates;
    }

    public function setHeaderTitle($topLeftTitle)
    {
        $this->activeSheet->setCellValue($this->firstCol . $this->currentRow, $topLeftTitle);
        $this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->colDescription . $this->currentRow);
        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->colDescription . $this->currentRow)->applyFromArray($this->getLeftTitleStyle());

        $this->titleHeaderRow = $this->currentRow;

        $this->currentRow++;
    }

    public function setTopRightHeaderTitle($topRightTitle)
    {
        $this->activeSheet->setCellValue($this->getPreviousColumn($this->lastCol) . $this->titleHeaderRow, $topRightTitle);
        $this->activeSheet->mergeCells($this->getPreviousColumn($this->lastCol) . $this->titleHeaderRow . ':' . $this->lastCol . $this->titleHeaderRow);
        $this->activeSheet->getStyle($this->getPreviousColumn($this->lastCol) . $this->titleHeaderRow . ':' . $this->lastCol . $this->titleHeaderRow)->applyFromArray($this->getRightTitleStyle());
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
    }

    public function process($pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
    {
        $this->setExcelParameter($lock, $withoutCents);

        $this->totalPage = $totalPage;

        $description   = '';
        $char          = '';
        $prevItemType  = '';
        $prevItemLevel = 0;

        $this->createSheet($header, $topLeftTitle, $subTitle);

        $pageNo = 1;

        foreach($pages as $key => $elements)
        {
            foreach($elements as $billElementId => $page)
            {
                $topLeftTitle = ( array_key_exists($billElementId, $this->billElementIdToDescription) ) ? $this->billElementIdToDescription[ $billElementId ] : '';

                $topRightTitle = ( array_key_exists($key, $this->tradeIdToDescription) ) ? $this->tradeIdToDescription[ $key ] : '';

                $this->setHeaderTitle($topLeftTitle);

                for($i = 1; $i <= $page['item_pages']->count(); $i++)
                {
                    if( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                    {
                        $printGrandTotal = false;

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

                                            parent::setChar($char);

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
                                        $char        = '';
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
                                    $char .= $item[ ReportGenerator::ROW_BILL_ITEM_ROW_IDX ];
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
                                        $char        = '';
                                    }

                                    break;
                            }
                        }

                        // Apply cell style for all rows.
                        // Done here because new resource columns may have been added and earlier items would not get the style for all columns.
                        $this->activeSheet->getStyle($this->firstCol . $this->itemStartRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getNewLineStyle());

                        $this->setTopRightHeaderTitle($topRightTitle);

                        if( $printGrandTotal )
                        {
                            $this->createFooter(true);
                        }
                        else
                        {
                            $this->createFooter();
                        }

                        $pageNo++;
                    }
                }
            }
        }
        //write to Excel File
        $this->fileInfo = $this->writeExcel();
    }

    public function createNewPage($pageNo = null, $printGrandTotal = false, $printFooter = false)
    {
        $this->resetResources();

        $this->hasBillMarkup = false;

        parent::createNewPage($pageNo, $printGrandTotal, $printFooter);
    }

    private function resetResources()
    {
        $this->lastCol         = $this->colRate;
        $this->resourceColumns = array();
    }

    public function processItems($item)
    {
        parent::setUnit($item[ ReportGenerator::ROW_BILL_ITEM_UNIT ]);

        parent::setNormalQtyValue($this->colTotalQty, $item[ ReportGenerator::ROW_BILL_ITEM_TOTAL_QTY ]);

        parent::setValue($this->colRate, $item[ ReportGenerator::ROW_BILL_ITEM_RATE ]);

        if(array_key_exists($this->scheduleOfRateTrade->id, $this->scheduleOfRatesNoBuildUp) && array_key_exists($item[ ReportGenerator::ROW_BILL_ITEM_ID ], $this->scheduleOfRatesNoBuildUp[$this->scheduleOfRateTrade->id]))
        {
            $headerText = $this->activeSheet->getCell($this->colProfitRate.$this->headerRow)->getValue();

            if($headerText != self::COL_NAME_SOR_NO_BUILD_UP)
            {
                $this->activeSheet->setCellValue($this->colProfitRate.$this->headerRow, self::COL_NAME_SOR_NO_BUILD_UP);
            }

            parent::setValue($this->colProfitRate, $this->scheduleOfRatesNoBuildUp[$this->scheduleOfRateTrade->id][ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]['rate']);
            parent::setValue($this->colProfitTotal, $this->scheduleOfRatesNoBuildUp[$this->scheduleOfRateTrade->id][ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]['total']);
        }

        if(array_key_exists($this->scheduleOfRateTrade->id, $this->profitFromBillMarkup) && array_key_exists($item[ ReportGenerator::ROW_BILL_ITEM_ID ], $this->profitFromBillMarkup[$this->scheduleOfRateTrade->id]))
        {
            parent::setValue($this->colProfit2Total, $this->profitFromBillMarkup[$this->scheduleOfRateTrade->id][ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]);

            if(!$this->hasBillMarkup)
            {
                $lengthAndMap = $this->addHeaderColumns(array(
                    self::COL_NAME_PROFIT_BILL_MARKUP => array(
                        self::COL_NAME_RATE,
                        self::COL_NAME_TOTAL )
                ), $this->getNextColumn($this->lastCol), $this->headerRow);

                // Update lastCol.
                $this->increment($this->lastCol, $lengthAndMap['numberOfColumns']);

                $this->hasBillMarkup = true;
            }
        }

        if(array_key_exists($item[ ReportGenerator::ROW_BILL_ITEM_ID ], $this->billItemResourceRates) && array_key_exists('resourceRates', $this->billItemResourceRates[ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]))
        {
            parent::setValue($this->colProfitRate, $this->billItemResourceRates[ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]['profitRates']['rate']);
            parent::setValue($this->colProfitTotal, $this->billItemResourceRates[ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]['profitRates']['total']);

            $this->addRelevantResourceColumns($item);
        }
    }

    protected function addRelevantResourceColumns($item)
    {
        foreach($this->billItemResourceRates[ $item[ ReportGenerator::ROW_BILL_ITEM_ID ] ]['resourceRates'] as $resourceName => $resourceRates)
        {
            if( ! array_key_exists($resourceName, $this->resourceColumns) )
            {
                $lengthAndMap = $this->addHeaderColumns(array(
                    $resourceName => array(
                        self::COL_NAME_RATE,
                        self::COL_NAME_TOTAL,
                        self::COL_NAME_WASTAGE_RATE,
                        self::COL_NAME_WASTAGE_TOTAL,
                    )
                ), $this->getNextColumn($this->lastCol), $this->headerRow);

                $this->resourceColumns = array_merge($this->resourceColumns, $lengthAndMap['map']);

                // Update lastCol.
                $this->increment($this->lastCol, $lengthAndMap['numberOfColumns']);
            }

            // Add values to the columns.
            parent::setValue($this->resourceColumns[ $resourceName ][ self::COL_NAME_RATE ], $resourceRates['rate']);
            parent::setValue($this->resourceColumns[ $resourceName ][ self::COL_NAME_TOTAL ], $resourceRates['total']);
            parent::setValue($this->resourceColumns[ $resourceName ][ self::COL_NAME_WASTAGE_RATE ], $resourceRates['wastageRate']);
            parent::setValue($this->resourceColumns[ $resourceName ][ self::COL_NAME_WASTAGE_TOTAL ], $resourceRates['wastageTotal']);
        }
    }

}