<?php

use sfBuildspaceReportItemRateAndTotalPageGenerator as PageGenerator;

class sfItemRateAndTotalPerUnitReportGenerator extends sfBuildspaceExcelReportGenerator {

    public $colItem          = "B";
    public $colDescription   = "C";
    public $colUnit          = "D";
    public $colQty           = "E";
    public $colEstimateRate  = "F";
    public $colEstimateTotal = "G";
    public $colEstimate      = "F";

    public $printQty;
    public $billColumnSettings;
    public $tenderers;
    public $contractorRates;
    public $contractorTotals;
    public $contractorElementTotals;
    public $estimateElementTotals;
    public $currentElementId;
    public $currentNotListedId;
    public $currentNotListedCount;
    private $currentBillColumnSetting;

    public function __construct($project = null, $savePath = null, $filename = null, $printQty = false, $printSettings)
    {
        $filename = ( $filename ) ? $filename : $this->bill->title . '-' . date('dmY H_i_s');

        $savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        // Always print quantity
        $this->printQty = true;

        parent::__construct($project, $savePath, $filename, $printSettings);
    }

    /**
     * Sets the parameters for the class.
     *
     * @param $billColumnSettings
     * @param $tenderers
     * @param $contractorRates
     * @param $estimateElementTotals
     * @param $contractorElementTotals
     */
    public function setParameter($billColumnSettings, $tenderers, $contractorRates, $estimateElementTotals, $contractorElementTotals)
    {
        $this->billColumnSettings = $billColumnSettings;

        $this->tenderers = $tenderers;

        $this->contractorRates = $contractorRates;

        $this->estimateElementTotals = $estimateElementTotals;

        $this->contractorElementTotals = $contractorElementTotals;
    }

    /**
     * Prints the text 'Total' in the desired cell.
     *
     * @param bool $title
     */
    public function printTotalText($title = false)
    {
        $this->activeSheet->setCellValue($this->colQty . $this->currentRow, "Total Per Unit:");
    }

    /**
     * Sets the value for the grand total.
     *
     * @param $style
     */
    public function printGrandTotalValue($style)
    {
        $perUnitTotal = 0;
        if( isset( $this->estimateElementTotals[ $this->currentElementId ][ $this->currentBillColumnSetting['id'] ] ) )
        {
            $perUnitTotal = $this->estimateElementTotals[ $this->currentElementId ][ $this->currentBillColumnSetting['id'] ];
        }
        $this->setValue($this->colEstimateRate, $perUnitTotal);
        $this->activeSheet->mergeCells($this->colEstimateRate . $this->currentRow . ':' . $this->colEstimateTotal . $this->currentRow);

        $currentColumn = $this->colEstimateTotal;

        if( count($this->tenderers) )
        {
            foreach($this->tenderers as $tenderer)
            {
                ++$currentColumn;

                $grandTotal = 0;

                if( isset( $this->contractorElementTotals[ $tenderer['id'] ][ $this->currentElementId ][ $this->currentBillColumnSetting['id'] ] ) )
                {
                    $grandTotal = $this->contractorElementTotals[ $tenderer['id'] ][ $this->currentElementId ][ $this->currentBillColumnSetting['id'] ];
                }

                $this->setValue($currentColumn, $grandTotal);
                $this->activeSheet->mergeCells($currentColumn . $this->currentRow . ':' . $this->getNextColumn($currentColumn) . $this->currentRow);
                ++$currentColumn;
            }
        }

        $this->activeSheet->getStyle($this->colEstimateRate . $this->currentRow . ":" . $currentColumn . $this->currentRow)
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
        $this->firstCol   = $this->colItem;

        if( count($this->tenderers) )
        {
            $currentColumn = $this->colEstimateTotal;

            foreach($this->tenderers as $tenderer)
            {
                // One for 'rate' column and another for 'total' column
                ++$currentColumn;
                ++$currentColumn;
            }

            $this->lastCol = $currentColumn;
        }
        else
        {
            $this->lastCol = $this->colEstimateTotal;
        }

        $this->currentElementNo = 0;
        $this->columnSetting    = null;
    }

    /**
     * Creates the header.
     *
     * @param bool $new
     */
    public function createHeader($new = false)
    {
        $this->activeSheet->setCellValue($this->colItem . $this->currentRow, "{$this->topLeftTitle} - {$this->currentBillColumnSetting['name']}");
        $this->activeSheet->mergeCells($this->colItem . "{$this->currentRow}:" . $this->colDescription . $this->currentRow);
        $this->activeSheet->getStyle($this->colItem . "{$this->currentRow}:" . $this->colDescription . $this->currentRow)->applyFromArray($this->getLeftTitleStyle());

        $this->currentRow++;
        $row = $this->currentRow;

        // Account for merged header rows
        $this->currentRow++;

        //set default column
        $this->activeSheet->setCellValue($this->colItem . $row, self::COL_NAME_BILL_REF);
        $this->mergeRows($this->colItem, $row);

        $this->activeSheet->setCellValue($this->colDescription . $row, self::COL_NAME_DESCRIPTION);
        $this->mergeRows($this->colDescription, $row);

        $this->activeSheet->setCellValue($this->colUnit . $row, self::COL_NAME_UNIT);
        $this->mergeRows($this->colUnit, $row);

        $this->activeSheet->setCellValue($this->colEstimateRate . $row, self::COL_NAME_ESTIMATE);
        $this->activeSheet->mergeCells($this->colEstimateRate . $row . ':' . $this->colEstimateTotal . $row);

        $this->activeSheet->setCellValue($this->colEstimateRate . $this->getNextRow($row), self::COL_NAME_RATE);
        $this->activeSheet->setCellValue($this->colEstimateTotal . $this->getNextRow($row), self::COL_NAME_SINGLE_UNIT_TOTAL);

        $this->activeSheet->setCellValue($this->colQty . $row, self::COL_NAME_SINGLE_UNIT_QTY);
        $this->activeSheet->getColumnDimension($this->colQty)->setWidth(13);
        $this->mergeRows($this->colQty, $row);

        $currentColumn = $this->colEstimateTotal;

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
                    $objBold     = $objRichText->createTextRun(( ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : $tenderer['name'] ));
                    $objBold->getFont()->setBold(true)->getColor()->setRGB('0000FF');

                    $tendererName = $objRichText;
                }

                // column for rate
                $this->activeSheet->setCellValue($currentColumn . $row, $tendererName);
                // merge with column for total
                $this->activeSheet->mergeCells($currentColumn . $row . ':' . $this->getNextColumn($currentColumn) . $row);

                $this->activeSheet->setCellValue($currentColumn . $this->getNextRow($row), self::COL_NAME_RATE);
                $this->activeSheet->setCellValue($this->getNextColumn($currentColumn) . $this->getNextRow($row), self::COL_NAME_SINGLE_UNIT_TOTAL);

                $this->activeSheet->getColumnDimension($currentColumn)->setWidth(15);
                $this->activeSheet->getColumnDimension($this->getNextColumn($currentColumn))->setWidth(15);

                // To account for 'total' column(s)
                ++$currentColumn;
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
        $this->activeSheet->getColumnDimension($this->colEstimateRate)->setWidth(15);
        $this->activeSheet->getColumnDimension($this->colEstimateTotal)->setWidth(15);
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
    public function process($pages, $lock = false, $header, $topLeftTitle, $subTitle, $withoutCents, $totalPage)
    {
        $this->topLeftTitle = $topLeftTitle;

        $this->setExcelParameter($lock, $withoutCents);

        $this->totalPage = ( $totalPage * count($this->billColumnSettings) );

        $description   = '';
        $char          = '';
        $prevItemType  = '';
        $prevItemLevel = 0;

        $this->createSheet($header, null, $this->project->title);

        $pageNo = 1;

        foreach($this->billColumnSettings as $billColumnSetting)
        {
            $this->currentBillColumnSetting = $billColumnSetting;

            foreach($pages as $elementId => $element)
            {
                $this->currentElementId = $elementId;

                $pageCountPerElement = 0;

                foreach($element['item_pages'] as $i => $itemPage)
                {
                    if( ! ( $element['item_pages'] instanceof SplFixedArray ) || empty( $itemPage ) )
                    {
                        continue;
                    }
                    $pageCountPerElement++;
                    $printGrandTotal = ( $pageCountPerElement === $element['item_pages']->count() - 1 );

                    $this->createNewPage($pageNo, false, 0);

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
                                $prevItemType  = $item[ PageGenerator::ROW_BILL_ITEM_TYPE ];
                                $prevItemLevel = $item[ PageGenerator::ROW_BILL_ITEM_LEVEL ];

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
                                    $char        = '';
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

        $quantity = null;
        if( isset( $item[ PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT ][ $this->currentBillColumnSetting['id'] ] ) )
        {
            $quantity = $item[ PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT ][ $this->currentBillColumnSetting['id'] ];
        }
        parent::setNormalQtyValue($this->colQty, $quantity);

        $estimateRate  = $item[ PageGenerator::ROW_BILL_ITEM_RATE ];
        $estimateTotal = null;
        if( isset( $item[ PageGenerator::ROW_BILL_ITEM_TOTAL ][ $this->currentBillColumnSetting['id'] ] ) )
        {
            $estimateTotal = $item[ PageGenerator::ROW_BILL_ITEM_TOTAL ][ $this->currentBillColumnSetting['id'] ];
        }

        parent::setValue($this->colEstimateRate, $estimateRate);
        parent::setValue($this->colEstimateTotal, $estimateTotal);

        $currentColumn = $this->colEstimateTotal;

        if( count($this->tenderers) )
        {
            $itemId            = $item[ PageGenerator::ROW_BILL_ITEM_ID ];
            $lowestTendererId  = null;
            $highestTendererId = null;
            $listOfRates       = array();

            foreach($this->tenderers as $k => $tenderer)
            {
                if( isset( $this->contractorRates[ $tenderer['id'] ][ $this->currentElementId ][ $itemId ] ) )
                {
                    $listOfRates[$tenderer['id']] = $this->contractorRates[ $tenderer['id'] ][ $this->currentElementId ][ $itemId ];
                }
            }

            //calculate highest and lowest rates
            $lowestRate  = count($listOfRates) ? min($listOfRates) : 0;
            $highestRate = count($listOfRates) ? max($listOfRates) : 0;

            $lowestTendererId  = array_search($lowestRate, $listOfRates);
            $highestTendererId = array_search($highestRate, $listOfRates);

            $tendererCounter = 1;

            foreach($this->tenderers as $tenderer)
            {
                ++$currentColumn;
                $rateColumn = $currentColumn;
                ++$currentColumn;
                $totalColumn = $currentColumn;

                $tendererRateValue  = null;
                $tendererTotalValue = null;
                if( isset( $this->contractorRates[ $tenderer['id'] ][ $this->currentElementId ][ $itemId ] ) )
                {
                    $tendererRateValue = $this->contractorRates[ $tenderer['id'] ][ $this->currentElementId ][ $itemId ];
                }
                if( $tendererRateValue && isset( $item[ PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT ][ $this->currentBillColumnSetting['id'] ] ) )
                {
                    $tendererTotalValue = $tendererRateValue * $item[ PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT ][ $this->currentBillColumnSetting['id'] ];
                }

                if( $lowestTendererId == $highestTendererId )
                {
                    parent::setValue($rateColumn, $tendererRateValue);
                    parent::setValue($totalColumn, $tendererTotalValue);
                }
                else
                {
                    if( $tenderer['id'] == $lowestTendererId )
                    {
                        parent::setLowestValue($rateColumn, $tendererRateValue);
                        parent::setLowestValue($totalColumn, $tendererTotalValue);
                    }
                    else if( $tenderer['id'] == $highestTendererId )
                    {
                        parent::setHighestValue($rateColumn, $tendererRateValue);
                        parent::setHighestValue($totalColumn, $tendererTotalValue);
                    }
                    else
                    {
                        parent::setValue($rateColumn, $tendererRateValue);
                        parent::setValue($totalColumn, $tendererTotalValue);
                    }
                }

                $tendererCounter++;
            }
        }
    }

    /**
     * Prints the row for the grand total.
     */
    public function printGrandTotal()
    {
        $this->activeSheet->getStyle($this->colQty . $this->currentRow)->applyFromArray($this->getTotalStyle());
        parent::printGrandTotal();

        $this->activeSheet->setCellValue($this->colQty . $this->currentRow, "Units:");

        parent::setNormalQtyValue($this->colEstimate, $this->currentBillColumnSetting['quantity']);

        $this->mergeColumns($this->colEstimate, $this->currentRow, 1 + ( count($this->tenderers) * 2 ));

        $this->activeSheet->getStyle($this->colQty . $this->currentRow)->applyFromArray($this->getTotalStyle());
        $this->activeSheet->getStyle($this->colEstimate . $this->currentRow)
            ->applyFromArray($this->getNewLineStyle());

        $this->currentRow++;

        $this->activeSheet->setCellValue($this->colQty . $this->currentRow, "Final Total:");

        $totalPerUnit = null;
        if( isset( $this->estimateElementTotals[ $this->currentElementId ][ $this->currentBillColumnSetting['id'] ] ) )
        {
            $totalPerUnit = $this->estimateElementTotals[ $this->currentElementId ][ $this->currentBillColumnSetting['id'] ];
        }
        $this->setValue($this->colEstimate, $totalPerUnit * $this->currentBillColumnSetting['quantity']);

        $this->mergeColumns($this->colEstimate, $this->currentRow);

        $currentColumn = $this->colEstimate;

        ++$currentColumn;

        foreach($this->tenderers as $tenderer)
        {
            ++$currentColumn;

            $grandTotal = 0;

            if( isset( $this->contractorElementTotals[ $tenderer['id'] ][ $this->currentElementId ][ $this->currentBillColumnSetting['id'] ] ) )
            {
                $grandTotal = $this->contractorElementTotals[ $tenderer['id'] ][ $this->currentElementId ][ $this->currentBillColumnSetting['id'] ] * $this->currentBillColumnSetting['quantity'];
            }

            parent::setValue($currentColumn, $grandTotal);

            $this->mergeColumns($currentColumn, $this->currentRow);

            ++$currentColumn;
        }

        $this->activeSheet->getStyle($this->colQty . $this->currentRow)->applyFromArray($this->getTotalStyle());
        $this->activeSheet->getStyle($this->colEstimate . $this->currentRow . ":" . $currentColumn . $this->currentRow)
            ->applyFromArray($this->getNewLineStyle());

        $this->currentRow++;
    }
}