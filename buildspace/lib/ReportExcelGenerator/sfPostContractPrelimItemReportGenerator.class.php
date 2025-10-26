<?php
class sfPostContractPrelimItemReportGenerator extends sfPostContractItemReportGenerator
{
    public $elementTotals;

    public $colContractAmount = "D";
    public $colInitialPercent="E";
    public $colInitialAmount="F";
    public $colRecurringPercent="G";
    public $colRecurringAmount="H";
    public $colFinalPercent="I";
    public $colFinalAmount="J";

    public function printGrandTotalValue($style)
    {
        parent::setValue( $this->colContractAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('grand_total',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['grand_total'] : 0);

        parent::setPercentageValue( $this->colInitialPercent, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('initial-percentage',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['initial-percentage'] / 100 : 0);
        parent::setValue( $this->colInitialAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('initial-amount',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['initial-amount'] : 0);

        parent::setPercentageValue( $this->colRecurringPercent, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('recurring-percentage',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['recurring-percentage'] / 100 : 0);
        parent::setValue( $this->colRecurringAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('recurring-amount',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['recurring-amount'] : 0);

        parent::setPercentageValue( $this->colFinalPercent, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('final-percentage',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['final-percentage'] / 100 : 0);
        parent::setValue( $this->colFinalAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('final-amount',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['final-amount'] : 0);

        $this->activeSheet->getStyle( $this->colContractAmount.$this->currentRow.":".$this->colFinalAmount.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function startBillCounter() 
    {
        $this->currentRow = $this->startRow;
        $this->firstCol = $this->colItem;
        $this->lastCol  = $this->colFinalAmount;

        $this->currentElementNo = 0;
        $this->columnSetting = null;
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
        $this->activeSheet->setCellValue( $this->colFinalPercent."3", $this->topRightTitle );
        $this->activeSheet->mergeCells( $this->colFinalPercent.'3:'.$this->colFinalAmount.'3' );
        $this->activeSheet->getStyle( $this->colFinalPercent.'3:'.$this->colFinalAmount.'3' )->applyFromArray( $this->getRightTitleStyle() );
    }

    public function createHeader( $new = false ) 
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );
        $this->activeSheet->setCellValue( $this->colContractAmount.$row, self::COL_NAME_CONTRACT_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colContractAmount )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colInitialPercent.$row, self::COL_NAME_INITIAL );
        $this->activeSheet->mergeCells( $this->colInitialPercent.$this->currentRow.':'.$this->colInitialAmount.$this->currentRow );

        $this->activeSheet->setCellValue( $this->colRecurringPercent.$row, self::COL_NAME_RECURRING );
        $this->activeSheet->mergeCells( $this->colRecurringPercent.$this->currentRow.':'.$this->colRecurringAmount.$this->currentRow );

        $this->activeSheet->setCellValue( $this->colFinalPercent.$row, self::COL_NAME_FINAL );
        $this->activeSheet->mergeCells( $this->colFinalPercent.$this->currentRow.':'.$this->colFinalAmount.$this->currentRow );

        $this->currentRow++;

        $this->activeSheet->setCellValue( $this->colInitialPercent.$this->currentRow, self::COL_NAME_PERCENT );
        $this->activeSheet->setCellValue( $this->colInitialAmount.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colInitialAmount )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colRecurringPercent.$this->currentRow, self::COL_NAME_PERCENT );
        $this->activeSheet->setCellValue( $this->colRecurringAmount.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colRecurringAmount )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colFinalPercent.$this->currentRow, self::COL_NAME_PERCENT );
        $this->activeSheet->setCellValue( $this->colFinalAmount.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colFinalAmount )->setWidth( 16 );

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$this->colFinalAmount.$this->currentRow )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
        
        $this->activeSheet->mergeCells( $this->colItem.$row.':'.$this->colItem.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colDescription.$row.':'.$this->colDescription.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colContractAmount.$row.':'.$this->colContractAmount.$this->currentRow );

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 12 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
    }

    public function processItems($item)
    {
        if ( $item[4] == BillItem::TYPE_ITEM_LUMP_SUM || $item[4] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE || $item[4] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT )
        {
            parent::setValue( $this->colContractAmount, $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_BILL_ITEM_RATE]);
        }
        else
        {
            parent::setValue( $this->colContractAmount, $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_BILL_ITEM_CONTRACT_AMOUNT]);
        }

        $initialPercentage = (array_key_exists('percentage', $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_INITIAL])) ? $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_INITIAL]['percentage'] : 0;
        $initialAmount = (array_key_exists('amount', $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_INITIAL])) ? $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_INITIAL]['amount'] : 0;

        $recurringPercentage = (array_key_exists('percentage', $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_RECURRING])) ? $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_RECURRING]['percentage'] : 0;
        $recurringAmount = (array_key_exists('amount', $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_RECURRING])) ? $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_RECURRING]['amount'] : 0;

        $finalPercentage = (array_key_exists('percentage', $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_FINAL])) ? $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_FINAL]['percentage'] : 0;
        $finalAmount = (array_key_exists('amount', $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_FINAL])) ? $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_FINAL]['amount'] : 0;

        parent::setPercentageValue( $this->colInitialPercent, $initialPercentage / 100);
        parent::setValue( $this->colInitialAmount, $initialAmount);

        parent::setPercentageValue( $this->colRecurringPercent, $recurringPercentage / 100);
        parent::setValue( $this->colRecurringAmount, $recurringAmount);

        parent::setPercentageValue( $this->colFinalPercent, $finalPercentage / 100);
        parent::setValue( $this->colFinalAmount, $finalAmount);
    }
}