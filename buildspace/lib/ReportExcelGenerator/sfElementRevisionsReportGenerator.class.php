<?php

class sfElementRevisionsReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colEstimate = "D";

    public $tenderers;
    public $contractorElementTotals;
    public $contractorBillGrandTotal;
    public $estimateBillGrandTotal;
    private $projectRevisions;
    private $currentColumn;
    private $estimateElementGrandTotals;
    private $tendererElementGrandTotals;

    function __construct($project = null, $estimateBillGrandTotal, $savePath = null, $filename = null, $printSettings)
    {
        $filename = ( $filename ) ? $filename : $this->bill->title . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->estimateBillGrandTotal = $estimateBillGrandTotal;

        $this->projectRevisions = ProjectRevisionTable::getRevisions($project);

        parent::__construct($project, $savePath, $filename, $printSettings);
    }

    public function setParameters($tenderers, $contractorBillGrandTotal, $contractorElementTotals, $estimateElementGrandTotals, $tendererElementGrandTotals)
    {
        $this->tenderers = count($tenderers) ? $tenderers : array();

        $this->contractorElementTotals = $contractorElementTotals;

        $this->contractorBillGrandTotal = $contractorBillGrandTotal;

        $this->estimateElementGrandTotals = $estimateElementGrandTotals;

        $this->tendererElementGrandTotals = $tendererElementGrandTotals;
    }

    public function printGrandTotalValue($style)
    {
        $this->setValue($this->colEstimate, $this->estimateBillGrandTotal['value']);

        $this->currentColumn = $this->colEstimate;

        foreach($this->projectRevisions as $revision)
        {
            $this->currentColumn++;
        }

        $this->activeSheet->mergeCells($this->colEstimate . $this->currentRow . ':' . $this->currentColumn . $this->currentRow);

        foreach($this->tenderers as $tenderer)
        {
            $startColumn = ++$this->currentColumn;

            foreach($this->projectRevisions as $revision)
            {
                $this->currentColumn++;
            }

            $grandTotal = ( $this->contractorBillGrandTotal && array_key_exists($tenderer['id'], $this->contractorBillGrandTotal) && $this->contractorBillGrandTotal[ $tenderer['id'] ] != 0 ) ? $this->contractorBillGrandTotal[ $tenderer['id'] ] : 0;

            parent::setValue($startColumn, $grandTotal);

            $this->activeSheet->mergeCells($startColumn . $this->currentRow . ':' . $this->currentColumn . $this->currentRow);
        }

        $this->activeSheet->getStyle($this->colEstimate . $this->currentRow . ":" . $this->currentColumn . $this->currentRow)
            ->applyFromArray($style);
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol = $this->colItem;

        $this->currentColumn = $this->colEstimate;

        foreach($this->projectRevisions as $revision)
        {
            $this->currentColumn++;
        }

        foreach($this->tenderers as $tenderer)
        {
            $this->currentColumn++;

            foreach($this->projectRevisions as $revision)
            {
                $this->currentColumn++;
            }
        }

        $this->lastCol = $this->currentColumn;

        $this->currentElementNo = 0;
        $this->columnSetting = null;
    }

    public function createHeader($new = false)
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue($this->colItem . $row, self::COL_NAME_NO);
        $this->activeSheet->setCellValue($this->colDescription . $row, self::COL_NAME_DESCRIPTION);

        $this->mergeRows($this->colItem, $row);
        $this->mergeRows($this->colDescription, $row);

        $this->createEstimateHeaders();
        $this->createTendererHeaders();

        //Set header styling
        $this->activeSheet->getStyle($this->colItem . $row . ':' . $this->currentColumn . $this->getNextRow($row))->applyFromArray($this->getColumnHeaderStyle());
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        $this->currentRow++;

        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
        $this->activeSheet->getColumnDimension($this->colEstimate)->setWidth(15);
    }

    private function createEstimateHeaders()
    {
        $this->activeSheet->setCellValue($this->colEstimate . $this->currentRow, self::COL_NAME_ESTIMATE);

        $this->mergeColumns($this->colEstimate, $this->currentRow, count($this->projectRevisions));

        $this->currentColumn = $this->colEstimate;

        foreach($this->projectRevisions as $revision)
        {
            $this->activeSheet->setCellValue($this->currentColumn++ . $this->getNextRow($this->currentRow), $revision['revision'] . ' ' . self::COL_NAME_GRAND_TOTAL);
        }

        $this->activeSheet->setCellValue($this->currentColumn . $this->getNextRow($this->currentRow), self::COL_NAME_GRAND_TOTAL);
    }

    private function createTendererHeaders()
    {
        foreach($this->tenderers as $tenderer)
        {
            $startCol = ++$this->currentColumn;

            $tendererName = ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : $tenderer['name'];

            if( isset( $tenderer['selected'] ) AND $tenderer['selected'] )
            {
                // set the selected tenderer a blue marker
                $objRichText = new PHPExcel_RichText();
                $objBold = $objRichText->createTextRun(( ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : $tenderer['name'] ));
                $objBold->getFont()->setBold(true)->getColor()->setRGB('0000FF');

                $tendererName = $objRichText;
            }

            $this->activeSheet->setCellValue($this->currentColumn . $this->currentRow, $tendererName);
            $this->activeSheet->getColumnDimension($this->currentColumn)->setWidth(15);

            foreach($this->projectRevisions as $revision)
            {
                $this->activeSheet->setCellValue($this->currentColumn++ . $this->getNextRow($this->currentRow), $revision['revision'] . ' ' . self::COL_NAME_GRAND_TOTAL);
            }

            $this->activeSheet->setCellValue($this->currentColumn . $this->getNextRow($this->currentRow), self::COL_NAME_GRAND_TOTAL);

            $this->mergeColumns($startCol, $this->currentRow, count($this->projectRevisions));
        }
    }

    public function processItems($item)
    {
        self::processEstimateItems($item);

        self::processTendererItems($item);
    }

    private function processEstimateItems($item)
    {
        $this->currentColumn = $this->colEstimate;

        foreach($this->projectRevisions as $revisionNumber => $revision)
        {
            $revisionRate = $this->estimateElementGrandTotals[ $revisionNumber ]['elements'][ $item[ self::ROW_BILL_ITEM_ID ] ];
            parent::setValue($this->currentColumn++, $revisionRate);
        }

        parent::setValue($this->currentColumn, $item[ self::ROW_BILL_ITEM_RATE ]);
    }

    private function processTendererItems($item)
    {
        if( empty( $this->tenderers ) )
        {
            return;
        }

        $itemId = $item[ self::ROW_BILL_ITEM_ID ];
        $lowestTendererId = null;
        $highestTendererId = null;
        $listOfRates = array();

        foreach($this->tenderers as $k => $tenderer)
        {
            if( array_key_exists($itemId, $this->contractorElementTotals) && array_key_exists($tenderer['id'], $this->contractorElementTotals[ $itemId ]) )
            {
                array_push($listOfRates, $this->contractorElementTotals[ $itemId ][ $tenderer['id'] ]);
            }
        }

        $lowestRate = count($listOfRates) ? min($listOfRates) : 0;
        $highestRate = count($listOfRates) ? max($listOfRates) : 0;

        $lowestTendererId = $this->tenderers[ array_search($lowestRate, $listOfRates) ]['id'];
        $highestTendererId = $this->tenderers[ array_search($highestRate, $listOfRates) ]['id'];

        foreach($this->tenderers as $tenderer)
        {
            $this->currentColumn++;

            foreach($this->projectRevisions as $revisionNumber => $revision)
            {
                $tendererRate = $this->tendererElementGrandTotals[ $tenderer['id'] ][ $revisionNumber ]['elements'][ $item[ self::ROW_BILL_ITEM_ID ] ];

                parent::setValue($this->currentColumn++, $tendererRate);
            }
            $this->currentColumn--;

            $value = ( array_key_exists($item[ self::ROW_BILL_ITEM_ID ], $this->contractorElementTotals) && array_key_exists($tenderer['id'], $this->contractorElementTotals[ $item[ self::ROW_BILL_ITEM_ID ] ]) ) ? $this->contractorElementTotals[ $item[ self::ROW_BILL_ITEM_ID ] ][ $tenderer['id'] ] : 0;

            if( $lowestTendererId == $highestTendererId )
            {
                parent::setValue($this->currentColumn, $value);
            }
            else
            {
                if( $tenderer['id'] == $lowestTendererId )
                {
                    parent::setLowestValue($this->currentColumn, $value);
                }
                else if( $tenderer['id'] == $highestTendererId )
                {
                    parent::setHighestValue($this->currentColumn, $value);
                }
                else
                {
                    parent::setValue($this->currentColumn, $value);
                }
            }
        }
    }

}