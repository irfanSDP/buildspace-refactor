<?php

class sfScheduleOfRateBillItemExcelReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colItem = "B";
    public $colDescription = "C";
    public $colUnit = "D";
    public $colEstimateRate = "E";

    public $bill;
    public $tenderers;
    public $contractorRates;
    public $currentElementId;

    function __construct($project = null, $bill = null, $savePath = null, $filename = null, $printSettings)
    {
        $this->bill = $bill;

        $filename = ( $filename ) ? $filename : $this->bill->title . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        parent::__construct($project, $savePath, $filename, $printSettings);
    }

    /**
     * Sets the parameters for the class.
     *
     * @param $tenderers
     * @param $contractorRates
     */
    public function setParameter($tenderers, $contractorRates)
    {
        $this->tenderers = $tenderers;

        $this->contractorRates = $contractorRates;
    }

    /**
     * Starts the bill counter.
     * This sets the first row, currentElementNo,  and currentRow to the starting.
     * Also determines the first and last column.
     */
    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol = $this->colItem;

        if( count($this->tenderers) )
        {
            $currentColumn = $this->colEstimateRate;

            foreach($this->tenderers as $tenderer)
            {
                // One for 'rate' column
                ++$currentColumn;
            }

            $this->lastCol = $currentColumn;
        }
        else
        {
            $this->lastCol = $this->colEstimateRate;
        }

        $this->currentElementNo = 0;
    }

    /**
     * Creates the header.
     *
     * @param bool $new
     */
    public function createHeader($new = false)
    {
        $this->currentRow++;
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue($this->colItem . $row, self::COL_NAME_NO);

        $this->activeSheet->setCellValue($this->colDescription . $row, self::COL_NAME_DESCRIPTION);

        $this->activeSheet->setCellValue($this->colUnit . $row, self::COL_NAME_UNIT);

        $this->activeSheet->setCellValue($this->colEstimateRate . $row, self::COL_NAME_ESTIMATE);

        $currentColumn = $this->colEstimateRate;

        if( count($this->tenderers) )
        {
            foreach($this->tenderers as $tenderer)
            {
                ++$currentColumn;

                $tendererName = ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : $tenderer['name'];

                if( isset( $tenderer['selected'] ) AND $tenderer['selected'] )
                {
                    // set the selected tenderer a blue marker
                    $objRichText = new PHPExcel_RichText();
                    $objBold = $objRichText->createTextRun(( ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : $tenderer['name'] ));
                    $objBold->getFont()->setBold(true)->getColor()->setRGB('0000FF');

                    $tendererName = $objRichText;
                }

                // column for rate
                $this->activeSheet->setCellValue($currentColumn . $row, $tendererName);

                $this->activeSheet->getColumnDimension($currentColumn)->setWidth(15);
            }
        }

        //Set header styling
        $this->activeSheet->getStyle($this->colItem . $row . ':' . $currentColumn . $row)->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colItem)->setWidth(9);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
        $this->activeSheet->getColumnDimension($this->colUnit)->setWidth(7);
        $this->activeSheet->getColumnDimension($this->colEstimateRate)->setWidth(15);
    }

    /**
     * Creates the sheet, processes the items and creates the footer for all items' pages.
     *
     * @param      $pages
     * @param bool $lock
     * @param      $header
     * @param      $subTitle
     * @param      $topLeftTitle
     * @param      $withoutCents
     * @param      $totalPage
     */
    public function process($pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
    {
        $this->setExcelParameter($lock, $withoutCents);

        $this->totalPage = $totalPage;

        $description = '';
        $char = '';
        $prevItemType = '';
        $prevItemLevel = 0;

        $this->createSheet($header, $subTitle, $topLeftTitle);

        $pageNo = 1;

        //each page is for one element
        foreach($pages as $key => $page)
        {
            $this->currentElementId = $key;

            for($i = 1; $i <= $page['item_pages']->count(); $i++)
            {
                if( $page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i) )
                {
                    $this->createNewPage($pageNo, false, 0);
                    $itemPage = $page['item_pages']->offsetGet($i);

                    foreach($itemPage as $item)
                    {
                        $itemType = $item[ self::ROW_BILL_ITEM_TYPE ];

                        switch($itemType)
                        {
                            case self::ROW_TYPE_BLANK:

                                if( $description != '' && $prevItemType != '' )
                                {
                                    if( $prevItemType == ScheduleOfRateBillItem::TYPE_HEADER_N || $prevItemType == ScheduleOfRateBillItem::TYPE_HEADER )
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

                                if( strpos($item[ self::ROW_BILL_ITEM_DESCRIPTION ], $this->printSettings['layoutSetting']['contdPrefix']) !== false )
                                {
                                    $this->setElementTitle($this->printSettings['layoutSetting']['contdPrefix']);
                                }
                                else
                                {
                                    $this->setElement(array( 'description' => $item[ self::ROW_BILL_ITEM_DESCRIPTION ] ));
                                }

                                break;
                            case ScheduleOfRateBillItem::TYPE_HEADER_N:
                            case ScheduleOfRateBillItem::TYPE_HEADER:

                                $description .= $item[ self::ROW_BILL_ITEM_DESCRIPTION ] . "\n";
                                $prevItemType = $item[ self::ROW_BILL_ITEM_TYPE ];
                                $prevItemLevel = $item[ self::ROW_BILL_ITEM_LEVEL ];

                                break;
                            default:

                                $description .= $item[ self::ROW_BILL_ITEM_DESCRIPTION ] . "\n";
                                $char .= $item[ self::ROW_BILL_ITEM_ROW_IDX ];

                                if( $item[ self::ROW_BILL_ITEM_ID ] )
                                {
                                    $this->newItem();

                                    $this->setItem($description, $itemType, $item[ self::ROW_BILL_ITEM_LEVEL ], $char);

                                    $this->processItems($item);

                                    $description = '';
                                    $char = '';
                                }

                                break;
                        }

                    }

                    $this->createFooter();

                    $pageNo++;
                }
            }
        }

        //write to Excel File
        $this->fileInfo = $this->writeExcel();
    }

    /**
     * Sets the values for the unit, estimated rate and tendered rates for an item.
     *
     * @param $item
     */
    public function processItems($item)
    {
        parent::setUnit($item[ self::ROW_BILL_ITEM_UNIT ]);

        parent::setValue($this->colEstimateRate, $item[ self::ROW_BILL_ITEM_RATE ]);

        $currentColumn = $this->colEstimateRate;

        if( count($this->tenderers) )
        {
            $itemId = $item[ self::ROW_BILL_ITEM_ID ];
            $lowestTendererId = null;
            $highestTendererId = null;
            $listOfRates = array();

            foreach($this->tenderers as $k => $tenderer)
            {
                if( array_key_exists($tenderer['id'], $this->contractorRates) && array_key_exists($itemId, $this->contractorRates[ $tenderer['id'] ]) )
                {
                    array_push($listOfRates, $this->contractorRates[ $tenderer['id'] ][ $itemId ]);
                }
            }

            //calculate highest and lowest rates
            $lowestRate = count($listOfRates) ? min($listOfRates) : 0;
            $highestRate = count($listOfRates) ? max($listOfRates) : 0;

            $lowestTendererId = $this->tenderers[ array_search($lowestRate, $listOfRates) ]['id'];
            $highestTendererId = $this->tenderers[ array_search($highestRate, $listOfRates) ]['id'];

            foreach($this->tenderers as $tenderer)
            {
                ++$currentColumn;
                $rateColumn = $currentColumn;
                $tendererRateValue = isset( $this->contractorRates[ $tenderer['id'] ][ $item[ self::ROW_BILL_ITEM_ID ] ] ) ? $this->contractorRates[ $tenderer['id'] ][ $item[ self::ROW_BILL_ITEM_ID ] ] : null;

                if( $lowestTendererId == $highestTendererId )
                {
                    parent::setValue($rateColumn, $tendererRateValue);
                }
                else
                {
                    if( $tenderer['id'] == $lowestTendererId )
                    {
                        parent::setLowestValue($rateColumn, $tendererRateValue);
                    }
                    else if( $tenderer['id'] == $highestTendererId )
                    {
                        parent::setHighestValue($rateColumn, $tendererRateValue);
                    }
                    else
                    {
                        parent::setValue($rateColumn, $tendererRateValue);
                    }
                }

            }
        }
    }

}