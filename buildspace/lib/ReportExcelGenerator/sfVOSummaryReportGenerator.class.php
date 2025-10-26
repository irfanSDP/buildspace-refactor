<?php
class sfVOSummaryReportGenerator extends sfPostContractElementReportGenerator
{
    public $colOmissionAmount = "D";
    public $colAdditionAmount = "E";
    public $voTotal;

    public function setVoTotal($voTotal)
    {
        $this->voTotal = $voTotal;
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

        $this->activeSheet->getStyle( $this->colDescription.$this->currentRow )->applyFromArray( $totalStyle );
        
        $this->printTotalText();        

        $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );

        $this->printGrandTotalValue($newLineStyle);

        $this->currentRow++;

        $this->activeSheet->getStyle( $this->colDescription.$this->currentRow )->applyFromArray( $totalStyle );
        $this->printTotalText('Nett Omission/Addition:');        

        $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );

        $this->printNetAmount($newLineStyle);

        $this->currentRow++;
    }

    public function printNetAmount($style)
    {
        $this->activeSheet->mergeCells( $this->colOmissionAmount.$this->currentRow.':'.$this->colAdditionAmount.$this->currentRow );

        parent::setValue( $this->colOmissionAmount, (array_key_exists('omission',$this->voTotal) && array_key_exists('omission',$this->voTotal)) ? $this->voTotal['addition'] - $this->voTotal['omission'] : 0);

        $this->activeSheet->getStyle( $this->colOmissionAmount.$this->currentRow.":".$this->colAdditionAmount.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function printGrandTotalValue($style)
    {
        parent::setValue( $this->colOmissionAmount, (array_key_exists('omission',$this->voTotal)) ? sprintf('(%s)', number_format($this->voTotal['omission'], 2)) : 0);

        parent::setValue( $this->colAdditionAmount, (array_key_exists('addition',$this->voTotal)) ? $this->voTotal['addition'] : 0);

        $this->activeSheet->getStyle( $this->colOmissionAmount.$this->currentRow.":".$this->colAdditionAmount.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function process( $itemPages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
    {
        $this->setExcelParameter( $lock, $withoutCents );

        $this->totalPage = $totalPage;

        $description    = '';
        $char = '';
        $prevItemType   = '';
        $prevItemLevel  = 0;

        $this->createSheet($header, $subTitle, $topLeftTitle);

        foreach($itemPages as $pageNo => $page)
        {
            if(count($page))
            {
                $this->createNewPage($pageNo);

                foreach($page as $item)
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
            }
        }

        $this->createFooter(true);

        $this->fileInfo = $this->writeExcel();
    }

    public function startBillCounter() 
    {
        $this->currentRow   = $this->startRow;
        $this->firstCol     = $this->colItem;
        $this->lastCol      = $this->colAdditionAmount;

        $this->currentElementNo = 0;
        $this->columnSetting    = null;
    }

    public function createHeader( $new = false )
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );

        $this->activeSheet->setCellValue( $this->colOmissionAmount.$row, self::COL_NAME_AMOUNT );
        $this->activeSheet->mergeCells( $this->colOmissionAmount.$this->currentRow.':'.$this->colAdditionAmount.$this->currentRow );

        $this->currentRow++;

        $this->activeSheet->setCellValue( $this->colOmissionAmount.$this->currentRow, self::COL_NAME_OMISSION );
        $this->activeSheet->setCellValue( $this->colAdditionAmount.$this->currentRow, self::COL_NAME_ADDITION );
        $this->activeSheet->getColumnDimension( $this->colOmissionAmount )->setWidth( 16 );
        $this->activeSheet->getColumnDimension( $this->colAdditionAmount )->setWidth( 16 );

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$this->colAdditionAmount.$this->currentRow )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
        
        $this->activeSheet->mergeCells( $this->colItem.$row.':'.$this->colItem.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colDescription.$row.':'.$this->colDescription.$this->currentRow );

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
    }

    public function processItems($item)
    {
        parent::setValue( $this->colOmissionAmount, ($item[sfBuildspaceVOSummaryReportGenerator::ROW_OMISSION] == 0) ? '-' : sprintf('(%s)', number_format($item[sfBuildspaceVOSummaryReportGenerator::ROW_OMISSION], 2)));

        parent::setValue( $this->colAdditionAmount, ($item[sfBuildspaceVOSummaryReportGenerator::ROW_ADDITION] == 0) ? '-' : $item[sfBuildspaceVOSummaryReportGenerator::ROW_ADDITION]);
    }
}