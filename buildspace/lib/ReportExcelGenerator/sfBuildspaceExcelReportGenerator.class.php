<?php

class sfBuildspaceExcelReportGenerator extends sfBuildspaceBQMasterFunction {

    public $filename   = "New File";
    public $savePath;
    public $project;
    public $bill;
    public $totalItems = 0;
    public $itemCount  = 0;
    public $lock       = true;
    public $billId     = 0;
    public $columnSetting;

    public    $startRow          = 4;
    public    $newLineGap        = 1;
    public    $currentElement    = array();
    public    $currentElementNo  = 0;
    public    $currentPage       = 0;
    public    $currentPageHeader = array();
    public    $currentRow        = 0;
    public    $currentItemType;
    protected $firstCol;
    protected $lastCol;
    protected $currentCol;
    protected $headerRow;
    protected $itemStartRow;

    public $colItem = "B";
    public $colDescription = "C";
    public $colQty         = "E";
    public $colUnit        = "D";
    public $colRate        = "F";
    public $colAmount      = "G";
    public $colRowType     = 'M';
    public $colItemType    = 'N';
    public $colLevel       = 'O';

    public $withoutCents = false;

    public $fileInfo = null;

    const ROW_TYPE_ELEMENT_TEXT = "Element";
    const ROW_TYPE_ITEM_TEXT    = "Item";

    const COL_NAME_DESCRIPTION       = "Description";
    const COL_NAME_UNIT              = "Unit";
    const COL_NAME_RATE              = "Rate";
    const COL_NAME_WASTAGE           = "Wastage (%)";
    const COL_NAME_WASTAGE_RATE      = "Wastage";
    const COL_NAME_WASTAGE_TOTAL     = "Wastage Total";
    const COL_NAME_PROFIT            = "Profit";
    const COL_NAME_MARKUP            = "Item Markup (%)";
    const COL_NAME_TOTAL_QTY         = "Total Qty";
    const COL_NAME_QTY               = "Qty";
    const COL_NAME_SINGLE_UNIT_QTY   = "Single Unit Qty";
    const COL_NAME_SINGLE_UNIT_TOTAL = "Single Unit Total";
    const COL_NAME_TOTAL_COST        = "Total Cost";
    const COL_NAME_GRAND_TOTAL       = "Grand Total";
    const COL_NAME_TYPE              = "Type";
    const COL_NAME_BILL_REF          = "Bill Ref";
    const COL_NAME_NO                = "No.";
    const COL_NAME_ESTIMATE          = "Estimate";
    const COL_NAME_DIFF_PERCENT      = "Difference (%)";
    const COL_NAME_DIFF_AMOUNT       = "Difference (RM)";
    const COL_NAME_CONTRACT_AMOUNT   = "Contract Amount";
    const COL_NAME_AMOUNT            = "Amount";
    const COL_NAME_PERCENT           = "%";
    const COL_NAME_WORKDONE          = "Work Done";
    const COL_NAME_PREV_PAYMENT      = "Previous Payment";
    const COL_NAME_CURRENT_PAYMENT   = "Current Payment";
    const COL_NAME_INITIAL           = "Initial";
    const COL_NAME_RECURRING         = "Recurring";
    const COL_NAME_FINAL             = "Final";
    const COL_NAME_PAYMENT           = "Payment";
    const COL_NAME_TOTAL             = "Total";
    const COL_NAME_OMISSION          = "Omission";
    const COL_NAME_ADDITION          = "Addition";
    const COL_NAME_NET               = "Nett";
    const COL_NAME_SOR_NO_BUILD_UP    = "SOR (No Build-Up)";
    const COL_NAME_PROFIT_BILL_MARKUP = "Profit (Bill Markup)";
    const COL_NAME_CLAIMED_QTY        = "Claimed Qty";
    const COL_NAME_CLAIMED_AMOUNT     = "Claimed Amount";

    function __construct($project = null, $savePath = null, $filename, $printSettings)
    {
        $this->objPHPExcel = new sfPhpExcel();

        $this->getActiveSheet();

        $this->printSettings = $printSettings;

        $this->project = $project;

        $this->savePath = ( $savePath ) ? $savePath : sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        $this->filename = ( $filename ) ? $filename : $this->filename;
        $this->filename = str_replace('/', '_', $this->filename);

        $this->setGlobalStyling();

        $this->setTitle();
    }

    public function setExcelParameter($lock = false, $withoutCents)
    {
        $this->lock = ( $lock ) ? $lock : $this->lock;

        $this->withoutCents = $withoutCents;
    }

    public function protectSheet()
    {
        $this->activeSheet->getProtection()->setSheet($this->lock);
        $this->activeSheet->getProtection()->setPassword("Buildspace");
    }

    public function createSheet($billHeader = null, $topLeftTitle = '', $subTitle = '')
    {
        $this->billId ++;

        $this->setActiveSheet($this->billId - 1);

        $this->startBillCounter();

        $this->setBillHeader($billHeader, $topLeftTitle, $subTitle);
    }

    public function setBillHeader($billHeader = null, $topLeftTitle, $subTitle)
    {
        $billHeader = ( $billHeader ) ? $billHeader : $this->filename;

        //Set Top Header
        $this->activeSheet->setCellValue($this->firstCol . "1", $billHeader);
        $this->activeSheet->mergeCells($this->firstCol . '1:' . $this->lastCol . '1');
        $this->activeSheet->getStyle($this->firstCol . '1:' . $this->lastCol . '1')->applyFromArray($this->getProjectTitleStyle());

        //Set SubTitle
        $this->activeSheet->setCellValue($this->firstCol . "2", $subTitle);
        $this->activeSheet->mergeCells($this->firstCol . '2:' . $this->lastCol . '2');
        $this->activeSheet->getStyle($this->firstCol . '2:' . $this->lastCol . '2')->applyFromArray($this->getSubTitleStyle());

        $this->activeSheet->setCellValue($this->firstCol . "3", $topLeftTitle);
        $this->activeSheet->mergeCells($this->firstCol . '3:' . $this->colDescription . '3');
        $this->activeSheet->getStyle($this->firstCol . '3:' . $this->colDescription . '3')->applyFromArray($this->getLeftTitleStyle());
    }

    public function setTitle($title = null)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Text' ));

        $invalidTitleChars = array('[', ']', '*', '/', '\\', '?', ':');

        if ( $title )
        {
            return $this->activeSheet->setTitle(truncate_text(str_replace($invalidTitleChars, "_", $title)));
        }

        return $this->activeSheet->setTitle(truncate_text(str_replace($invalidTitleChars, "_", $this->project->title . '-' . date('dmY H_i_s'))));
    }

    /**
     * Starts the bill counter.
     * This sets the first row, currentElementNo and currentRow to the starting.
     * Also determines the first and last column.
     */
    public function startBillCounter()
    {
        $this->currentRow       = $this->startRow;
        $this->firstCol         = $this->colItem;
        $this->lastCol          = $this->colAmount;
        $this->currentElementNo = 0;
        $this->columnSetting    = null;
    }

    public function startElementCounter()
    {
        $this->currentPageHeader = array();
    }

    public function setActiveSheet($index = null)
    {
        $index = ( $index ) ? $index : 0;

        $this->objPHPExcel->setActiveSheetIndex($index);

        $this->getActiveSheet();
    }

    public function getActiveSheet()
    {
        $this->activeSheet = $this->objPHPExcel->getActiveSheet();
    }

    public function hideColumn()
    {
        $this->activeSheet->getColumnDimension($this->colRowType)->setVisible(false);
        $this->activeSheet->getColumnDimension($this->colItemType)->setVisible(false);
        $this->activeSheet->getColumnDimension($this->colLevel)->setVisible(false);
    }

    public function createNewPage($pageNo = null, $printGrandTotal = false, $printFooter = false)
    {
        if ( !$pageNo )
        {
            return;
        }

        if ( $printFooter )
        {
            $this->createFooter($printGrandTotal);
        }

        $this->createHeader(true);

        $this->currentPage = $pageNo;

        $this->newLine();
    }

    /**
     * Creates the sheet, processes the items and creates the footer for all items' pages.
     *
     * @param      $itemPages
     * @param bool $lock
     * @param      $header
     * @param      $subTitle
     * @param      $topLeftTitle
     * @param      $withoutCents
     * @param      $totalPage
     */
    public function process($itemPages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
    {
        $this->setExcelParameter($lock, $withoutCents);

        $this->totalPage = $totalPage;

        $description   = '';
        $char          = '';
        $prevItemType  = '';
        $prevItemLevel = 0;

        $this->createSheet($header, $subTitle, $topLeftTitle);

        foreach ( $itemPages as $pageNo => $page )
        {
            if ( !empty($page) )
            {
                $this->createNewPage($pageNo);

                foreach ( $page as $item )
                {
                    $itemType = $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_TYPE];

                    switch ($itemType)
                    {
                        case self::ROW_TYPE_BLANK:

                            if ( $description != '' && $prevItemType != '' )
                            {
                                if ( $prevItemType == BillItem::TYPE_HEADER_N || $prevItemType == BillItem::TYPE_HEADER )
                                {
                                    $this->newItem();

                                    if ( strpos($description, $this->printSettings['layoutSetting']['contdPrefix']) !== false )
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

                            if ( strpos($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_DESCRIPTION], $this->printSettings['layoutSetting']['contdPrefix']) !== false )
                            {
                                $this->setElementTitle($this->printSettings['layoutSetting']['contdPrefix']);
                            }
                            else
                            {
                                $this->setElement(array( 'description' => $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_DESCRIPTION] ));
                            }

                            break;
                        case BillItem::TYPE_HEADER_N:
                        case BillItem::TYPE_HEADER:

                            $description .= $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_DESCRIPTION] . "\n";
                            $prevItemType  = $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_TYPE];
                            $prevItemLevel = $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_LEVEL];

                            break;

                        case BillItem::TYPE_ITEM_LUMP_SUM:
                        case BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE:

                            $description .= $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_DESCRIPTION] . "\n";

                            if ( $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_ID] )
                            {
                                $this->newItem();

                                $this->setItem($description, $itemType, $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_LEVEL]);

                                $this->setUnit($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_UNIT]);

                                $this->setRate('-');

                                $this->setQuantity('-', $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_INCLUDE], '-');

                                if ( $itemType == BillItem::TYPE_ITEM_LUMP_SUM )
                                {
                                    $this->setAmount($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_RATE]);
                                }

                                $description = '';

                                $char = '';
                            }

                            break;
                        default:
                            $description .= $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_DESCRIPTION] . "\n";
                            $char .= $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_ROW_IDX];

                            if ( $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_ID] )
                            {
                                $this->newItem();

                                $this->setItem($description, $itemType, $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_LEVEL], $char);

                                $this->processItems($item);

                                $description = '';
                                $char        = '';
                            }

                            break;
                    }
                }
                $this->newLine();
            }
        }

        $this->createFooter(true);

        $this->fileInfo = $this->writeExcel();
    }

    /**
     * Prints the row for the grand total.
     */
    public function printGrandTotal()
    {
        $this->activeSheet->getStyle($this->colDescription . $this->currentRow)->applyFromArray($this->getTotalStyle());

        $this->printTotalText();

        $this->printGrandTotalValue($this->getNewLineStyle());

        $this->currentRow ++;
    }

    /**
     * Prints the text 'Total' in the desired cell.
     *
     * @param bool $title
     */
    public function printTotalText($title = false)
    {
        $this->activeSheet->setCellValue($this->colDescription . $this->currentRow, ( $title ) ? $title : "Total:");
    }

    /**
     * Sets the value for the grand total.
     *
     * @param $style
     */
    public function printGrandTotalValue($style)
    {
        $this->activeSheet->getStyle($this->colEstimate . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($style);
    }

    /**
     * Sets the values for an item.
     * @param $item
     */
    public function processItems($item)
    {
        $this->setValue($this->colEstimate, $item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_RATE]);

        $this->setChar($item[sfBuildspaceBQMasterFunction::ROW_BILL_ITEM_ROW_IDX]);
    }

    /**
     * Creates the header.
     *
     * @param bool $new
     */
    public function createHeader($new = false)
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue($this->colItem . $row, "Item");
        $this->activeSheet->setCellValue($this->colDescription . $row, $this->printSettings['phrase']['descHeader']);
        $this->activeSheet->setCellValue($this->colUnit . $row, $this->printSettings['phrase']['unitHeader']);

        //reset Character Counter
        $this->currentChar = 'A';

        if ( ( $new == true && count($this->columnSetting) <= 1 ) )
        {
            $this->excelType = self::EXCEL_TYPE_SINGLE;
            $this->createSingleTypeHeader();
        }
        else
        {
            $this->excelType = self::EXCEL_TYPE_MULTIPLE;
            $this->createMultiTypeHeader();
        }

        //Set Column Width
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension($this->colItem)->setWidth(6);
        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
        $this->activeSheet->getColumnDimension($this->colUnit)->setWidth(6);
        $this->activeSheet->getColumnDimension($this->colRate)->setWidth(8);
        $this->activeSheet->getStyle('B1:G1')->applyFromArray($this->getProjectTitleStyle());
        $this->activeSheet->mergeCells('B1:G1');
        $this->currentRow ++;
    }

    public function createFooter($printGrandTotal = false)
    {
        $this->newLine(true);

        $this->currentRow ++;

        if ( $printGrandTotal )
        {
            $this->printGrandTotal();
        }

        if ( $this->currentPage >= 1 )
        {
            $this->activeSheet->setBreak($this->colDescription . $this->currentRow, PHPExcel_Worksheet::BREAK_ROW);

            $this->createFooterPageNo();
        }

        if ( $printGrandTotal || $this->currentPage >= 1 )
        {
            $this->currentRow += 2;
        }
    }

    public function createFooterPageNo()
    {
        $this->currentRow ++;

        $coord = $this->colItem . $this->currentRow;
        $this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow);

        $this->currentRow ++;

        $text = 'Page ' . $this->currentPage . ' Of ' . $this->totalPage;

        $pageNoStyle = array(
            'font' => array(
                'bold' => true
            )
        );

        $this->activeSheet->setCellValue($coord, $text);
        $this->activeSheet->getStyle($coord)->applyFromArray($pageNoStyle);
    }

    public function newLine($bottom = false)
    {
        $this->currentRow ++;

        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ":" . $this->lastCol . $this->currentRow)->applyFromArray($this->getNewLineStyle($bottom));
    }

    public function getNewLineStyle($bottom = false)
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

        if ( $bottom )
        {
            $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
            $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );
        }

        return $newLineStyle;
    }

    public function setElement($element = null)
    {
        if ( !$element )
        {
            return;
        }

        $this->currentElement = $element;

        $this->currentElementNo ++;

        $this->startElementCounter();

        $this->setElementTitle(null, true);
    }

    public function setItemHead($description, $itemType, $itemLvl, $new = false)
    {
        $coord = $this->colDescription . $this->currentRow;

        $style = $this->getDescriptionStyle(BillItem::TYPE_HEADER_TEXT);

        $this->activeSheet->setCellValue($coord, $description . "\n");

        $this->activeSheet->getStyle($coord)->applyFromArray($style);

        $this->lockCell($coord);
    }

    public function setElementTitle($appendString = null, $new = false)
    {
        if ( !$this->currentElement['description'] )
        {
            return;
        }

        $description = ( $appendString ) ? $this->currentElement['description'] . ' ' . $appendString : $this->currentElement['description'];

        $this->newLine();

        $coord = $this->colDescription . $this->currentRow;

        $style = $this->getElementTitleStyle();

        $this->activeSheet->setCellValue($coord, $description);

        $this->activeSheet->getStyle($coord)->applyFromArray($style);

        $this->lockCell($coord);

        $this->newLine();
    }

    public function newItem()
    {
        $this->newLine();

        $this->itemCount ++;
    }

    public function setItemStyle()
    {
        $coord = $this->colDescription . $this->currentRow;

        $this->activeSheet->getStyle($coord)->applyFromArray($this->getDescriptionStyle($this->itemType));
    }

    public function setItem($description, $itemType, $itemLvl, $char = false)
    {
        $coord = $this->colDescription . $this->currentRow;

        $this->itemType = $itemType;

        $this->activeSheet->setCellValue($coord, $description);

        if ( $char )
        {
            $this->setChar($char);
        }

        $this->setItemStyle();
    }

    public function setDescriptionValue($column, $value)
    {
        $coord = $column . $this->currentRow;

        $this->activeSheet->setCellValue($coord, $value);

        $this->activeSheet->getStyle($coord)->applyFromArray($this->getDescriptionStyle(null));
    }

    public function setPercentageDifference($column, $firstValue, $secValue)
    {
        $coord = $column . $this->currentRow;

        $style = $this->getQtyStyle();

        $format = $this->getNumberFormatPercentage();

        $percentage = ( $secValue != 0 ) ? ( ( $firstValue - $secValue ) / $secValue ) : 0;

        $this->activeSheet->setCellValue($coord, $percentage);

        $this->activeSheet->getStyle($coord)->applyFromArray($style);

        $this->activeSheet->getStyle($coord)->getNumberFormat()->applyFromArray($format);
    }


    public function setPercentageValue($column, $value)
    {
        $coord = $column . $this->currentRow;

        $style = $this->getQtyStyle();

        $format = $this->getNumberFormatPercentage();

        $this->activeSheet->setCellValue($coord, $value);

        $this->activeSheet->getStyle($coord)->applyFromArray($style);

        $this->activeSheet->getStyle($coord)->getNumberFormat()->applyFromArray($format);
    }


    public function setAmountDifference($column, $firstValue, $secValue)
    {
        $coord = $column . $this->currentRow;

        $style = $this->getRateStyle();

        $format = $this->getNumberFormatStandard();

        $amount = ( $secValue != 0 ) ? ( $firstValue - $secValue ) : $firstValue;

        $this->activeSheet->setCellValue($coord, $amount);

        $this->activeSheet->getStyle($coord)->applyFromArray($style);

        $this->activeSheet->getStyle($coord)->getNumberFormat()->applyFromArray($format);
    }

    public function setRegularValue($column, $value)
    {
        $coord = $column . $this->currentRow;

        $this->activeSheet->setCellValue($coord, $value);
    }

    public function getPageInfoFromBillReference($billReferences = array())
    {
        $pageInfo = array();

        if ( !( count($billReferences) > 0 ) )
        {
            return $pageInfo;
        }

        foreach ( $billReferences as $k => $reference )
        {
            $pageInfo['reference_num'] = $reference['reference_num'];
            $pageInfo['page_no']       = $reference['BillPage']['page_no'];
            $pageInfo['element_no']    = $reference['BillPage']['element_no'];
            $pageInfo['bill_ref']      = $reference['bill_ref'];
            $pageInfo['bill_item_id']  = $reference['bill_item_id'];
        }

        return $pageInfo;
    }

    public function getHeaderStartPage($billReferences = array())
    {
        $pageInfo = array();

        if ( !( count($billReferences) > 0 ) )
        {
            return $pageInfo;
        }

        $pageInfo['reference_num'] = $billReferences[0]['reference_num'];
        $pageInfo['page_no']       = $billReferences[0]['BillPage']['page_no'];
        $pageInfo['element_no']    = $billReferences[0]['BillPage']['element_no'];
        $pageInfo['bill_ref']      = $billReferences[0]['bill_ref'];
        $pageInfo['bill_item_id']  = $billReferences[0]['bill_item_id'];

        return $pageInfo;
    }

    public function setSubRow($description, $unit = false, $rate = false, $padding = false)
    {
        $this->newLine();
        $coord     = $this->colDescription . $this->currentRow;
        $rateCoord = $this->colRate . $this->currentRow;

        $style = $this->getSubDescriptionStyle();

        if ( $rate )
        {
            $this->activeSheet->setCellValue($rateCoord, $rate);
        }

        if ( $unit )
        {
            $this->setUnit($unit);
        }

        if ( $padding )
        {
            $description .= "\n";
        }

        $this->activeSheet->setCellValue($coord, $description);
        $this->activeSheet->getStyle($coord)->applyFromArray($style);
        $this->lockCell($coord);

    }

    public function setUnit($unit)
    {
        $coord = $this->colUnit . $this->currentRow;
        $style = $this->getUnitStyle();

        $this->activeSheet->setCellValue($coord, $unit);
        $this->activeSheet->getStyle($coord)->applyFromArray($style);
        $this->lockCell($coord);
    }

    public function setQty($qty)
    {
        $coord = $this->colQty . $this->currentRow;
        $style = $this->getQtyStyle();

        $this->activeSheet->setCellValue($coord, $qty);
        $this->activeSheet->getStyle($coord)->applyFromArray($style);
        $this->unlockCell($coord);
    }

    public function setNormalQtyValue($coord, $qty)
    {
        $coord = $coord . $this->currentRow;
        $style = $this->getQtyStyle();

        $this->activeSheet->setCellValue($coord, $qty);
        $this->activeSheet->getStyle($coord)->applyFromArray($style);

        $this->activeSheet->getStyle($coord)->getNumberFormat()->applyFromArray(array(
            'code' => '#,##0'
        ));

        $this->unlockCell($coord);
    }

    public function setRate($rate)
    {
        $coord = $this->colRate . $this->currentRow;
        $style = $this->getQtyStyle();

        $this->activeSheet->setCellValue($coord, $rate);
        $this->activeSheet->getStyle($coord)->applyFromArray($style);
    }

    public function setChar($char)
    {
        $coord = $this->colItem . $this->currentRow;
        $style = $this->getUnitStyle();

        $this->activeSheet->setCellValue($coord, $char);
        $this->activeSheet->getStyle($coord)->applyFromArray($style);
        $this->lockCell($coord);
    }

    public function setValue($column, $value)
    {
        $coord = $column . $this->currentRow;

        $style = $this->getRateStyle();

        $format = $this->getNumberFormatStandard();

        $this->activeSheet->setCellValue($coord, $value);

        $this->activeSheet->getStyle($coord)->applyFromArray($style);

        $this->activeSheet->getStyle($coord)->getNumberFormat()->applyFromArray($format);
    }

    public function getValue($column)
    {
        $coord = $column . $this->currentRow;

        return $this->activeSheet->getCell($coord)->getValue();
    }

    public function setLowestValue($column, $value)
    {
        $coord = $column . $this->currentRow;

        $style = $this->getLowestRateStyle();

        $format = $this->getNumberFormatStandard();

        $this->activeSheet->setCellValue($coord, $value);

        $this->activeSheet->getStyle($coord)->applyFromArray($style);

        $this->activeSheet->getStyle($coord)->getNumberFormat()->applyFromArray($format);
    }

    /**
     * Set value with the style.
     *
     * @param $column
     * @param $value
     */
    public function setHighestValue($column, $value)
    {
        $coord = $column . $this->currentRow;

        $style = $this->getHighestRateStyle();

        $format = $this->getNumberFormatStandard();

        $this->activeSheet->setCellValue($coord, $value);

        $this->activeSheet->getStyle($coord)->applyFromArray($style);

        $this->activeSheet->getStyle($coord)->getNumberFormat()->applyFromArray($format);
    }

    public function setGlobalStyling()
    {
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $pageMargins = $this->activeSheet->getPageMargins();
        $pageMargins->setTop(0.04);
        $pageMargins->setBottom(0.04);
    }

    public function getColumnHeaderStyle()
    {
        $columnHeadStyle = array(
            'borders'   => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
            ),
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )

        );

        return $columnHeadStyle;
    }

    public function getElementTitleStyle()
    {
        $elementTitleStyle = array(
            'font' => array(
                'bold'      => true,
                'underline' => true
            )
        );

        return $elementTitleStyle;
    }

    public function getUnitStyle()
    {
        $unitStyle = array( 'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
        ) );

        return $unitStyle;
    }


    public function getRateStyle()
    {
        $rateStyle = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );

        return $rateStyle;
    }

    public function getLowestRateStyle()
    {
        $rateStyle = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_TOP
            ),
            'fill'      => array(
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array( 'rgb' => 'adf393' )
            ),
            'font'      => array(
                'bold' => true,

            )
        );

        return $rateStyle;
    }

    public function getHighestRateStyle()
    {
        $rateStyle = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_TOP
            ),
            'fill'      => array(
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array( 'rgb' => 'ee4559' )
            ),
            'font'      => array(
                'bold'  => true,
                'color' => array( 'rgb' => 'FFFFFF' )
            )
        );

        return $rateStyle;
    }

    public function getQtyStyle()
    {
        $qtyStyle = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );

        return $qtyStyle;
    }

    public function getNumberFormatPercentage()
    {
        $format = array(
            'code' => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00
        );

        return $format;
    }

    public function getNumberFormatStandard()
    {
        if ( !$this->withoutCents )
        {
            $format = array(
                'code' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
        }
        else
        {
            $format = array(
                'code' => '#,##0'
            );
        }

        return $format;
    }

    public function getDescriptionStyle($billType = null)
    {
        switch ($billType)
        {
            case BillItem::TYPE_HEADER_N_TEXT:
            case BillItem::TYPE_HEADER_TEXT:
                $descriptionStyle = array(
                    'font'      => array(
                        'bold'      => true,
                        'underline' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'wrapText'   => true
                    )
                );
                break;
            default:
                $descriptionStyle = array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY, //PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'wrapText'   => true
                    )
                );
                break;
        }

        return $descriptionStyle;
    }

    public function getSubDescriptionStyle()
    {
        $descriptionStyle = array(
            'font'      => array(
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'wrapText'   => true,
                'indent'     => 1
            )
        );

        return $descriptionStyle;
    }

    public function getProjectTitleStyle()
    {
        $projectTitleStyle = array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );

        return $projectTitleStyle;
    }

    public function getSubTitleStyle()
    {
        $style = array(
            'font'      => array(
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );

        return $style;
    }

    public function getLeftTitleStyle()
    {
        $style = array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        );

        return $style;
    }

    public function getRightTitleStyle()
    {
        $style = array(
            'font'      => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            )
        );

        return $style;
    }

    public function lockCell($coord)
    {
        $this->activeSheet->protectCells($coord, 'PHPExcel');
    }

    public function unlockCell($coord)
    {
        $this->activeSheet->getStyle($coord)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
    }

    public function writeExcel()
    {
        $filename = Utilities::truncateString($this->filename, 100, "");
        $extension = '.xlsx';
        $objWriter = new PHPExcel_Writer_Excel2007($this->objPHPExcel);
        $objWriter->save($this->savePath . DIRECTORY_SEPARATOR . $filename . $extension);

        return array(
            'filename'  => $filename,
            'extension' => $extension,
            'type'      => ExportedFile::FILE_TYPE_EXCEL_TEXT
        );
    }

    public function writeCSV()
    {
        $filename = Utilities::truncateString($this->filename, 100, "");
        $extension = '.csv';
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'CSV');
        $objWriter->save($this->savePath . DIRECTORY_SEPARATOR . $filename . $extension);

        return array(
            'filename'  => $filename,
            'extension' => $extension,
            'type'      => 'Excel'
        );
    }

    public function generateExcelFile()
    {
        // write to Excel File
        return $this->fileInfo = $this->writeExcel();
    }

    public function getNoStyle()
    {
        return array(
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );
    }

    public function getRateStyling($item = null)
    {
        return array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'font'      => array(
                'color' => array( 'rgb' => '000000' ),
            ),
        );
    }

    public function getRedRateStyling()
    {
        return array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'font'      => array(
                'color' => array( 'rgb' => 'ee4559' ),
            ),
        );
    }

    public function getRedTotalStyle()
    {
        return array(
            'font'      => array(
                'bold' => true,
                'color' => array( 'rgb' => 'ee4559' ),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'wrapText'   => true
            )
        );
    }

    /**
     * Merges the a given number of rows.
     * (rowSpan="x").
     *
     * @param     $column
     * @param     $row
     * @param int $numberOfRows
     */
    public function mergeRows($column, $row, $numberOfRows = 1)
    {
        $nextRow = $row;
        for($i = 0; $i < $numberOfRows; $i++)
        {
            $nextRow = $this->getNextRow($nextRow);
        }
        $lastRowToMerge = $nextRow;
        $this->activeSheet->mergeCells($column . $row . ':' . $column . $lastRowToMerge);
    }

    /**
     * Merges the a given number of columns.
     * (colSpan="x").
     *
     * @param     $column
     * @param     $row
     * @param int $numberOfColumns
     */
    public function mergeColumns($column, $row, $numberOfColumns = 1)
    {
        $nextColumn = $column;
        for($i = 0; $i < $numberOfColumns; $i++)
        {
            $nextColumn = $this->getNextColumn($nextColumn);
        }
        $lastColumnToMerge = $nextColumn;

        $this->activeSheet->mergeCells($column . $row . ':' . $lastColumnToMerge . $row);
    }

    /**
     * Gets the next row without changing the value of the current row.
     * Typically we do a ++$row to get the value for the next row.
     * With this we can get the value of the next row and still keep the original row value.
     *
     * @param $currentRow
     *
     * @return mixed
     */
    public function getNextRow($currentRow)
    {
        $temporaryRow = $currentRow;
        $nextRow = ++$temporaryRow;
        unset( $temporaryRow );

        return $nextRow;
    }

    /**
     * Gets the next column without changing the value of the current column.
     * Typically we do a ++$column to get the value for the next column.
     * With this we can get the value of the next column and still keep the original column value.
     *
     * @param $currentColumn
     *
     * @return mixed
     */
    public function getNextColumn($currentColumn, $steps = 1)
    {
        $temporaryColumn = $currentColumn;

        $nextColumn = $temporaryColumn;

        for($i = 1; $i <= $steps; $i++) $nextColumn = ++$nextColumn;

        unset( $temporaryColumn );

        return $nextColumn;
    }

    public function getPreviousColumn($currentColumn, $steps = 1)
    {
        if( $currentColumn == "A" ) return $currentColumn;

        $columnNumber = PHPExcel_Cell::columnIndexFromString($currentColumn) - $steps;
        $column       = PHPExcel_Cell::stringFromColumnIndex($columnNumber - 1);

        return $column;
    }

    /**
     * Increments the value.
     *
     * @param $value
     * @param $incrementBy
     */
    public function increment(&$value, $incrementBy = 1)
    {
        for($i = 0; $i < $incrementBy; $i++)
        {
            $value++;
        }
    }

    /**
     * Adds Header columns.
     * Column width is set to 15.
     * Returns the number of columns and the Column Map of the newly created headers.
     * Todo: Fix multi-header column header style for levels > 2.
     *
     * @param $columnDefinition string|array    Use strings for column names, and arrays for sibling headers.
     * @param $startColumn      string          The column the headers starts.
     * @param $startRow         string          The row the headers start.
     *
     * @return array    Total number of columns and the Column Map.
     */
    protected function addHeaderColumns($columnDefinition, $startColumn, $startRow)
    {
        $columnMap = array();

        if( ! is_array($columnDefinition) )
        {
            $this->activeSheet->setCellValue($startColumn . $startRow, $columnDefinition);
            $this->activeSheet->getStyle($startColumn . $startRow)->applyFromArray($this->getColumnHeaderStyle());
            $this->activeSheet->getColumnDimension($startColumn)->setWidth(15);

            $columnMap[ $columnDefinition ] = $startColumn;

            return array(
                'numberOfColumns' => 1,
                'map'             => $columnMap
            );
        }

        $numberOfColumns  = 0;
        $childStartColumn = $startColumn;
        foreach($columnDefinition as $parentColumn => $childColumns)
        {
            $nextStartRow = $startRow;
            if( is_array($childColumns) )
            {
                $nextStartRow = $this->getNextRow($startRow);
            }
            $childrenLengthAndMap = $this->addHeaderColumns($childColumns, $childStartColumn, $nextStartRow);
            $childrenLength       = $childrenLengthAndMap['numberOfColumns'];
            $childrenColumnMap    = $childrenLengthAndMap['map'];

            if( is_array($childColumns) )
            {
                $this->activeSheet->setCellValue($childStartColumn . $startRow, $parentColumn);
                $this->mergeColumns($childStartColumn, $startRow, $childrenLength - 1);

                $lastColumn = $childStartColumn;
                $this->increment($lastColumn, $childrenLength - 1);

                $this->activeSheet->getStyle($childStartColumn . $startRow . ':' . $lastColumn . $startRow)->applyFromArray($this->getColumnHeaderStyle());

                $columnMap[ $parentColumn ] = $childrenColumnMap;
            }
            else
            {
                reset($childrenColumnMap);
                $columnName = key($childrenColumnMap);

                $columnMap[ $columnName ] = $childrenColumnMap[ $columnName ];
            }

            $numberOfColumns += $childrenLength;
            $this->increment($childStartColumn, $childrenLength);
        }

        return array(
            'numberOfColumns' => $numberOfColumns,
            'map'             => $columnMap
        );
    }

    public function getNonZeroValue($value, $valueIfZero = '')
    {
        if($value == 0) return $valueIfZero;

        return $value;
    }
}
