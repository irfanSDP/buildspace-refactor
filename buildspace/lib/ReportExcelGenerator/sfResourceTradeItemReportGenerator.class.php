<?php
class sfResourceTradeItemReportGenerator extends sfBuildspaceExcelReportGenerator
{
    public $colUnit = "D";
    public $colRate = "F";
    public $colWastage = "H";
    public $colTotalQty = "E";
    public $colTotalCost = "G";
    public $resourceItemTotals;
    public $currentElementId;
    public $currentResourceId;

    public $billElementIdToDescription;
    public $resourceIdToDescription;

    function __construct( $project = null, $resourceItemTotals, $savePath = null, $filename = null, $printSettings )
    {
        $filename = ( $filename ) ? $filename : $this->bill->title.'-'.date( 'dmY H_i_s' );

        $savePath = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->resourceItemTotals = $resourceItemTotals;

        parent::__construct( $project, $savePath, $filename, $printSettings );
    }

    public function setHeaderParameter($billElementIdToDescription, $resourceIdToDescription) 
    {
        $this->billElementIdToDescription = $billElementIdToDescription;

        $this->resourceIdToDescription = $resourceIdToDescription;
    }

    public function setHeaderTitle($topLeftTitle, $topRightTitle)
    {
        $this->activeSheet->setCellValue( $this->firstCol.$this->currentRow, $topLeftTitle );
        $this->activeSheet->mergeCells( $this->firstCol.$this->currentRow.':'.$this->colDescription.$this->currentRow );
        $this->activeSheet->getStyle( $this->firstCol.$this->currentRow.':'.$this->colDescription.$this->currentRow )->applyFromArray( $this->getLeftTitleStyle() );

        $this->activeSheet->setCellValue( $this->colTotalCost.$this->currentRow, $topRightTitle );
        $this->activeSheet->mergeCells( $this->colTotalCost.$this->currentRow.':'.$this->lastCol.$this->currentRow );
        $this->activeSheet->getStyle( $this->colTotalCost.$this->currentRow.':'.$this->lastCol.$this->currentRow )->applyFromArray( $this->getRightTitleStyle() );
        
        $this->currentRow++;
    }

    public function printGrandTotalValue($style)
    {
        $this->activeSheet->getStyle( $this->colEstimate.$this->currentRow.":".$currCol.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function startBillCounter() 
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colItem;
        $this->lastCol    = $this->colWastage;

        $this->currentElementNo = 0;
        $this->columnSetting = null;
    }

    public function createHeader( $new = false ) 
    {
        $this->currentRow++;
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );
        $this->activeSheet->setCellValue( $this->colUnit.$row, self::COL_NAME_UNIT );

        $this->activeSheet->setCellValue( $this->colRate.$row, self::COL_NAME_RATE );
        $this->activeSheet->setCellValue( $this->colWastage.$row, self::COL_NAME_WASTAGE );
        $this->activeSheet->setCellValue( $this->colTotalQty.$row, self::COL_NAME_TOTAL_QTY );
        $this->activeSheet->setCellValue( $this->colTotalCost.$row, self::COL_NAME_TOTAL_COST );

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$this->lastCol.$row )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 9 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
        $this->activeSheet->getColumnDimension( $this->colUnit )->setWidth( 10 );
        $this->activeSheet->getColumnDimension( $this->colRate )->setWidth( 15 );
        $this->activeSheet->getColumnDimension( $this->colWastage )->setWidth( 13 );
        $this->activeSheet->getColumnDimension( $this->colTotalQty )->setWidth( 15 );
        $this->activeSheet->getColumnDimension( $this->colTotalCost )->setWidth( 15 );
    }

    public function process( $pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
    {
        $this->setExcelParameter( $lock, $withoutCents );

        $this->totalPage = $totalPage;

        $description = '';
        $char = '';
        $prevItemType = '';
        $prevItemLevel = 0;

        $this->createSheet($header, $subTitle, $topLeftTitle);

        $pageNo = 1;

        foreach($pages as $key => $elements)
        {
            $this->currentResourceId = $key;

            foreach($elements as $billElementId => $page)
            {
                $this->currentElementId = $billElementId;

                $topLeftTitle = (array_key_exists($billElementId, $this->billElementIdToDescription)) ? $this->billElementIdToDescription[$billElementId] : '';

                $topRightTitle = (array_key_exists($key, $this->resourceIdToDescription)) ? $this->resourceIdToDescription[$key] : '';

                $this->setHeaderTitle($topLeftTitle, $topRightTitle);

                for($i=1;$i<=$page['item_pages']->count(); $i++)
                {
                    if($page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i))
                    {
                        $printGrandTotal = false;

                        $this->createNewPage($pageNo, false, 0);

                        $itemPage = $page['item_pages']->offsetGet($i);

                        foreach($itemPage as $item)
                        {
                            $itemType = $item[4];

                            switch($itemType)
                            {
                                case self::ROW_TYPE_BLANK:

                                    if($description != '' && $prevItemType != '')
                                    {
                                        if($prevItemType == ResourceItem::TYPE_HEADER)
                                        {
                                            $this->newItem();

                                            parent::setChar($char);

                                            if (strpos($description,$this->printSettings['layoutSetting']['contdPrefix']) !== false)
                                            {
                                                $this->setItemHead( $description,  $prevItemType, $prevItemLevel );
                                            }
                                            else
                                            {
                                                $this->setItemHead( $description,  $prevItemType, $prevItemLevel, true );
                                            }
                                        }

                                        $description = '';
                                        $char = '';
                                    }
                                break;
                                case self::ROW_TYPE_ELEMENT:

                                    if (strpos($item[2],$this->printSettings['layoutSetting']['contdPrefix']) !== false)
                                    {
                                        $this->setElementTitle($this->printSettings['layoutSetting']['contdPrefix']);
                                    }
                                    else
                                    {
                                        $this->setElement(array('description' => $item[2]));
                                    }

                                break;
                                case ResourceItem::TYPE_HEADER:

                                    $description.=$item[2]."\n";
                                    $char.=$item[1];
                                    $prevItemType = $item[4];
                                    $prevItemLevel = $item[3];

                                break;
                                default:

                                    $description.=$item[2]."\n";
                                    $char.=$item[1];

                                    if($item[0])
                                    {
                                        $this->newItem();

                                        $this->setItem( $description,  $itemType , $item[3], $char);

                                        $this->processItems($item);

                                        $description = '';
                                        $char = '';
                                    }

                                break;
                            }

                        }

                        if($printGrandTotal)
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

    public function processItems($item)
    {
        parent::setValue( $this->colRate, $item[6]);

        parent::setUnit( $item[5]);

        parent::setPercentageValue( $this->colWastage, $item[7]/100);

        parent::setNormalQtyValue( $this->colTotalQty, $item[8]);

        parent::setValue( $this->colTotalCost, $item[9]);
    }
}