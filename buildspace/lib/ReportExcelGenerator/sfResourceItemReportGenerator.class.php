<?php
class sfResourceItemReportGenerator extends sfBuildspaceExcelReportGenerator
{
    public $colUnit = "D";
    public $colRate = "E";
    public $colWastage = "F";
    public $colTotalQty = "G";
    public $colTotalCost = "H";
    public $colClaimedQuantity = "I";
    public $colClaimedAmount = "J";
    public $resourceItemTotals;
    public $currentTradeId;
    protected $includeClaimInformation = false;

    function __construct( $project = null, $resourceItemTotals, $savePath = null, $filename = null, $printSettings, $includeClaimInformation = false)
    {
        $filename = ( $filename ) ? $filename : $this->bill->title.'-'.date( 'dmY H_i_s' );

        $savePath = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->resourceItemTotals = $resourceItemTotals;

        $this->includeClaimInformation = $includeClaimInformation;

        parent::__construct( $project, $savePath, $filename, $printSettings );
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
        $this->lastCol    = (!$this->includeClaimInformation) ? $this->colTotalCost : $this->colClaimedAmount;

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

        if($this->includeClaimInformation)
        {
            $this->activeSheet->setCellValue( $this->colClaimedQuantity.$row, self::COL_NAME_CLAIMED_QTY );
            $this->activeSheet->setCellValue( $this->colClaimedAmount.$row, self::COL_NAME_CLAIMED_AMOUNT );
        }

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

        if( $this->includeClaimInformation )
        {
            $this->activeSheet->getColumnDimension( $this->colClaimedQuantity )->setWidth( 15 );
            $this->activeSheet->getColumnDimension( $this->colClaimedAmount )->setWidth( 20 );
        }
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

        foreach($pages as $key => $page)
        {
            $this->currentTradeId = $key;

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
        //write to Excel File
        $this->fileInfo = $this->writeExcel();
    }

    public function processItems($item)
    {
        parent::setValue( $this->colRate, $item[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_RATE]);

        parent::setUnit( $item[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_UNIT]);

        parent::setPercentageValue( $this->colWastage, $item[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_WASTAGE]/100);

        parent::setNormalQtyValue( $this->colTotalQty, $item[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_TOTAL_QTY]);

        parent::setValue( $this->colTotalCost, $item[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_TOTAL_COST]);

        if( $this->includeClaimInformation )
        {
            parent::setNormalQtyValue( $this->colClaimedQuantity, $item[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_CLAIM_QTY]);

            parent::setValue( $this->colClaimedAmount, $item[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_CLAIM_AMOUNT]);
        }
    }
}