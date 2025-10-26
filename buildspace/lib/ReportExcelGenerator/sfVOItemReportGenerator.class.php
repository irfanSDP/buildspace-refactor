<?php
class sfVOItemReportGenerator extends sfBuildspaceExcelReportGenerator
{
    public $variationTotals;

    public $colUnit = "D";
    public $colRate="E";
    public $colQtyOmission ="F";
    public $colAmountOmission ="G";
    public $colQtyAddition ="H";
    public $colAmountAddition="I";

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

    public function printGrandTotal()
    {
        $newLineStyle = array(
            'borders' => array(
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                )
            )
        );

        $totalStyle = array(
            'font' => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'wrapText' => true
            )
        );

        $this->activeSheet->getStyle( $this->colUnit.$this->currentRow )->applyFromArray( $totalStyle );
        $this->activeSheet->mergeCells( $this->colUnit.$this->currentRow.':'.$this->colRate.$this->currentRow );
        $this->printTotalText();        

        $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );
        $this->printGrandTotalValue($newLineStyle);

        $this->currentRow++;

        $this->activeSheet->getStyle( $this->colUnit.$this->currentRow )->applyFromArray( $totalStyle );
        $this->activeSheet->mergeCells( $this->colUnit.$this->currentRow.':'.$this->colRate.$this->currentRow );
        $this->printTotalText('Nett Omission/Addition:');        

        $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );
        $this->printNetAmount($newLineStyle);

        $this->currentRow++;
    }

    public function printTotalText($title = false)
    {
        $this->activeSheet->setCellValue( $this->colUnit.$this->currentRow, ($title) ? $title : "Total:" );
    }

    public function printNetAmount($style)
    {
        $this->activeSheet->mergeCells( $this->colQtyOmission.$this->currentRow.':'.$this->colAmountAddition.$this->currentRow );

        parent::setValue( $this->colQtyOmission, (array_key_exists($this->currentVariationId, $this->variationTotals) && array_key_exists('omission_amount',$this->variationTotals[$this->currentVariationId]) && array_key_exists($this->currentVariationId, $this->variationTotals) && array_key_exists('addition_amount',$this->variationTotals[$this->currentVariationId])) ? $this->variationTotals[$this->currentVariationId]['addition_amount'] - $this->variationTotals[$this->currentVariationId]['omission_amount'] : 0);

        $this->activeSheet->getStyle( $this->colQtyOmission.$this->currentRow.":".$this->colAmountAddition.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function printGrandTotalValue($style)
    {
        $this->activeSheet->mergeCells( $this->colQtyOmission.$this->currentRow.':'.$this->colAmountOmission.$this->currentRow );
        parent::setValue( $this->colQtyOmission, (array_key_exists($this->currentVariationId, $this->variationTotals) && array_key_exists('omission_amount',$this->variationTotals[$this->currentVariationId])) ? sprintf('(%s)', number_format($this->variationTotals[$this->currentVariationId]['omission_amount'] ,2)) : 0);

        $this->activeSheet->mergeCells( $this->colQtyAddition.$this->currentRow.':'.$this->colAmountAddition.$this->currentRow );
        parent::setValue( $this->colQtyAddition, (array_key_exists($this->currentVariationId, $this->variationTotals) && array_key_exists('addition_amount',$this->variationTotals[$this->currentVariationId])) ? $this->variationTotals[$this->currentVariationId]['addition_amount'] : 0);

        $this->activeSheet->getStyle( $this->colQtyOmission.$this->currentRow.":".$this->colAmountAddition.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function startBillCounter() 
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colItem;
        $this->lastCol    = $this->colAmountAddition;

        $this->currentElementNo = 0;
        $this->columnSetting = null;
    }

    public function createHeader( $new = false ) 
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );
        $this->activeSheet->setCellValue( $this->colUnit.$row, self::COL_NAME_UNIT );
        $this->activeSheet->setCellValue( $this->colRate.$row, self::COL_NAME_RATE );

        $this->activeSheet->setCellValue( $this->colQtyOmission.$row, self::COL_NAME_OMISSION );
        $this->activeSheet->mergeCells( $this->colQtyOmission.$this->currentRow.':'.$this->colAmountOmission.$this->currentRow );

        $this->activeSheet->setCellValue( $this->colQtyAddition.$row, self::COL_NAME_ADDITION );
        $this->activeSheet->mergeCells( $this->colQtyAddition.$this->currentRow.':'.$this->colAmountAddition.$this->currentRow );

        $this->currentRow++;

        $this->activeSheet->setCellValue( $this->colQtyOmission.$this->currentRow, self::COL_NAME_QTY );
        $this->activeSheet->setCellValue( $this->colAmountOmission.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colAmountOmission )->setWidth( 16 );
        $this->activeSheet->getColumnDimension( $this->colQtyOmission )->setWidth( 14 );

        $this->activeSheet->setCellValue( $this->colQtyAddition.$this->currentRow, self::COL_NAME_QTY );
        $this->activeSheet->setCellValue( $this->colAmountAddition.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colAmountAddition )->setWidth( 16 );
        $this->activeSheet->getColumnDimension( $this->colQtyAddition )->setWidth( 14 );

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$this->colAmountAddition.$this->currentRow )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
        
        $this->activeSheet->mergeCells( $this->colItem.$row.':'.$this->colItem.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colDescription.$row.':'.$this->colDescription.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colUnit.$row.':'.$this->colUnit.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colRate.$row.':'.$this->colRate.$this->currentRow );

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
        $this->activeSheet->getColumnDimension( $this->colUnit )->setWidth( 14 );
        $this->activeSheet->getColumnDimension( $this->colRate )->setWidth( 16 );
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
        $rate = $item ? $item[sfBuildspaceVOItemsReportGenerator::ROW_BILL_ITEM_RATE] : 0;
        $unit = $item ? $item[sfBuildspaceVOItemsReportGenerator::ROW_BILL_ITEM_UNIT] : '';

        parent::setValue($this->colRate, $rate);

        parent::setUnit($unit);

        parent::setNormalQtyValue($this->colQtyOmission, ($item[sfBuildspaceVOItemsReportGenerator::ROW_OMISSION]['qty'] == 0) ? '-' : $item[sfBuildspaceVOItemsReportGenerator::ROW_OMISSION]['qty']);

        parent::setValue($this->colAmountOmission, ($item[sfBuildspaceVOItemsReportGenerator::ROW_OMISSION]['amount'] == 0) ? '-' : sprintf('(%s)', number_format($item[sfBuildspaceVOItemsReportGenerator::ROW_OMISSION]['amount'] ,2)));

        parent::setNormalQtyValue($this->colQtyAddition, ($item[sfBuildspaceVOItemsReportGenerator::ROW_ADDITION]['qty'] == 0) ? '-' : $item[sfBuildspaceVOItemsReportGenerator::ROW_ADDITION]['qty']);

        parent::setValue($this->colAmountAddition, ($item[sfBuildspaceVOItemsReportGenerator::ROW_ADDITION]['amount'] == 0) ? '-' : $item[sfBuildspaceVOItemsReportGenerator::ROW_ADDITION]['amount']);
    }
}