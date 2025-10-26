<?php
class sfBillReportGenerator extends sfBuildspaceExcelReportGenerator
{
    public $colEstimate = "D";
    public $colSelected = "E";
    public $colRationalized = "E";
    public $colDifferencePercent = "F";
    public $colDifferenceAmount = "G";

    public $selectedTenderer;
    public $tenderers;
    public $contractorBillGrandTotals;
    public $contractorProjectGrandtotal;
    public $selectedProjectGrandTotal;
    public $rationalizedProjectGrandtotal;
    public $selectedBillGrandTotals;
    public $rationalizedBillGrandTotals;
    public $estimateProjectGrandTotal;
    public $participate = false;
    public $multiple = false;

    function __construct( $project = null, $estimateProjectGrandTotal, $savePath = null, $filename = null, $printSettings )
    {
        $filename = ( $filename ) ? $filename : $this->bill->title.'-'.date( 'dmY H_i_s' );

        $savePath = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->estimateProjectGrandTotal = $estimateProjectGrandTotal;

        parent::__construct( $project, $savePath, $filename, $printSettings );
    }

    public function setSelectedComparison($selectedTenderer, $selectedProjectGrandTotal, $selectedBillGrandTotals)
    {
        $this->selectedTenderer = $selectedTenderer;

        $this->selectedProjectGrandTotal = $selectedProjectGrandTotal;

        $this->selectedBillGrandTotals   = $selectedBillGrandTotals;
    }

    public function setRationalizedComparison($rationalizedProjectGrandtotal, $rationalizedBillGrandTotals)
    {
        $this->participate = true;

        $this->rationalizedProjectGrandtotal = $rationalizedProjectGrandtotal;

        $this->rationalizedBillGrandTotals   = $rationalizedBillGrandTotals;
    }

    public function setMultipleContractorParameter($tenderers, $contractorProjectGrandtotal, $contractorBillGrandTotals)
    {
        $this->multiple = true;

        $this->tenderers = $tenderers;

        $this->contractorBillGrandTotals = $contractorBillGrandTotals;

        $this->contractorProjectGrandtotal = $contractorProjectGrandtotal;
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol   = $this->colItem;

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

    public function printGrandTotalValue($style)
    {
        $this->setValue( $this->colEstimate, $this->estimateProjectGrandTotal['value']);

        if($this->multiple)
        {
            $currCol = $this->colEstimate;

            if(count($this->tenderers))
            {
                foreach($this->tenderers as $tenderer)
                {
                    ++$currCol;

                    $grandTotal = ($this->contractorProjectGrandtotal && array_key_exists($tenderer['id'], $this->contractorProjectGrandtotal) && $this->contractorProjectGrandtotal[$tenderer['id']] != 0) ? $this->contractorProjectGrandtotal[$tenderer['id']] : 0;

                    $this->setValue( $currCol, $grandTotal);
                }
            }

            $this->activeSheet->getStyle( $this->colEstimate.$this->currentRow.":".$currCol.$this->currentRow )
                ->applyFromArray( $style );
        }
        else
        {
            if($this->participate)
            {
                $this->setValue( $this->colRationalized, (array_key_exists('value', $this->rationalizedProjectGrandtotal)) ? $this->rationalizedProjectGrandtotal['value'] : 0);

                $this->setAmountDifference($this->colDifferenceAmount, (array_key_exists('value', $this->rationalizedProjectGrandtotal)) ? $this->rationalizedProjectGrandtotal['value'] : 0, $this->estimateProjectGrandTotal['value']);

                $this->setPercentageDifference($this->colDifferencePercent, (array_key_exists('value', $this->rationalizedProjectGrandtotal)) ? $this->rationalizedProjectGrandtotal['value'] : 0, $this->estimateProjectGrandTotal['value']);
            }
            else
            {
                $this->setValue( $this->colSelected, (array_key_exists('value', $this->selectedProjectGrandTotal)) ? $this->selectedProjectGrandTotal['value'] : 0);

                $this->setAmountDifference($this->colDifferenceAmount, (array_key_exists('value', $this->selectedProjectGrandTotal)) ? $this->selectedProjectGrandTotal['value'] : 0, $this->estimateProjectGrandTotal['value']);

                $this->setPercentageDifference($this->colDifferencePercent, (array_key_exists('value', $this->selectedProjectGrandTotal)) ? $this->selectedProjectGrandTotal['value'] : 0, $this->estimateProjectGrandTotal['value']);
            }

            $this->activeSheet->getStyle( $this->colEstimate.$this->currentRow.":".$this->lastCol.$this->currentRow )
                ->applyFromArray( $style );
        }
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
                    if(array_key_exists($itemId, $this->contractorBillGrandTotals) && array_key_exists($tenderer['id'], $this->contractorBillGrandTotals[$itemId]))
                    {
                        $listOfRates[$tenderer['id']] = $this->contractorBillGrandTotals[$itemId][$tenderer['id']];
                    }
                }

                $lowestRate  = count($listOfRates) ? min($listOfRates) : 0;
                $highestRate = count($listOfRates) ? max($listOfRates) : 0;

                $lowestTendererId  = array_search($lowestRate, $listOfRates);
                $highestTendererId = array_search($highestRate, $listOfRates);

                foreach($this->tenderers as $tenderer)
                {
                    ++$currCol;

                    if(array_key_exists($item[0], $this->contractorBillGrandTotals) && array_key_exists($tenderer['id'], $this->contractorBillGrandTotals[$item[0]]))
                    {
                        if($lowestTendererId == $highestTendererId)
                        {
                            parent::setValue( $currCol, $this->contractorBillGrandTotals[$item[0]][$tenderer['id']]);
                        }
                        else
                        {
                            if($tenderer['id'] == $lowestTendererId)
                            {
                                parent::setLowestValue( $currCol, $this->contractorBillGrandTotals[$item[0]][$tenderer['id']]);
                            }
                            else if($tenderer['id'] == $highestTendererId)
                            {
                                parent::setHighestValue( $currCol, $this->contractorBillGrandTotals[$item[0]][$tenderer['id']]);
                            }
                            else
                            {
                                parent::setValue( $currCol, $this->contractorBillGrandTotals[$item[0]][$tenderer['id']]);
                            }
                        }
                    }
                }
            }
        }
        else
        {
            if($this->participate && array_key_exists($item[0], $this->rationalizedBillGrandTotals))
            {
                parent::setValue( $this->colRationalized, $this->rationalizedBillGrandTotals[$item[0]]);

                parent::setAmountDifference($this->colDifferenceAmount, $this->rationalizedBillGrandTotals[$item[0]], $item[6]);

                parent::setPercentageDifference($this->colDifferencePercent, $this->rationalizedBillGrandTotals[$item[0]], $item[6]);
            }
            else
            {
                if(array_key_exists($item[0], $this->selectedBillGrandTotals))
                {
                    parent::setValue( $this->colSelected, $this->selectedBillGrandTotals[$item[0]]);

                    parent::setAmountDifference($this->colDifferenceAmount, $this->selectedBillGrandTotals[$item[0]], $item[6]);

                    parent::setPercentageDifference($this->colDifferencePercent, $this->selectedBillGrandTotals[$item[0]], $item[6]);
                }
            }
        }
    }
}
