<?php
class sfPostContractStandardElementReportWorkdoneOnly extends sfPostContractStandardElementReport
{
    public $colContractAmount = "D";
    public $colWorkDonePercent="E";
    public $colWorkDoneAmount="F";

    public $topRightTitle;

    public function printGrandTotalValue($style)
    {
        $currCol  = $this->colContractAmount;

        parent::setValue( $this->colContractAmount, (array_key_exists('total_per_unit',$this->typeTotals)) ? $this->typeTotals['total_per_unit'] : 0);

        parent::setPercentageValue( $this->colWorkDonePercent, (array_key_exists('up_to_date_percentage',$this->typeTotals)) ? $this->typeTotals['up_to_date_percentage'] / 100 : 0);
        parent::setValue( $this->colWorkDoneAmount, (array_key_exists('up_to_date_amount',$this->typeTotals)) ? $this->typeTotals['up_to_date_amount'] : 0);

        $this->activeSheet->getStyle( $this->colContractAmount.$this->currentRow.":".$this->colWorkDoneAmount.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function startBillCounter() 
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colItem;
        $this->lastCol    = $this->colWorkDoneAmount;

        $this->currentElementNo = 0;
        $this->columnSetting = null;
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
        $this->activeSheet->setCellValue( $this->colWorkDonePercent."3", $this->topRightTitle );
        $this->activeSheet->mergeCells( $this->colWorkDonePercent.'3:'.$this->colWorkDoneAmount.'3' );
        $this->activeSheet->getStyle( $this->colWorkDonePercent.'3:'.$this->colWorkDoneAmount.'3' )->applyFromArray( $this->getRightTitleStyle() );
    }

    public function createHeader( $new = false ) 
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );
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

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
    }

    public function processItems($item)
    {
        parent::setValue( $this->colContractAmount, $item[sfBuildspacePostContractReportPageElementGenerator::ROW_BILL_ITEM_CONTRACT_AMOUNT]);

        $workDonePercentage = (array_key_exists('up_to_date_percentage', $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE])) ? $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE]['up_to_date_percentage'] : 0;
        $workDoneAmount = (array_key_exists('up_to_date_amount', $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE])) ? $item[sfBuildspacePostContractReportPageElementGenerator::ROW_CLAIM_WORKDONE]['up_to_date_amount'] : 0;

        parent::setPercentageValue( $this->colWorkDonePercent, $workDonePercentage / 100);
        parent::setValue( $this->colWorkDoneAmount, $workDoneAmount);
    }
}