<?php
class sfVOItemReportWithClaimGenerator extends sfBuildspaceExcelReportGenerator
{
    public $variationTotals;

    public $colNet = "D";
    public $colPercentagePrevious ="E";
    public $colAmountPrevious ="F";
    public $colPercentageWorkDone ="G";
    public $colAmountWorkDone ="H";
    public $colPercentageCurrent="I";
    public $colAmountCurrent="J";

    public $currentVariationId = null;

    function __construct( $project = null, $savePath = null, $filename = null, $printSettings )
    {
        $filename = ( $filename ) ? $filename : $this->bill->title.'-'.date( 'dmY H_i_s' );

        $savePath = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads';

        parent::__construct( $project, $savePath, $filename, $printSettings );
    }

    public function setParameter($variationTotals)
    {
        $this->variationTotals = $variationTotals;
    }

    public function printGrandTotalValue($style)
    {
        $net = (array_key_exists($this->currentVariationId, $this->variationTotals) && array_key_exists('net',$this->variationTotals[$this->currentVariationId])) ? $this->variationTotals[$this->currentVariationId]['net'] : 0;

        parent::setValue( $this->colNet, $net);

        $previousAmount  = (array_key_exists($this->currentVariationId, $this->variationTotals) && array_key_exists('previous_amount',$this->variationTotals[$this->currentVariationId])) ? $this->variationTotals[$this->currentVariationId]['previous_amount'] : 0;
        $previousPercent = ($net != 0) ? $previousAmount / $net * 100 : 0;

        parent::setPercentageValue($this->colPercentagePrevious, $previousPercent / 100);
        parent::setValue( $this->colAmountPrevious, $previousAmount);

        $workDoneAmount  = (array_key_exists($this->currentVariationId, $this->variationTotals) && array_key_exists('workdone_amount',$this->variationTotals[$this->currentVariationId])) ? $this->variationTotals[$this->currentVariationId]['workdone_amount'] : 0;
        $workDonePercent = ($net != 0) ? $workDoneAmount / $net * 100 : 0;

        parent::setPercentageValue($this->colPercentageWorkDone, $workDonePercent / 100);
        parent::setValue( $this->colAmountWorkDone, $workDoneAmount);

        $currentAmount  = (array_key_exists($this->currentVariationId, $this->variationTotals) && array_key_exists('current_amount',$this->variationTotals[$this->currentVariationId])) ? $this->variationTotals[$this->currentVariationId]['current_amount'] : 0;
        $currentPercent = ($net != 0) ? $currentAmount / $net * 100 : 0;

        parent::setPercentageValue($this->colPercentageCurrent, $currentPercent / 100);
        parent::setValue( $this->colAmountCurrent, $currentAmount);

        $this->activeSheet->getStyle( $this->colRate.$this->currentRow.":".$this->colAmountCurrent.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function startBillCounter() 
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colItem;
        $this->lastCol    = $this->colAmountCurrent;

        $this->currentElementNo = 0;
        $this->columnSetting = null;
    }

    public function createHeader( $new = false ) 
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );
        $this->activeSheet->setCellValue( $this->colNet.$row, "Nett Omission/Addition" );

        $this->activeSheet->setCellValue( $this->colPercentagePrevious.$row, self::COL_NAME_PREV_PAYMENT );
        $this->activeSheet->mergeCells( $this->colPercentagePrevious.$this->currentRow.':'.$this->colAmountPrevious.$this->currentRow );

        $this->activeSheet->setCellValue( $this->colPercentageWorkDone.$row, self::COL_NAME_WORKDONE );
        $this->activeSheet->mergeCells( $this->colPercentageWorkDone.$this->currentRow.':'.$this->colAmountWorkDone.$this->currentRow );

        $this->activeSheet->setCellValue( $this->colPercentageCurrent.$row, self::COL_NAME_CURRENT_PAYMENT );
        $this->activeSheet->mergeCells( $this->colPercentageCurrent.$this->currentRow.':'.$this->colAmountCurrent.$this->currentRow );

        $this->currentRow++;

        $this->activeSheet->setCellValue( $this->colPercentagePrevious.$this->currentRow, self::COL_NAME_PERCENT );
        $this->activeSheet->setCellValue( $this->colAmountPrevious.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colAmountPrevious )->setWidth( 16 );
        $this->activeSheet->getColumnDimension( $this->colPercentagePrevious )->setWidth( 14 );

        $this->activeSheet->setCellValue( $this->colPercentageWorkDone.$this->currentRow, self::COL_NAME_PERCENT );
        $this->activeSheet->setCellValue( $this->colAmountWorkDone.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colAmountWorkDone )->setWidth( 16 );
        $this->activeSheet->getColumnDimension( $this->colPercentageWorkDone )->setWidth( 14 );

        $this->activeSheet->setCellValue( $this->colPercentageCurrent.$this->currentRow, self::COL_NAME_PERCENT );
        $this->activeSheet->setCellValue( $this->colAmountCurrent.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colAmountCurrent )->setWidth( 16 );
        $this->activeSheet->getColumnDimension( $this->colPercentageCurrent )->setWidth( 14 );

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$this->colAmountCurrent.$this->currentRow )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
        
        $this->activeSheet->mergeCells( $this->colItem.$row.':'.$this->colItem.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colDescription.$row.':'.$this->colDescription.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colNet.$row.':'.$this->colNet.$this->currentRow );

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
        $this->activeSheet->getColumnDimension( $this->colNet )->setWidth( 16 );
    }

    public function process( $pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
    {
        $this->setExcelParameter( $lock, $withoutCents );

        $this->totalPage = $totalPage;

        $description = '';
        $char = '';
        $char = '';
        $prevItemType = '';
        $prevItemLevel = 0;

        $this->createSheet($header, $subTitle, $topLeftTitle);

        $pageNo = 1;

        foreach($pages as $key => $page)
        {
            $this->currentVariationId = $key;

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
                                    if($prevItemType == VariationOrderItem::TYPE_HEADER)
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
                            case VariationOrderItem::TYPE_HEADER:

                                $description.= str_replace(array('<b>', '</b>'), "", $item[2])."\n";
                                $prevItemType = $item[4];
                                $prevItemLevel = $item[3];

                            break;
                            default:

                                $description.= str_replace(array('<b>', '</b>'), "", $item[2])."\n";
                                $char.= $item[1];

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
        $net  = $item ? $item[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_NET] : 0;

        parent::setValue($this->colNet, $net);

        parent::setPercentageValue($this->colPercentagePrevious, $item[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_PREVIOUS]['percentage'] / 100);

        parent::setValue($this->colAmountPrevious, $item[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_PREVIOUS]['amount']);

        parent::setPercentageValue($this->colPercentageWorkDone, $item[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_WORKDONE]['percentage'] / 100);

        parent::setValue($this->colAmountWorkDone, $item[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_WORKDONE]['amount']);

        parent::setPercentageValue($this->colPercentageCurrent, $item[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_CURRENT]['percentage'] / 100);

        parent::setValue($this->colAmountCurrent, $item[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_CURRENT]['amount']);
    }
}