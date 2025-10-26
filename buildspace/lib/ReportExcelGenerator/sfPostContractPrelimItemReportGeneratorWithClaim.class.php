<?php
class sfPostContractPrelimItemReportGeneratorWithClaim extends sfPostContractPrelimItemReportGenerator
{
    public $elementTotals;

    public $colContractAmount = "D";
    public $colInitial="E";
    public $colRecurring="F";
    public $colFinal="G";
    public $colTotalPercent="H";
    public $colTotalAmount="I";

    public function printGrandTotalValue($style)
    {
        parent::setValue( $this->colContractAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('grand_total',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['grand_total'] : 0);
        
        parent::setValue( $this->colInitial, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('initial-amount',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['initial-amount'] : 0);
        parent::setValue( $this->colRecurring, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('recurring-amount',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['recurring-amount'] : 0);
        parent::setValue( $this->colFinal, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('final-amount',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['final-amount'] : 0);

        parent::setPercentageValue( $this->colTotalPercent, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('upToDateClaim-percentage',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['upToDateClaim-percentage'] / 100 : 0);
        parent::setValue( $this->colTotalAmount, (array_key_exists($this->currentElementId, $this->elementTotals) && array_key_exists('upToDateClaim-amount',$this->elementTotals[$this->currentElementId])) ? $this->elementTotals[$this->currentElementId]['upToDateClaim-amount'] : 0);

        $this->activeSheet->getStyle( $this->colContractAmount.$this->currentRow.":".$this->colTotalAmount.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function startBillCounter() 
    {
        $this->currentRow = $this->startRow;
        $this->firstCol = $this->colItem;
        $this->lastCol  = $this->colTotalAmount;

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
        $this->activeSheet->setCellValue( $this->colTotalPercent."3", $this->topRightTitle );
        $this->activeSheet->mergeCells( $this->colTotalPercent.'3:'.$this->colTotalAmount.'3' );
        $this->activeSheet->getStyle( $this->colTotalPercent.'3:'.$this->colTotalAmount.'3' )->applyFromArray( $this->getRightTitleStyle() );
    }

    public function createHeader( $new = false ) 
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );
        $this->activeSheet->setCellValue( $this->colContractAmount.$row, self::COL_NAME_CONTRACT_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colContractAmount )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colInitial.$row, self::COL_NAME_INITIAL.' '.self::COL_NAME_PAYMENT );
        $this->activeSheet->getColumnDimension( $this->colInitial )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colRecurring.$row, self::COL_NAME_RECURRING.' '.self::COL_NAME_PAYMENT );
        $this->activeSheet->getColumnDimension( $this->colRecurring )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colFinal.$row, self::COL_NAME_FINAL.' '.self::COL_NAME_PAYMENT );
        $this->activeSheet->getColumnDimension( $this->colFinal )->setWidth( 16 );

        $this->activeSheet->setCellValue( $this->colTotalPercent.$row, self::COL_NAME_TOTAL.' '.self::COL_NAME_PAYMENT );
        $this->activeSheet->mergeCells( $this->colTotalPercent.$this->currentRow.':'.$this->colTotalAmount.$this->currentRow );

        $this->currentRow++;

        $this->activeSheet->setCellValue( $this->colTotalPercent.$this->currentRow, self::COL_NAME_PERCENT );
        $this->activeSheet->setCellValue( $this->colTotalAmount.$this->currentRow, self::COL_NAME_AMOUNT );
        $this->activeSheet->getColumnDimension( $this->colTotalAmount )->setWidth( 16 );

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$this->colTotalAmount.$this->currentRow )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
        
        $this->activeSheet->mergeCells( $this->colItem.$row.':'.$this->colItem.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colDescription.$row.':'.$this->colDescription.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colContractAmount.$row.':'.$this->colContractAmount.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colInitial.$row.':'.$this->colInitial.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colRecurring.$row.':'.$this->colRecurring.$this->currentRow );
        $this->activeSheet->mergeCells( $this->colFinal.$row.':'.$this->colFinal.$this->currentRow );

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

        $initialAmount = (array_key_exists('amount', $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_INITIAL])) ? $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_INITIAL]['amount'] : 0;
        $recurringAmount = (array_key_exists('amount', $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_RECURRING])) ? $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_RECURRING]['amount'] : 0;
        $finalAmount = (array_key_exists('amount', $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_FINAL])) ? $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_FINAL]['amount'] : 0;

        $totalPercent = (array_key_exists('percentage', $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_TOTAL])) ? $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_TOTAL]['percentage'] : 0;
        $totalAmount = (array_key_exists('amount', $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_TOTAL])) ? $item[sfBuildspacePostContractPrelimReportPageItemGenerator::ROW_CLAIM_TOTAL]['amount'] : 0;

        parent::setValue( $this->colInitial, $initialAmount);
        parent::setValue( $this->colRecurring, $recurringAmount);
        parent::setValue( $this->colFinal, $finalAmount);

        parent::setPercentageValue( $this->colTotalPercent, $totalPercent / 100);
        parent::setValue( $this->colTotalAmount, $totalAmount);
    }
}