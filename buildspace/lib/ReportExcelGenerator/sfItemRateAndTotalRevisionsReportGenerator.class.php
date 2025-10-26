<?php

use sfBuildspaceReportItemRateAndTotalRevisionsPageGenerator as PageGenerator;

class sfItemRateAndTotalRevisionsReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colItem = "B";
    public $colDescription = "C";
    public $colUnit = "D";
    public $colQty = "E";
    public $colEstimate = "F";

    public $printQty;
    public $billColumnSettings;
    public $tenderers;
    public $contractorRates;
    public $contractorTotals;
    public $contractorElementTotals;
    public $estimateElementTotals;
    public $currentElementId;
    private $estimateRateRevisions;
    private $estimateTotalRevisions;
    private $tendererRateRevisions;
    private $tendererTotalRevisions;
    private $projectRevisions;
    private $itemRateLog;

    public $currentNotListedId;
    public $currentNotListedCount;

    function __construct($project = null, $estimateElementTotals, $savePath = null, $filename = null, $printQty = false, $printSettings)
    {
        $filename = ( $filename ) ? $filename : $this->bill->title . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->estimateElementTotals = $estimateElementTotals;

        // Always print quantity
        $this->printQty = true;

        $this->projectRevisions = ProjectRevisionTable::getRevisions($project, true);

        $this->itemRateLog = TenderBillItemRateLogTable::getBillItemRateLogs($project);

        parent::__construct($project, $savePath, $filename, $printSettings);
    }

    /**
     * Sets the parameters for the class.
     *
     * @param $billColumnSettings
     * @param $tenderers
     * @param $contractorRates
     * @param $contractorTotals
     * @param $contractorElementTotals
     * @param $estimateRateRevisions
     * @param $estimateTotalRevisions
     * @param $tendererRateRevisions
     * @param $tendererTotalRevisions
     */
    public function setParameter($billColumnSettings, $tenderers, $contractorRates, $contractorTotals, $contractorElementTotals, $estimateRateRevisions, $estimateTotalRevisions, $tendererRateRevisions, $tendererTotalRevisions)
    {
        $this->billColumnSettings = $billColumnSettings;

        $this->tenderers = $tenderers;

        $this->contractorRates = $contractorRates;

        $this->contractorTotals = $contractorTotals;

        $this->contractorElementTotals = $contractorElementTotals;

        $this->estimateRateRevisions = $estimateRateRevisions;

        $this->estimateTotalRevisions = $estimateTotalRevisions;

        $this->tendererRateRevisions = $tendererRateRevisions;

        $this->tendererTotalRevisions = $tendererTotalRevisions;
    }

    /**
     * Prints the text 'Total' in the desired cell.
     *
     * @param bool $title
     */
    public function printTotalText($title = false)
    {
        if( $this->printQty )
        {
            $this->activeSheet->setCellValue($this->colQty . $this->currentRow, "Total:");
        }
        else
        {
            $this->activeSheet->setCellValue($this->colDescription . $this->currentRow, "Total:");
        }
    }

    /**
     * Sets the value for the grand total.
     *
     * @param $style
     */
    public function printGrandTotalValue($style)
    {
        $this->setValue($this->colEstimate, $this->estimateElementTotals[ $this->currentElementId ]);
        $currentColumn = $this->colEstimate;
        foreach($this->projectRevisions as $revisions)
        {
            $currentColumn++;
            $currentColumn++;
        }

        $currentColumn = $this->getPreviousColumn($currentColumn);

        $this->activeSheet->mergeCells($this->colEstimate . $this->currentRow . ':' . $currentColumn . $this->currentRow);

        if( count($this->tenderers) )
        {
            foreach($this->tenderers as $tenderer)
            {
                $tendererColumnsStart = $currentColumn;
                $tendererColumnsStart++;

                foreach($this->projectRevisions as $revisions)
                {
                    ++$currentColumn;
                    ++$currentColumn;
                }

                $currentColumn = $this->getPreviousColumn($currentColumn);

                $grandTotal = ( $this->contractorElementTotals && array_key_exists($this->currentElementId, $this->contractorElementTotals) && array_key_exists($tenderer['id'], $this->contractorElementTotals[ $this->currentElementId ]) && $this->contractorElementTotals[ $this->currentElementId ][ $tenderer['id'] ] != 0 ) ? $this->contractorElementTotals[ $this->currentElementId ][ $tenderer['id'] ] : 0;

                $this->setValue($tendererColumnsStart, $grandTotal);
                $this->activeSheet->mergeCells($tendererColumnsStart . $this->currentRow . ':' . $this->getNextColumn($currentColumn++) . $this->currentRow);
            }
        }

        $this->activeSheet->getStyle($this->colEstimate . $this->currentRow . ":" . $currentColumn . $this->currentRow)
            ->applyFromArray($style);
    }

    /**
     * Starts the bill counter.
     * This sets the first row, currentElementNo,  and currentRow to the starting.
     * Also determines the first and last column.
     */
    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol = $this->colItem;
        $currentColumn = $this->colEstimate;

        // Estimate Revisions.
        foreach($this->projectRevisions as $revisions)
        {
            ++$currentColumn;
            ++$currentColumn;
        }

        $currentColumn = $this->getPreviousColumn($currentColumn);

        if( count($this->tenderers) )
        {
            foreach($this->tenderers as $tenderer)
            {
                foreach($this->projectRevisions as $revisions)
                {
                    ++$currentColumn;
                    ++$currentColumn;
                }
            }
        }

        $this->lastCol = $currentColumn;

        $this->currentElementNo = 0;
        $this->columnSetting = null;
    }

    /**
     * Creates the header.
     *
     * @param bool $new
     */
    public function createHeader($new = false)
    {
        $this->currentRow++;
        $row = $this->currentRow;

        // Account for merged header rows
        $this->currentRow++;

        // Set default column
        // First header row.
        $this->activeSheet->setCellValue($this->colItem . $row, self::COL_NAME_NO);
        $this->mergeRows($this->colItem, $row);

        $this->activeSheet->setCellValue($this->colDescription . $row, self::COL_NAME_DESCRIPTION);
        $this->mergeRows($this->colDescription, $row);

        $this->activeSheet->setCellValue($this->colUnit . $row, self::COL_NAME_UNIT);
        $this->mergeRows($this->colUnit, $row);

        $this->activeSheet->setCellValue($this->colEstimate . $row, self::COL_NAME_ESTIMATE);

        $endOfEstimateColumns = $this->colEstimate;
        foreach($this->projectRevisions as $revision)
        {
            $endOfEstimateColumns++;
            $endOfEstimateColumns++;
        }

        // 'Total' Column.
        $endOfEstimateColumns = $this->getPreviousColumn($endOfEstimateColumns);

        $this->activeSheet->mergeCells($this->colEstimate . $row . ':' . $endOfEstimateColumns . $row);

        // Second header row.
        // Revisions of estimates.
        $currentColumn = $this->colEstimate;
        foreach($this->projectRevisions as $revision)
        {
            $headerValue = $revision['revision'] . ' ' . self::COL_NAME_RATE;
            $this->activeSheet->setCellValue($currentColumn++ . $this->getNextRow($row), $headerValue);

            $headerValue = $revision['revision'] . ' ' . self::COL_NAME_TOTAL;
            $this->activeSheet->setCellValue($currentColumn++ . $this->getNextRow($row), $headerValue);
        }

        $currentColumn = $this->getPreviousColumn($currentColumn);

        if( $this->printQty )
        {
            $this->activeSheet->setCellValue($this->colQty . $row, self::COL_NAME_QTY);
            $this->activeSheet->getColumnDimension($this->colQty)->setWidth(13);
            $this->mergeRows($this->colQty, $row);
        }

        if( count($this->tenderers) )
        {
            foreach($this->tenderers as $tenderer)
            {
                ++$currentColumn;

                $tendererName = ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : $tenderer['name'];

                if( isset( $tenderer['selected'] ) AND $tenderer['selected'] )
                {
                    // set the selected tenderer a blue marker
                    $objRichText = new PHPExcel_RichText();
                    $objBold = $objRichText->createTextRun(( ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : $tenderer['name'] ));
                    $objBold->getFont()->setBold(true)->getColor()->setRGB('0000FF');

                    $tendererName = $objRichText;
                }

                // column for rate
                $this->activeSheet->setCellValue($currentColumn . $row, $tendererName);

                $tendererTotalColumn = $currentColumn;

                foreach($this->projectRevisions as $revision)
                {
                    $tendererTotalColumn++;
                    $tendererTotalColumn++;
                }

                $tendererTotalColumn = $this->getPreviousColumn($tendererTotalColumn);

                // merge with column for total
                $this->activeSheet->mergeCells($currentColumn . $row . ':' . $tendererTotalColumn . $row);

                // Second header row.
                // Revisions of estimates.
                foreach($this->projectRevisions as $revision)
                {
                    $headerValue = $revision['revision'] . ' ' . self::COL_NAME_RATE;
                    $this->activeSheet->setCellValue($currentColumn . $this->getNextRow($row), $headerValue);
                    $this->activeSheet->getColumnDimension($currentColumn)->setWidth(15);
                    $currentColumn++;

                    $headerValue = $revision['revision'] . ' ' . self::COL_NAME_TOTAL;
                    $this->activeSheet->setCellValue($currentColumn . $this->getNextRow($row), $headerValue);
                    $this->activeSheet->getColumnDimension($currentColumn)->setWidth(15);
                    $currentColumn++;
                }

                $currentColumn = $this->getPreviousColumn($currentColumn);
            }
        }

        //Set header styling
        $this->activeSheet->getStyle($this->colItem . $row . ':' . $currentColumn . $row)->applyFromArray($this->getColumnHeaderStyle());
        // For merged header rows
        $this->activeSheet->getStyle($this->colItem . $this->getNextRow($row) . ':' . $currentColumn . $this->getNextRow($row))->applyFromArray($this->getColumnHeaderStyle());

        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        //Set Column Sizing
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colItem)->setWidth(9);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
        $this->activeSheet->getColumnDimension($this->colUnit)->setWidth(13);

        $this->activeSheet->getColumnDimension($currentColumn)->setWidth(15);
        $this->activeSheet->getColumnDimension($this->getNextColumn($currentColumn))->setWidth(15);
    }

    /**
     * Creates the sheet, processes the items and creates the footer for all items' pages.
     *
     * @param      $pages
     * @param bool $lock
     * @param      $header
     * @param      $subTitle
     * @param      $topLeftTitle
     * @param      $withoutCents
     * @param      $totalPage
     */
    public function process($pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
    {
        $this->setExcelParameter($lock, $withoutCents);

        $this->totalPage = $totalPage;

        $description = '';
        $char = '';
        $prevItemType = '';
        $prevItemLevel = 0;

        $this->createSheet($header, $subTitle, $topLeftTitle);

        $pageNo = 1;

        //each page is for one item
        foreach($pages as $key => $page)
        {
            $this->currentElementId = $key;

            for($i = 1; $i <= $page['item_pages']->count(); $i++)
            {
                if( ( ! $page['item_pages'] instanceof SplFixedArray ) || ( ! $page['item_pages']->offsetExists($i) ) )
                {
                    continue;
                }

                // Grand total only if last page
                $printGrandTotal = ( ( $i + 1 ) == $page['item_pages']->count() ) ? true : false;

                $this->createNewPage($pageNo, false, 0);
                $itemPage = $page['item_pages']->offsetGet($i);

                $this->currentNotListedId = null;
                $this->currentNotListedCount = 1;

                foreach($itemPage as $item)
                {
                    $itemType = $item[ PageGenerator::ROW_BILL_ITEM_TYPE ];

                    switch($itemType)
                    {
                        case self::ROW_TYPE_BLANK:

                            if( $description != '' && $prevItemType != '' )
                            {
                                if( $prevItemType == BillItem::TYPE_HEADER_N || $prevItemType == BillItem::TYPE_HEADER )
                                {
                                    $this->newItem();

                                    if( strpos($description, $this->printSettings['layoutSetting']['contdPrefix']) !== false )
                                    {
                                        $this->setItemHead($description, $prevItemType, $prevItemLevel);
                                    }
                                    else
                                    {
                                        $this->setItemHead($description, $prevItemType, $prevItemLevel, true);
                                    }
                                }

                                $description = '';
                            }
                            break;
                        case self::ROW_TYPE_ELEMENT:

                            if( strpos($item[ PageGenerator::ROW_BILL_ITEM_DESCRIPTION ], $this->printSettings['layoutSetting']['contdPrefix']) !== false )
                            {
                                $this->setElementTitle($this->printSettings['layoutSetting']['contdPrefix']);
                            }
                            else
                            {
                                $this->setElement(array( 'description' => $item[ PageGenerator::ROW_BILL_ITEM_DESCRIPTION ] ));
                            }

                            break;
                        case BillItem::TYPE_HEADER_N:
                        case BillItem::TYPE_HEADER:

                            $description .= $item[ PageGenerator::ROW_BILL_ITEM_DESCRIPTION ] . "\n";
                            $prevItemType = $item[ PageGenerator::ROW_BILL_ITEM_TYPE ];
                            $prevItemLevel = $item[ PageGenerator::ROW_BILL_ITEM_LEVEL ];

                            break;
                        case BillItem::TYPE_ITEM_NOT_LISTED:

                            $description .= str_replace(array( '&nbsp;' ), "\t\t", $item[ PageGenerator::ROW_BILL_ITEM_DESCRIPTION ]) . "\n";

                            if( $item[ PageGenerator::ROW_BILL_ITEM_ID ] )
                            {
                                if( $this->currentNotListedId != $item[ PageGenerator::ROW_BILL_ITEM_ID ] )
                                {
                                    $this->currentNotListedId = $item[ PageGenerator::ROW_BILL_ITEM_ID ];
                                    $this->currentNotListedCount = 1;
                                }
                                else
                                {
                                    $this->currentNotListedId = $item[ PageGenerator::ROW_BILL_ITEM_ID ];
                                    $this->currentNotListedCount += 1;
                                }

                                $this->newItem();

                                $this->setItem($description, $itemType, $item[ PageGenerator::ROW_BILL_ITEM_LEVEL ]);

                                $this->processItems($item);

                                $description = '';
                            }

                            break;
                        default:

                            $description .= $item[ PageGenerator::ROW_BILL_ITEM_DESCRIPTION ] . "\n";
                            $char .= $item[ PageGenerator::ROW_BILL_ITEM_ROW_IDX ];

                            if( $item[ PageGenerator::ROW_BILL_ITEM_ID ] )
                            {
                                $this->newItem();

                                $this->setItem($description, $itemType, $item[ PageGenerator::ROW_BILL_ITEM_LEVEL ], $char);

                                $this->processItems($item);

                                $description = '';
                                $char = '';
                            }

                            break;
                    }

                }

                if( $printGrandTotal )
                {
                    $this->createFooter(true);
                }
                else
                {
                    $this->createFooter();
                }

                $pageNo++;
            }
        }

        //write to Excel File
        $this->fileInfo = $this->writeExcel();
    }

    /**
     * Sets the values for the estimated and tendered rates and totals for an item.
     *
     * @param $item
     */
    public function processItems($item)
    {
        parent::setUnit($item[ PageGenerator::ROW_BILL_ITEM_UNIT ]);

        if( $this->printQty )
        {
            if( $item && is_array($item[ PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT ]) )
            {
                $quantity = 0;

                foreach($this->billColumnSettings as $column)
                {
                    $qtyField = array_key_exists($column['id'], $item[ PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT ]) ? $item[ PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT ][ $column['id'] ] : 0;
                    $quantity += ( $qtyField * $column['quantity'] );
                }
            }
            else
            {
                $quantity = 0;
            }

            parent::setNormalQtyValue($this->colQty, $quantity);
        }

        $currentColumn = $this->colEstimate;
        foreach($this->projectRevisions as $revisionNumber => $revision)
        {
            // Skip displaying values for not listed items that do not belong to the current tenderer.
            if( !($item[ self::ROW_BILL_ITEM_TYPE ] == BillItem::TYPE_ITEM_NOT_LISTED && ( empty( $item[ self::ROW_BILL_ITEM_RATE ][ 0 ] ) )) )
            {
                $rate = isset( $this->estimateRateRevisions[ $revisionNumber ][ $item[ PageGenerator::ROW_BILL_ITEM_ID ] ] ) ? $this->estimateRateRevisions[ $revisionNumber ][ $item[ PageGenerator::ROW_BILL_ITEM_ID ] ] : null;
                $total = isset( $this->estimateTotalRevisions[ $revisionNumber ][ $item[ PageGenerator::ROW_BILL_ITEM_ID ] ] ) ? $this->estimateTotalRevisions[ $revisionNumber ][ $item[ PageGenerator::ROW_BILL_ITEM_ID ] ] : null;
                parent::setValue($currentColumn, $rate);
                $currentColumn++;
                parent::setValue($currentColumn, $total);
                $currentColumn++;
            }
            else
            {
                $currentColumn++;
                $currentColumn++;
            }
        }

        $currentColumn = $this->getPreviousColumn($currentColumn);

        if( count($this->tenderers) )
        {
            $tendererCounter = 1;

            foreach($this->tenderers as $index => $tenderer)
            {
                //revision rates
                foreach($this->projectRevisions as $revisionNumber => $revision)
                {
                    ++$currentColumn;
                    $rateColumn = $currentColumn;
                    ++$currentColumn;
                    $totalColumn = $currentColumn;

                    // Skip displaying values for not listed items that do not belong to the current tenderer.
                    if( !($item[ self::ROW_BILL_ITEM_TYPE ] == BillItem::TYPE_ITEM_NOT_LISTED && ( empty( $item[ self::ROW_BILL_ITEM_RATE ][ $index + 1 ] ) )) )
                    {
                        $rate = isset( $this->itemRateLog[ $tenderer['id'] ][ $revisionNumber ][ $item[ PageGenerator::ROW_BILL_ITEM_ID ] ] ) ? $this->itemRateLog[ $tenderer['id'] ][ $revisionNumber ][ $item[ PageGenerator::ROW_BILL_ITEM_ID ] ]['rate'] : null;
                        $total = isset( $this->itemRateLog[ $tenderer['id'] ][ $revisionNumber ][ $item[ PageGenerator::ROW_BILL_ITEM_ID ] ] ) ? $this->itemRateLog[ $tenderer['id'] ][ $revisionNumber ][ $item[ PageGenerator::ROW_BILL_ITEM_ID ] ]['grand_total'] : null;

                        parent::setValue($rateColumn, $rate);
                        parent::setValue($totalColumn, $total);
                    }
                }

                $tendererCounter++;
            }
        }

        //if deleted, set style
        if( $item[ PageGenerator::ROW_BILL_ITEM_DELETED ] )
        {
            $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray(array(
                'font' => array(
                    'strike' => true,
                ),
                'fill' => array(
                    'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array( 'rgb' => 'FAFCB8' )
                ),
            ));
        }
    }

    /**
     * Prints the row for the grand total.
     */
    public function printGrandTotal()
    {
        $newLineStyle = array(
            'borders' => array(
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'outline'  => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'top'      => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                ),
                'bottom'   => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                )
            )
        );

        $totalStyle = array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'wrapText'   => true
            )
        );

        if( $this->printQty )
        {
            $this->activeSheet->getStyle($this->colQty . $this->currentRow)->applyFromArray($totalStyle);
        }
        else
        {
            $this->activeSheet->getStyle($this->colDescription . $this->currentRow)->applyFromArray($totalStyle);
        }

        $this->printTotalText();

        $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );

        $this->printGrandTotalValue($newLineStyle);

        $this->currentRow++;
    }

}