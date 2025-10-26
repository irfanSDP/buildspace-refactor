<?php
class sfPostContractElementReportWithClaimGenerator extends sfPostContractElementReportGenerator
{
    public $unitNames;

    public function setUnitNames($unitNames)
    {
        $this->unitNames = $unitNames;
    }

    public function printGrandTotalValue($style)
    {
        $currCol  = $this->colDescription;

        if(count($this->billColumnSettings))
        {
            $counter  = 1;
            $firstCol = false;

            foreach($this->billColumnSettings as $column)
            {
                if(array_key_exists($column['id'], $this->unitNames) && count($this->unitNames[$column['id']]))
                {
                    $currCol++;

                    if($counter == 1)
                    {
                        $firstCol = $currCol;
                    }

                    parent::setValue( $currCol, (array_key_exists($column['id'], $this->typeTotals) && array_key_exists('total_per_unit', $this->typeTotals[$column['id']])) ? $this->typeTotals[$column['id']]['total_per_unit'] : 0);

                    $currCol++;
                    parent::setPercentageValue( $currCol, (array_key_exists($column['id'], $this->typeTotals) && array_key_exists('up_to_date_percentage', $this->typeTotals[$column['id']])) ? $this->typeTotals[$column['id']]['up_to_date_percentage'] / 100 : 0);

                    $currCol++;
                    parent::setValue( $currCol, (array_key_exists($column['id'], $this->typeTotals) && array_key_exists('up_to_date_amount', $this->typeTotals[$column['id']])) ? $this->typeTotals[$column['id']]['up_to_date_amount'] : 0);
                
                    if(array_key_exists('unit_totals', $this->typeTotals[$column['id']]) && count($this->typeTotals[$column['id']]['unit_totals']))
                    {
                        foreach ($this->typeTotals[$column['id']]['unit_totals'] as $key => $total) 
                        {
                            $currCol++;
                            parent::setPercentageValue( $currCol, (array_key_exists('up_to_date_percentage', $total)) ? $total['up_to_date_percentage'] / 100 : 0);
                        }
                    }

                    $counter++;
                }
            }

            if($firstCol)
            {
                $this->activeSheet->getStyle( $firstCol.$this->currentRow.":".$currCol.$this->currentRow )
                    ->applyFromArray( $style );
            }
        }
    }

    public function startBillCounter() 
    {
        $this->currentRow = $this->startRow;
        $this->firstCol = $this->colItem;

        if(count($this->billColumnSettings))
        {
            $currCol = $this->colDescription;

            foreach($this->billColumnSettings as $column)
            {
                if(array_key_exists($column['id'], $this->unitNames) && count($this->unitNames[$column['id']]))
                {
                    for($i=1; $i<=3; $i++)
                    {
                        $currCol++;
                    }

                    foreach($this->unitNames[$column['id']] as $name)
                    {
                        $currCol++;
                    }
                }
            }

            $this->lastCol = $currCol;
        }
        else
        {
            $this->lastCol = $this->colDescription;
        }

        $this->currentElementNo = 0;
        $this->columnSetting = null;
    }

    public function createHeader( $new = false ) 
    {
        $row = $currentRow = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );

        $currCol  = $this->colDescription;

        if(count($this->billColumnSettings))
        {
            foreach($this->billColumnSettings as $column)
            {
                if(array_key_exists($column['id'], $this->unitNames) && count($this->unitNames[$column['id']]))
                {
                    $currentRow = $startRow = $row;

                    $currCol++;
                    $lastCol = $currCol;

                    for($i = 1; $i < (3+count($this->unitNames[$column['id']])); $i++)
                    {
                        $lastCol++;
                    }

                    //setup first header row
                    $this->activeSheet->setCellValue( $currCol.$currentRow, $column['name'] );
                    $this->activeSheet->mergeCells( $currCol.$currentRow.':'.$lastCol.$currentRow );

                    //Setup Second Header Row
                    $currentRow++;
                    $firstCol = $currCol;
                    $secondRow = $currentRow;

                    $this->activeSheet->setCellValue( $currCol.$currentRow, self::COL_NAME_CONTRACT_AMOUNT );
                    $this->activeSheet->getColumnDimension( $currCol )->setWidth( 15 );

                    $currCol++;
                    $secondCol = $currCol;

                    $this->activeSheet->setCellValue( $secondCol.$currentRow, self::COL_NAME_WORKDONE );

                    $currCol++;
                    $thirdCol = $currCol;

                    $this->activeSheet->mergeCells( $secondCol.$currentRow.':'.$thirdCol.$currentRow );

                    foreach($this->unitNames[$column['id']] as $k => $name)
                    {
                        $currCol++;

                        $this->activeSheet->setCellValue( $currCol.$currentRow, $name );
                    }

                    //Setup Third Header Row
                    $currentRow++;

                    $this->activeSheet->mergeCells( $firstCol.$secondRow.':'.$firstCol.$currentRow );
                    $this->activeSheet->setCellValue( $secondCol.$currentRow, self::COL_NAME_PERCENT );
                    $this->activeSheet->getColumnDimension( $secondCol )->setWidth( 10 );

                    $currCol = $thirdCol;

                    $this->activeSheet->setCellValue( $thirdCol.$currentRow, self::COL_NAME_AMOUNT );
                    $this->activeSheet->getColumnDimension( $thirdCol )->setWidth( 15 );

                    foreach($this->unitNames[$column['id']] as $k => $name)
                    {
                        $currCol++;

                        $this->activeSheet->setCellValue( $currCol.$currentRow, "%" );
                    } 
                }
            }
        }

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$currCol.($currentRow) )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );

        $this->activeSheet->mergeCells( $this->colItem.$this->currentRow.':'.$this->colItem.$currentRow );
        $this->activeSheet->mergeCells( $this->colDescription.$this->currentRow.':'.$this->colDescription.$currentRow );

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );

        if(count($this->billColumnSettings))
        {
            $this->currentRow+=2;
        }
        else
        {
            $this->currentRow++;
        }
    }

    public function processItems($item)
    {
        $currCol  = $this->colDescription;

        if(count($this->billColumnSettings))
        {
            foreach($this->billColumnSettings as $column)
            {
                if(array_key_exists($column['id'], $this->unitNames) && count($this->unitNames[$column['id']]))
                {
                    $currCol++;
                    parent::setValue( $currCol, (array_key_exists($item[0], $this->elementTotals) && array_key_exists($column['id'], $this->elementTotals[$item[0]]) && array_key_exists('grand_total', $this->elementTotals[$item[0]][$column['id']])) ? $this->elementTotals[$item[0]][$column['id']]['grand_total'] : 0);

                    $currCol++;
                    parent::setPercentageValue( $currCol, (array_key_exists($item[0], $this->elementTotals) && array_key_exists($column['id'], $this->elementTotals[$item[0]]) && array_key_exists('type_total_percentage', $this->elementTotals[$item[0]][$column['id']])) ? $this->elementTotals[$item[0]][$column['id']]['type_total_percentage'] / 100 : 0);

                    $currCol++;
                    parent::setValue( $currCol, (array_key_exists($item[0], $this->elementTotals) && array_key_exists($column['id'], $this->elementTotals[$item[0]]) && array_key_exists('type_total_up_to_date_amount', $this->elementTotals[$item[0]][$column['id']])) ? $this->elementTotals[$item[0]][$column['id']]['type_total_up_to_date_amount'] : 0);
                
                    if(array_key_exists('unit_total', $this->elementTotals[$item[0]][$column['id']]) && count($this->elementTotals[$item[0]][$column['id']]['unit_total']))
                    {
                        foreach ($this->elementTotals[$item[0]][$column['id']]['unit_total'] as $key => $total) 
                        {
                            $currCol++;
                            parent::setPercentageValue( $currCol, (array_key_exists('unit_total_percentage', $total)) ? $total['unit_total_percentage'] / 100 : 0);
                        }
                    }
                }
            }
        }
    }
}