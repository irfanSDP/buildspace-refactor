<?php
class sfItemComparisonReportGenerator extends sfItemReportGenerator {

    public $colEstimate = "E";
    public $colSelected = "F";
    public $colRationalized = "F";
    public $colDifferencePercent = "G";
    public $colDifferenceAmount = "H";

    public $billColumnSettings;
    public $rationalizedRates;
    public $selectedRates;
    public $selectedElementTotals;
    public $rationalizedElementTotals;
    public $participate;
    public $selectedTenderer;

    public function setSelectedParameter($billColumnSettings, $selectedTenderer, $selectedRates, $selectedElementTotals)
    {
        $this->selectedTenderer = $selectedTenderer;

        $this->billColumnSettings = $billColumnSettings;

        $this->selectedRates = $selectedRates;

        $this->selectedElementTotals = $selectedElementTotals;
    }

    public function calculateColumn()
    {
        if ( $this->printQty )
        {
            $this->colQty               = "E";
            $this->colEstimate          = "F";
            $this->colSelected          = "G";
            $this->colRationalized      = "G";
            $this->colDifferencePercent = "H";
            $this->colDifferenceAmount  = "I";
        }
    }

    public function setRationalizedParameter($billColumnSettings, $rationalizedRates, $rationalizedElementTotals)
    {
        $this->participate = true;

        $this->rationalizedRates = $rationalizedRates;

        $this->billColumnSettings = $billColumnSettings;

        $this->rationalizedElementTotals = $rationalizedElementTotals;
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colItem;
        $this->lastCol    = $this->colDifferenceAmount;

        $this->currentElementNo = 0;
        $this->columnSetting    = null;
    }

    public function printGrandTotalValue($style)
    {
        $this->setValue($this->colEstimate, $this->estimateElementTotals[$this->currentElementId]);

        if ( $this->participate )
        {
            $this->setValue($this->colRationalized, $this->rationalizedElementTotals[$this->currentElementId]);

            $this->setAmountDifference($this->colDifferenceAmount, $this->rationalizedElementTotals[$this->currentElementId], $this->estimateElementTotals[$this->currentElementId]);

            $this->setPercentageDifference($this->colDifferencePercent, $this->rationalizedElementTotals[$this->currentElementId], $this->estimateElementTotals[$this->currentElementId]);
        }
        else
        {
            $value = isset( $this->selectedElementTotals[$this->currentElementId] ) ? $this->selectedElementTotals[$this->currentElementId] : null;

            $this->setValue($this->colSelected, $value);

            $this->setAmountDifference($this->colDifferenceAmount, $value, $this->estimateElementTotals[$this->currentElementId]);

            $this->setPercentageDifference($this->colDifferencePercent, $value, $this->estimateElementTotals[$this->currentElementId]);
        }

        $this->activeSheet->getStyle($this->colEstimate . $this->currentRow . ":" . $this->lastCol . $this->currentRow)
            ->applyFromArray($style);
    }

    public function createHeader($new = false)
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue($this->colItem . $row, self::COL_NAME_NO);
        $this->activeSheet->setCellValue($this->colDescription . $row, self::COL_NAME_DESCRIPTION);
        $this->activeSheet->setCellValue($this->colUnit . $row, self::COL_NAME_UNIT);
        $this->activeSheet->setCellValue($this->colEstimate . $row, self::COL_NAME_ESTIMATE);

        if ( $this->printQty )
        {
            $this->activeSheet->setCellValue($this->colQty . $row, self::COL_NAME_QTY);
            $this->activeSheet->getColumnDimension($this->colQty)->setWidth(13);
        }

        if ( $this->participate )
        {
            $this->createParticipateHeader();
        }
        else
        {
            $this->createSelectedHeader();
        }

        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colItem)->setWidth(9);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
        $this->activeSheet->getColumnDimension($this->colUnit)->setWidth(13);
        $this->activeSheet->getColumnDimension($this->colEstimate)->setWidth(15);
    }

    public function createParticipateHeader()
    {
        $row = $this->currentRow;

        $this->activeSheet->setCellValue($this->colRationalized . $row, "Rationalized Rate");
        $this->activeSheet->setCellValue($this->colDifferencePercent . $row, self::COL_NAME_DIFF_PERCENT);
        $this->activeSheet->setCellValue($this->colDifferenceAmount . $row, self::COL_NAME_DIFF_AMOUNT);

        //Set header styling
        $this->activeSheet->getStyle($this->colItem . $row . ':' . $this->colDifferenceAmount . $row)->applyFromArray($this->getColumnHeaderStyle());
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        //Set Column Sizing
        $this->activeSheet->getColumnDimension($this->colRationalized)->setWidth(15);
        $this->activeSheet->getColumnDimension($this->colDifferencePercent)->setWidth(12);
        $this->activeSheet->getColumnDimension($this->colDifferenceAmount)->setWidth(12);
    }

    public function createSelectedHeader()
    {
        $row = $this->currentRow;

        // set the selected tenderer a blue marker
        $objRichText = new PHPExcel_RichText();
        $objBold     = $objRichText->createTextRun(( ( strlen($this->selectedTenderer['shortname']) ) ? $this->selectedTenderer['shortname'] : $this->selectedTenderer['name'] ));
        $objBold->getFont()->setBold(true)->getColor()->setRGB('0000FF');

        $this->activeSheet->setCellValue($this->colSelected . $row, $objRichText);
        $this->activeSheet->setCellValue($this->colDifferencePercent . $row, self::COL_NAME_DIFF_PERCENT);
        $this->activeSheet->setCellValue($this->colDifferenceAmount . $row, self::COL_NAME_DIFF_AMOUNT);

        //Set header styling
        $this->activeSheet->getStyle($this->colItem . $row . ':' . $this->colDifferenceAmount . $row)->applyFromArray($this->getColumnHeaderStyle());
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        //Set Column Sizing
        $this->activeSheet->getColumnDimension($this->colSelected)->setWidth(15);
        $this->activeSheet->getColumnDimension($this->colDifferencePercent)->setWidth(12);
        $this->activeSheet->getColumnDimension($this->colDifferenceAmount)->setWidth(12);
    }

    public function processItems($item)
    {
        parent::setUnit($item[ self::ROW_BILL_ITEM_UNIT ]);

        if ( $this->printQty )
        {
            if ( $item && is_array($item[7]) )
            {
                $quantity = 0;

                foreach ( $this->billColumnSettings as $column )
                {
                    $qtyField = array_key_exists($column['id'], $item[7]) ? $item[7][$column['id']] : 0;
                    $quantity += ( $qtyField * $column['quantity'] );
                }
            }
            else
            {
                $quantity = 0;
            }

            parent::setValue($this->colQty, $quantity);
        }

        if ( $item[4] == BillItem::TYPE_ITEM_NOT_LISTED )
        {
            parent::setValue($this->colEstimate, $item[6][0]);
        }
        else
        {
            parent::setValue($this->colEstimate, $item[6]);
        }

        if ( $this->participate )
        {
            if ( $item[4] == BillItem::TYPE_ITEM_NOT_LISTED )
            {
                parent::setValue($this->colRationalized, $item[6][1]);
            }
            else
            {
                parent::setValue($this->colRationalized, ( array_key_exists($item[0], $this->rationalizedRates) ) ? $this->rationalizedRates[$item[0]] : 0);

                parent::setAmountDifference($this->colDifferenceAmount, ( array_key_exists($item[0], $this->rationalizedRates) ) ? $this->rationalizedRates[$item[0]] : 0, $item[6]);

                parent::setPercentageDifference($this->colDifferencePercent, ( array_key_exists($item[0], $this->rationalizedRates) ) ? $this->rationalizedRates[$item[0]] : 0, $item[6]);
            }
        }
        else
        {
            if ( $item[4] == BillItem::TYPE_ITEM_NOT_LISTED )
            {
                parent::setValue($this->colSelected, $item[6][1]);
            }
            else
            {
                parent::setValue($this->colSelected, ( array_key_exists($item[0], $this->selectedRates) ) ? $this->selectedRates[$item[0]] : 0);

                parent::setAmountDifference($this->colDifferenceAmount, ( array_key_exists($item[0], $this->selectedRates) ) ? $this->selectedRates[$item[0]] : 0, $item[6]);

                parent::setPercentageDifference($this->colDifferencePercent, ( array_key_exists($item[0], $this->selectedRates) ) ? $this->selectedRates[$item[0]] : 0, $item[6]);
            }
        }
    }
}