<?php
class sfPostContractItemReportGenerator extends sfBuildspaceExcelReportGenerator
{
    public $elementTotals;

    public $colContractAmount = "D";
    public $colPrevPaymentPercent="E";
    public $colPrevPaymentAmount="F";
    public $colWorkDonePercent="G";
    public $colWorkDoneAmount="H";
    public $colCurrentPaymentPercent="I";
    public $colCurrentPaymentAmount="J";

    public $topRightTitle;

    function __construct( $project = null, $savePath = null, $filename = null, $printSettings )
    {
        $filename = ( $filename ) ? $filename : $this->bill->title.'-'.date( 'dmY H_i_s' );

        $savePath = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads';

        parent::__construct( $project, $savePath, $filename, $printSettings );
    }

    public function setParameter($elementTotals)
    {
        $this->elementTotals = $elementTotals;
    }

    public function setTopRightTitle($title = '')
    {
        $this->topRightTitle = $title;
    }

    public function setBillHeader( $billHeader = null, $topLeftTitle, $subTitle ) 
    {
        $billHeader = ( $billHeader ) ? $billHeader: $this->filename;

        //Set Top Header
        $this->activeSheet->setCellValue( $this->firstCol."1", $billHeader );
        $this->activeSheet->mergeCells( $this->firstCol.'1:'.$this->lastCol.'1' );
        $this->activeSheet->getStyle( $this->firstCol.'1:'.$this->lastCol.'1' )->applyFromArray( $this->getProjectTitleStyle() );

        //Set SubTitle
        $this->activeSheet->setCellValue( $this->firstCol."2", $subTitle );
        $this->activeSheet->mergeCells( $this->firstCol.'2:'.$this->lastCol.'2' );
        $this->activeSheet->getStyle( $this->firstCol.'2:'.$this->lastCol.'2' )->applyFromArray( $this->getSubTitleStyle() );

        //Set Left Title
        $this->activeSheet->setCellValue( $this->firstCol."3", $topLeftTitle );
        $this->activeSheet->mergeCells( $this->firstCol.'3:'.$this->colDescription.'3' );
        $this->activeSheet->getStyle( $this->firstCol.'3:'.$this->colDescription.'3' )->applyFromArray( $this->getLeftTitleStyle() );

        //Set Right Title
        $this->activeSheet->setCellValue( $this->colCurrentPaymentPercent."3", $this->topRightTitle );
        $this->activeSheet->mergeCells( $this->colCurrentPaymentPercent.'3:'.$this->colCurrentPaymentAmount.'3' );
        $this->activeSheet->getStyle( $this->colCurrentPaymentPercent.'3:'.$this->colCurrentPaymentAmount.'3' )->applyFromArray( $this->getRightTitleStyle() );
    }

    public function printGrandTotalValue($style)
    {
        parent::setValue( $this->colContractAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('total_per_unit',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['total_per_unit'] : 0);

        parent::setPercentageValue( $this->colPrevPaymentPercent, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('prev_percentage',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['prev_percentage'] / 100 : 0);
        parent::setValue( $this->colPrevPaymentAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('prev_amount',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['prev_amount'] : 0);

        parent::setPercentageValue( $this->colWorkDonePercent, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('up_to_date_percentage',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['up_to_date_percentage'] / 100 : 0);
        parent::setValue( $this->colWorkDoneAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('up_to_date_amount',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['up_to_date_amount'] : 0);

        parent::setPercentageValue( $this->colCurrentPaymentPercent, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('current_percentage',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['current_percentage'] / 100 : 0);
        parent::setValue( $this->colCurrentPaymentAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('current_amount',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['current_amount'] : 0);

        $this->activeSheet->getStyle( $this->colContractAmount.$this->currentRow.":".$this->colCurrentPaymentAmount.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function startBillCounter() 
    {
        $this->currentRow = $this->startRow;
        $this->firstCol = $this->colItem;
        $this->lastCol  = $this->colCurrentPaymentAmount;

        $this->currentElementNo = 0;
        $this->columnSetting = null;
    }

    public function createHeader( $new = false ) 
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );
        $this->activeSheet->setCellValue( $this->colContractAmount.$row, self::COL_NAME_CONTRACT_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colContractAmount )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colPrevPaymentPercent.$row, self::COL_NAME_PREV_PAYMENT );
        $this->activeSheet->mergeCells( $this->colPrevPaymentPercent.$this->currentRow.':'.$this->colPrevPaymentAmount.$this->currentRow );

        $this->activeSheet->setCellValue( $this->colWorkDonePercent.$row, self::COL_NAME_WORKDONE );
        $this->activeSheet->mergeCells( $this->colWorkDonePercent.$this->currentRow.':'.$this->colWorkDoneAmount.$this->currentRow );

        $this->activeSheet->setCellValue( $this->colCurrentPaymentPercent.$row, self::COL_NAME_CURRENT_PAYMENT );
        $this->activeSheet->mergeCells( $this->colCurrentPaymentPercent.$this->currentRow.':'.$this->colCurrentPaymentAmount.$this->currentRow );

        $this->currentRow++;

        $this->activeSheet->setCellValue( $this->colPrevPaymentPercent.$this->currentRow, self::COL_NAME_PERCENT );
        $this->activeSheet->setCellValue( $this->colPrevPaymentAmount.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colPrevPaymentAmount )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colWorkDonePercent.$this->currentRow, self::COL_NAME_PERCENT );
        $this->activeSheet->setCellValue( $this->colWorkDoneAmount.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colWorkDoneAmount )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colCurrentPaymentPercent.$this->currentRow, self::COL_NAME_PERCENT );
        $this->activeSheet->setCellValue( $this->colCurrentPaymentAmount.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colCurrentPaymentAmount )->setWidth( 16 );

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$this->colCurrentPaymentAmount.$this->currentRow )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
        
        $this->activeSheet->mergeCells( $this->colItem.$row.':'.$this->colItem.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colDescription.$row.':'.$this->colDescription.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colContractAmount.$row.':'.$this->colContractAmount.$this->currentRow );

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
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
            $this->currentElementId = $key;

            for($i=1;$i<=$page['item_pages']->count(); $i++)
            {
                if($page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i))
                {
                    $printGrandTotal = (($i+1) == $page['item_pages']->count()) ? true : false;

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
                                    if($prevItemType == BillItem::TYPE_HEADER_N || $prevItemType == BillItem::TYPE_HEADER)
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
                            case BillItem::TYPE_HEADER_N:
                            case BillItem::TYPE_HEADER:

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
        parent::setValue( $this->colContractAmount, $item[sfBuildspacePostContractReportPageElementGenerator::ROW_BILL_ITEM_CONTRACT_AMOUNT]);

        $prevPercentage = (array_key_exists('prev_percentage', $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_PREVIOUS])) ? $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_PREVIOUS]['prev_percentage'] : 0;
        $prevAmount = (array_key_exists('prev_amount', $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_PREVIOUS])) ? $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_PREVIOUS]['prev_amount'] : 0;

        $workDonePercentage = (array_key_exists('up_to_date_percentage', $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE])) ? $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE]['up_to_date_percentage'] : 0;
        $workDoneAmount = (array_key_exists('up_to_date_amount', $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE])) ? $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE]['up_to_date_amount'] : 0;

        $currentPercentage = (array_key_exists('current_percentage', $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_CURRENT])) ? $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_CURRENT]['current_percentage'] : 0;
        $currentAmount = (array_key_exists('current_amount', $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_CURRENT])) ? $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_CURRENT]['current_amount'] : 0;

        parent::setPercentageValue( $this->colPrevPaymentPercent, $prevPercentage / 100);
        parent::setValue( $this->colPrevPaymentAmount, $prevAmount);

        parent::setPercentageValue( $this->colWorkDonePercent, $workDonePercentage / 100);
        parent::setValue( $this->colWorkDoneAmount, $workDoneAmount);

        parent::setPercentageValue( $this->colCurrentPaymentPercent, $currentPercentage / 100);
        parent::setValue( $this->colCurrentPaymentAmount, $currentAmount);
    }
}