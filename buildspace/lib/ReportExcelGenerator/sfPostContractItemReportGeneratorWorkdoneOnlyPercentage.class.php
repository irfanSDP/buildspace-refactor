<?php
class sfPostContractItemReportGeneratorWorkdoneOnlyPercentage extends sfPostContractItemReportGenerator
{
    public $elementTotals;

    public $colQty = "D";
    public $colUnit = "E";
    public $colRate = "F";
    public $colContractAmount = "G";
    public $colWorkDonePercent="H";
    public $colWorkDoneAmount="I";

    public function printGrandTotalValue($style)
    {
        parent::setValue( $this->colContractAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('total_per_unit',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['total_per_unit'] : 0);

        parent::setPercentageValue( $this->colWorkDonePercent, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('up_to_date_percentage',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['up_to_date_percentage'] / 100 : 0);
        parent::setValue( $this->colWorkDoneAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('up_to_date_amount',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['up_to_date_amount'] : 0);

        $this->activeSheet->getStyle( $this->colContractAmount.$this->currentRow.":".$this->colWorkDoneAmount.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function printTotalText($title = false)
    {
        $this->activeSheet->setCellValue( $this->colRate.$this->currentRow, "Total:" );
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
        $this->activeSheet->setCellValue( $this->colWorkDonePercent."3", $this->topRightTitle );
        $this->activeSheet->mergeCells( $this->colWorkDonePercent.'3:'.$this->colWorkDoneAmount.'3' );
        $this->activeSheet->getStyle( $this->colWorkDonePercent.'3:'.$this->colWorkDoneAmount.'3' )->applyFromArray( $this->getRightTitleStyle() );
    }

    public function startBillCounter() 
    {
        $this->currentRow = $this->startRow;
        $this->firstCol = $this->colItem;
        $this->lastCol  = $this->colWorkDoneAmount;

        $this->currentElementNo = 0;
        $this->columnSetting = null;
    }

    public function createHeader( $new = false ) 
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );

        $this->activeSheet->setCellValue( $this->colQty.$row, self::COL_NAME_QTY );
        $this->activeSheet->getColumnDimension( $this->colQty )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colUnit.$row, self::COL_NAME_UNIT );
        $this->activeSheet->getColumnDimension( $this->colUnit )->setWidth( 10 );

        $this->activeSheet->setCellValue( $this->colRate.$row, self::COL_NAME_RATE );
        $this->activeSheet->getColumnDimension( $this->colRate )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colContractAmount.$row, self::COL_NAME_CONTRACT_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colContractAmount )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colWorkDonePercent.$row, self::COL_NAME_WORKDONE );
        $this->activeSheet->mergeCells( $this->colWorkDonePercent.$this->currentRow.':'.$this->colWorkDoneAmount.$this->currentRow );

        $this->currentRow++;

        $this->activeSheet->setCellValue( $this->colWorkDonePercent.$this->currentRow, self::COL_NAME_PERCENT );
        $this->activeSheet->setCellValue( $this->colWorkDoneAmount.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colWorkDoneAmount )->setWidth( 16 );

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$this->colWorkDoneAmount.$this->currentRow )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
        
        $this->activeSheet->mergeCells( $this->colItem.$row.':'.$this->colItem.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colDescription.$row.':'.$this->colDescription.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colContractAmount.$row.':'.$this->colContractAmount.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colQty.$row.':'.$this->colQty.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colRate.$row.':'.$this->colRate.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colUnit.$row.':'.$this->colUnit.$this->currentRow );

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
    }

    public function processItems($item)
    {
        $rate = $item ? $item[6] : 0;
        $unit = $item ? $item[5] : '';
        $qty  = $item ? $item[7] : 0;

        parent::setNormalQtyValue($this->colQty, $qty);

        parent::setValue($this->colRate, $rate);

        parent::setValue($this->colUnit, $unit);

        parent::setValue( $this->colContractAmount, $item[sfBuildspacePostContractReportPageElementGenerator::ROW_BILL_ITEM_CONTRACT_AMOUNT]);

        $workDonePercent = (array_key_exists('up_to_date_percentage', $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE])) ? $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE]['up_to_date_percentage'] : 0;
        $workDoneAmount = (array_key_exists('up_to_date_amount', $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE])) ? $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE]['up_to_date_amount'] : 0;

        parent::setPercentageValue( $this->colWorkDonePercent, $workDonePercent / 100);
        parent::setValue( $this->colWorkDoneAmount, $workDoneAmount);
    }
}