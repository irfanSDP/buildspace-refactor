<?php
class sfElementReportGenerator extends sfBuildspaceExcelReportGenerator
{
    public $colEstimate = "D";
    public $colSelected = "E";
    public $colRationalized = "E";
    public $colDifferencePercent = "F";
    public $colDifferenceAmount = "G";

    public $selectedTenderer;
    public $tenderers;
    public $contractorElementTotals;
    public $contractorBillGrandTotal;
    public $selectedBillGrandTotal;
    public $rationalizedBillGrandtotal;
    public $selectedElementTotals;
    public $rationalizedElementTotals;
    public $estimateBillGrandTotal;
    public $participate = false;
    public $multiple = false;

    function __construct( $project = null, $estimateBillGrandTotal, $savePath = null, $filename = null, $printSettings )
    {
        $filename = ( $filename ) ? $filename : $this->bill->title.'-'.date( 'dmY H_i_s' );

        $savePath = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->estimateBillGrandTotal = $estimateBillGrandTotal;

        parent::__construct( $project, $savePath, $filename, $printSettings );
    }

    public function setSelectedComparison($selectedTenderer, $selectedBillGrandTotal, $selectedElementTotals)
    {
        $this->selectedTenderer = $selectedTenderer;

        $this->selectedBillGrandTotal = $selectedBillGrandTotal;

        $this->selectedElementTotals   = $selectedElementTotals;
    }

    public function setRationalizedComparison($rationalizedBillGrandtotal, $rationalizedElementTotals)
    {
        $this->participate = true;

        $this->rationalizedBillGrandtotal = $rationalizedBillGrandtotal;

        $this->rationalizedElementTotals   = $rationalizedElementTotals;
    }

    public function setMultipleContractorParameter($tenderers, $contractorBillGrandTotal, $contractorElementTotals)
    {
        $this->multiple = true;

        $this->tenderers = $tenderers;

        $this->contractorElementTotals = $contractorElementTotals;

        $this->contractorBillGrandTotal = $contractorBillGrandTotal;
    }

    public function printGrandTotalValue($style)
    {
        $this->setValue( $this->colEstimate, $this->estimateBillGrandTotal['value']);

        if($this->multiple)
        {
            $currCol = $this->colEstimate;

            if(count($this->tenderers))
            {
                foreach($this->tenderers as $tenderer)
                {
                    ++$currCol;

                    $grandTotal = ($this->contractorBillGrandTotal && array_key_exists($tenderer['id'], $this->contractorBillGrandTotal) && $this->contractorBillGrandTotal[$tenderer['id']] != 0) ? $this->contractorBillGrandTotal[$tenderer['id']] : 0;

                    parent::setValue( $currCol, $grandTotal);
                }
            }

            $this->activeSheet->getStyle( $this->colEstimate.$this->currentRow.":".$currCol.$this->currentRow )
                ->applyFromArray( $style );
        }
        else
        {
            if($this->participate)
            {
                $this->setValue( $this->colRationalized, $this->rationalizedBillGrandtotal['value']);

                $this->setAmountDifference($this->colDifferenceAmount, $this->rationalizedBillGrandtotal['value'], $this->estimateBillGrandTotal['value']);

                $this->setPercentageDifference($this->colDifferencePercent, $this->rationalizedBillGrandtotal['value'], $this->estimateBillGrandTotal['value']);
            }
            else
            {
                $this->setValue( $this->colSelected, $this->selectedBillGrandTotal['value']);

                $this->setAmountDifference($this->colDifferenceAmount, $this->selectedBillGrandTotal['value'], $this->estimateBillGrandTotal['value']);

                $this->setPercentageDifference($this->colDifferencePercent, $this->selectedBillGrandTotal['value'], $this->estimateBillGrandTotal['value']);
            }

            $this->activeSheet->getStyle( $this->colEstimate.$this->currentRow.":".$this->lastCol.$this->currentRow )
                ->applyFromArray( $style );
        }
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol = $this->colItem;

        if($this->multiple)
        {
            if(count($this->tenderers))
            {
                $currCol = $this->colEstimate;

                foreach($this->tenderers as $tenderer)
                {
                    ++$currCol;
                }

                $this->lastCol = $currCol;
            }
            else
            {
                $this->lastCol = $this->colEstimate;
            }
        }
        else
        {
            $this->lastCol = $this->colDifferenceAmount;
        }

        $this->currentElementNo = 0;
        $this->columnSetting = null;
    }

    public function createHeader( $new = false )
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );
        $this->activeSheet->setCellValue( $this->colEstimate.$row, self::COL_NAME_ESTIMATE );

        if($this->multiple)
        {
            $this->createMultipleHeader();
        }
        else
        {
            if($this->participate)
            {
                $this->createParticipateHeader();
            }
            else
            {
                $this->createSelectedHeader();
            }
        }

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
        $this->activeSheet->getColumnDimension( $this->colEstimate )->setWidth( 15 );
    }

    public function createMultipleHeader()
    {
        $row = $this->currentRow;
        $currCol = $this->colEstimate;

        if(count($this->tenderers))
        {
            foreach($this->tenderers as $tenderer)
            {
                ++$currCol;

                $tendererName = (strlen($tenderer['shortname'])) ? $tenderer['shortname'] : $tenderer['name'];

                if ( isset($tenderer['selected']) AND $tenderer['selected'] )
                {
                    // set the selected tenderer a blue marker
                    $objRichText = new PHPExcel_RichText();
                    $objBold = $objRichText->createTextRun( ((strlen($tenderer['shortname'])) ? $tenderer['shortname'] : $tenderer['name']) );
                    $objBold->getFont()->setBold(true)->getColor()->setRGB('0000FF');

                    $tendererName = $objRichText;
                }

                $this->activeSheet->setCellValue( $currCol.$row, $tendererName );
                $this->activeSheet->getColumnDimension( $currCol )->setWidth( 15 );
            }
        }

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$currCol.$row )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );

    }

    public function createParticipateHeader()
    {
        $row = $this->currentRow;

        $this->activeSheet->setCellValue( $this->colRationalized.$row, "Rationalized Rate" );
        $this->activeSheet->setCellValue( $this->colDifferencePercent.$row, self::COL_NAME_DIFF_PERCENT );
        $this->activeSheet->setCellValue( $this->colDifferenceAmount.$row, self::COL_NAME_DIFF_AMOUNT );

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$this->colDifferenceAmount.$row )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( $this->colRationalized )->setWidth( 15 );
        $this->activeSheet->getColumnDimension( $this->colDifferencePercent )->setWidth( 12 );
        $this->activeSheet->getColumnDimension( $this->colDifferenceAmount )->setWidth( 12 );
    }

    public function createSelectedHeader()
    {
        $row = $this->currentRow;

        // set the selected tenderer a blue marker
        $objRichText = new PHPExcel_RichText();
        $objBold = $objRichText->createTextRun(((strlen($this->selectedTenderer['shortname'])) ? $this->selectedTenderer['shortname'] : $this->selectedTenderer['name']));
        $objBold->getFont()->setBold(true)->getColor()->setRGB('0000FF');

        $this->activeSheet->setCellValue( $this->colSelected.$row, $objRichText );
        $this->activeSheet->setCellValue( $this->colDifferencePercent.$row, self::COL_NAME_DIFF_PERCENT );
        $this->activeSheet->setCellValue( $this->colDifferenceAmount.$row, self::COL_NAME_DIFF_AMOUNT );

        //Set header styling
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$this->colDifferenceAmount.$row )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( $this->colSelected )->setWidth( 15 );
        $this->activeSheet->getColumnDimension( $this->colDifferencePercent )->setWidth( 12 );
        $this->activeSheet->getColumnDimension( $this->colDifferenceAmount )->setWidth( 12 );
    }

    public function processItems($item)
    {
        parent::setValue( $this->colEstimate, $item[6]);

        if($this->multiple)
        {
            $currCol = $this->colEstimate;

            if(count($this->tenderers))
            {
                $itemId = $item[0];
                $lowestTendererId  = null;
                $highestTendererId = null;
                $listOfRates       = array();

                foreach($this->tenderers as $k => $tenderer)
                {
                    if(array_key_exists($itemId, $this->contractorElementTotals) && array_key_exists($tenderer['id'], $this->contractorElementTotals[$itemId]))
                    {
                        $listOfRates[$tenderer['id']] = $this->contractorElementTotals[$itemId][$tenderer['id']];
                    }
                }

                $lowestRate  = count($listOfRates) ? min($listOfRates) : 0;
                $highestRate = count($listOfRates) ? max($listOfRates) : 0;

                $lowestTendererId  = array_search($lowestRate, $listOfRates);
                $highestTendererId = array_search($highestRate, $listOfRates);

                foreach($this->tenderers as $tenderer)
                {
                    ++$currCol;

                    $value = (array_key_exists($item[0], $this->contractorElementTotals) && array_key_exists($tenderer['id'], $this->contractorElementTotals[$item[0]])) ? $this->contractorElementTotals[$item[0]][$tenderer['id']] : 0;

                    if($lowestTendererId == $highestTendererId)
                    {
                        parent::setValue( $currCol, $value);
                    }
                    else
                    {
                        if($tenderer['id'] == $lowestTendererId)
                        {
                            parent::setLowestValue( $currCol, $value);
                        }
                        else if($tenderer['id'] == $highestTendererId)
                        {
                            parent::setHighestValue( $currCol, $value);
                        }
                        else
                        {
                            parent::setValue( $currCol, $value);
                        }
                    }
                }
            }
        }
        else
        {
            if($this->participate)
            {
                parent::setValue( $this->colRationalized, (array_key_exists($item[0], $this->rationalizedElementTotals)) ? $this->rationalizedElementTotals[$item[0]] : 0);

                parent::setAmountDifference($this->colDifferenceAmount, (array_key_exists($item[0], $this->rationalizedElementTotals)) ? $this->rationalizedElementTotals[$item[0]] : 0, $item[6]);

                parent::setPercentageDifference($this->colDifferencePercent, (array_key_exists($item[0], $this->rationalizedElementTotals)) ? $this->rationalizedElementTotals[$item[0]] : 0, $item[6]);
            }
            else
            {
                parent::setValue( $this->colSelected, (array_key_exists($item[0], $this->selectedElementTotals)) ? $this->selectedElementTotals[$item[0]] : 0);

                parent::setAmountDifference($this->colDifferenceAmount, (array_key_exists($item[0], $this->selectedElementTotals)) ? $this->selectedElementTotals[$item[0]] : 0, $item[6]);

                parent::setPercentageDifference($this->colDifferencePercent, (array_key_exists($item[0], $this->selectedElementTotals)) ? $this->selectedElementTotals[$item[0]] : 0, $item[6]);
            }
        }
    }
}