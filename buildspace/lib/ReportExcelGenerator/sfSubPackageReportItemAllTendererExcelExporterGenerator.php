<?php

class sfSubPackageReportItemAllTendererExcelExporterGenerator extends sfBuildspaceExcelReportGenerator {

    protected $subConsElementTotals = array();
    protected $subCons = array();
    protected $subConRates = array();
    protected $estimateElementTotals = array();
    protected $currentElementId = null;

    public $totalPage = 0;
    private $pageNo = 1;
    private $isLastPage = false;
    private $currentBill = null;

    public $colItem             = "B";
    public $colDescription      = "C";
    public $colUnit             = "D";
    public $colQuantity         = "E";
    public $colEstimate         = "F";
    public $colEstimateRate     = "F";
    public $colEstimateTotal    = "G";

    public $currentRow = 1;

    public $lastCol;
    private $itemQuantities = array();

    public function __construct(ProjectStructure $project, $printingPageTitle, $printSettings)
    {
        $filename = ( $printingPageTitle ) ? $printingPageTitle : $project->title . '-' . date('dmY H_i_s');
        $savePath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';

        parent::__construct($project, $savePath, $filename, $printSettings);
    }

    public function process($pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
    {
        if( ! ( $pages instanceof SplFixedArray ) )
        {
            return;
        }

        $this->setExcelParameter($lock, $withoutCents);

        $this->createSheet($header, $subTitle, $topLeftTitle);

        $description = '';
        $char = '';
        $prevItemType = '';
        $prevItemLevel = 0;

        foreach($pages as $i => $page)
        {
            if( ! $page )
            {
                continue;
            }

            $this->createNewPage($this->pageNo, false, 0);

            $itemPage = $page;
            $lastItemKeyCounter = 1;

            foreach($itemPage as $item)
            {
                $lastItemKeyCounter++;

                $itemType = $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_TYPE ];

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

                        if( strpos($item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION ], $this->printSettings['layoutSetting']['contdPrefix']) !== false )
                        {
                            $this->setElementTitle($this->printSettings['layoutSetting']['contdPrefix']);
                        }
                        else
                        {
                            $this->setElement(array( 'description' => $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION ] ));
                        }

                        break;
                    case BillItem::TYPE_HEADER_N:
                    case BillItem::TYPE_HEADER:

                        $description .= $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION ] . "\n";
                        $prevItemType = $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_TYPE ];
                        $prevItemLevel = $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_LEVEL ];

                        break;

                    case BillItem::TYPE_ITEM_LUMP_SUM:
                    case BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE:

                        $description .= $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION ] . "\n";

                        if( $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_ID ] )
                        {
                            $this->newItem();

                            $this->setBillItem($item, $description, $char);

                            $this->setUnit($item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_UNIT ]);

                            $this->setRate('-');

                            $description = '';

                            $char = '';
                        }

                        break;
                    default:
                        $description .= $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION ] . "\n";
                        $char .= $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_ROW_IDX ];

                        if( $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_ID ] )
                        {
                            $this->newItem();

                            $this->setBillItem($item, $description, $char);

                            $this->processItems($item);

                            $description = '';
                            $char = '';
                        }

                        break;
                }
            }

            $isLastPage = ($i+1 == $pages->count());
            $this->setIsLastPage($isLastPage);

            $this->createFooter($this->isLastPage);

            $this->pageNo++;
        }
    }

    public function createSheet($billHeader = null, $topLeftTitle = '', $subTitle = '')
    {
        $this->setActiveSheet(0);

        $this->startBillCounter();

        $this->setBillHeader($billHeader, $topLeftTitle, $subTitle);
    }

    public function createHeader($new = false)
    {
        $row = $this->currentRow;
        $lastDynamicColumn = $this->colEstimateTotal;

        //set default column
        $this->activeSheet->setCellValue($this->colItem . $row, self::COL_NAME_NO);
        $this->mergeRows($this->colItem, $row);

        $this->activeSheet->setCellValue($this->colDescription . $row, self::COL_NAME_DESCRIPTION);
        $this->mergeRows($this->colDescription, $row);

        $this->activeSheet->setCellValue($this->colUnit . $row, self::COL_NAME_UNIT);
        $this->mergeRows($this->colUnit, $row);

        $this->activeSheet->setCellValue($this->colQuantity . $row, self::COL_NAME_QTY);
        $this->mergeRows($this->colQuantity, $row);

        $this->activeSheet->setCellValue($this->colEstimate . $row, self::COL_NAME_ESTIMATE);
        $this->mergeColumns($this->colEstimate, $row);

        $this->activeSheet->setCellValue($this->colEstimateRate . $this->getNextRow($row), self::COL_NAME_RATE);
        $this->activeSheet->setCellValue($this->colEstimateTotal . $this->getNextRow($row), self::COL_NAME_TOTAL);

        foreach($this->subCons as $subCon)
        {
            $lastDynamicColumn++;

            $subConName = CompanyTable::formatCompanyName($subCon);

            if( isset( $subCon['selected'] ) AND $subCon['selected'] )
            {
                // set the selected tenderer a blue marker
                $objRichText = new PHPExcel_RichText();
                $objBold = $objRichText->createTextRun('*' . CompanyTable::formatCompanyName($subCon));
                $objBold->getFont()->setBold(true)->getColor()->setRGB('0000FF');

                $subConName = $objRichText;
            }

            $this->activeSheet->setCellValue($lastDynamicColumn . $row, $subConName);

            $this->activeSheet->setCellValue($lastDynamicColumn . $row, $subConName);
            $this->mergeColumns($lastDynamicColumn, $row);

            $this->activeSheet->setCellValue($lastDynamicColumn . $this->getNextRow($row), self::COL_NAME_RATE);
            $this->activeSheet->setCellValue($this->getNextColumn($lastDynamicColumn) . $this->getNextRow($row), self::COL_NAME_TOTAL);

            $this->activeSheet->getColumnDimension($lastDynamicColumn)->setWidth(12);
            $this->activeSheet->getColumnDimension($this->getNextColumn($lastDynamicColumn))->setWidth(12);

            //for contractor total
            $lastDynamicColumn++;
        }

        $this->lastCol = $lastDynamicColumn;

        $this->activeSheet->getColumnDimension($this->colDescription)->setWidth(45);
        $this->activeSheet->getStyle($this->colDescription)->applyFromArray($this->getDescriptionStyling());

        $this->activeSheet->getColumnDimension($this->colEstimateRate)->setWidth(12);
        $this->activeSheet->getColumnDimension($this->colEstimateTotal)->setWidth(12);

        $this->activeSheet->getStyle("{$this->firstCol}{$row}:{$this->lastCol}{$this->getNextRow($row)}")->applyFromArray($this->getColumnHeaderStyle());

        // Current row is changed to the one after the second Header row.
        $this->currentRow++;
    }

    public function setBillItem($item, $description, $char)
    {
        $listOfRates = array();
        $row = $this->currentRow;
        $lastDynamicColumn = $this->colEstimateTotal;
        $billId = $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_ID ];

        $this->activeSheet->setCellValue($this->colItem . $row, $char);
        $this->activeSheet->getStyle($this->colItem . $row)->applyFromArray($this->getNoStyle());

        $this->activeSheet->setCellValue($this->colDescription . $row, $description);

        $this->activeSheet->setCellValue($this->colUnit . $row, $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_UNIT ]);
        $this->activeSheet->getStyle($this->colUnit . $row)->applyFromArray($this->getUnitStyle());

        $this->activeSheet->setCellValue($this->colQuantity . $row, $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT ]);
        $this->activeSheet->getStyle($this->colQuantity . $row)->applyFromArray($this->getUnitStyle());

        $this->activeSheet->setCellValue($this->colEstimateRate . $row, $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_RATE ]);
        $this->activeSheet->getStyle($this->colEstimateRate . $row)->applyFromArray($this->getRateStyling());
        $this->activeSheet->getStyle($this->colEstimateRate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

        $this->activeSheet->setCellValue($this->colEstimateTotal . $row, $item[ sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_TOTAL ]);
        $this->activeSheet->getStyle($this->colEstimateTotal . $row)->applyFromArray($this->getRateStyling());
        $this->activeSheet->getStyle($this->colEstimateTotal . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());


        foreach($this->subCons as $subCon)
        {
            if( array_key_exists($subCon['id'], $this->subConRates) && array_key_exists($billId, $this->subConRates[ $subCon['id'] ]) )
            {
                $listOfRates[] = $this->subConRates[ $subCon['id'] ][ $billId ];
            }
        }

        $lowestRate = count($listOfRates) ? min($listOfRates) : 0;
        $highestRate = count($listOfRates) ? max($listOfRates) : 0;
        $lowestSubConId = $this->subCons[ array_search($lowestRate, $listOfRates) ]['id'];
        $highestSubConId = $this->subCons[ array_search($highestRate, $listOfRates) ]['id'];

        foreach($this->subCons as $subCon)
        {
            $lastDynamicColumn++;

            $contractorRate = isset( $this->subConRates[ $subCon['id'] ][ $billId ] ) ? $this->subConRates[ $subCon['id'] ][ $billId ] : 0;

            $itemQuantity = (array_key_exists($billId, $this->itemQuantities)) ? $this->itemQuantities[ $billId ] : 0;

            $contractorTotal = $contractorRate * $itemQuantity;

            if( $lowestSubConId == $highestSubConId )
            {
                parent::setValue($lastDynamicColumn, $contractorRate);
                parent::setValue($this->getNextColumn($lastDynamicColumn), $contractorTotal);
            }
            else
            {
                if( $subCon['id'] == $lowestSubConId )
                {
                    parent::setLowestValue($lastDynamicColumn, $contractorRate);
                    parent::setLowestValue($this->getNextColumn($lastDynamicColumn), $contractorTotal);
                }
                else if( $subCon['id'] == $highestSubConId )
                {
                    parent::setHighestValue($lastDynamicColumn, $contractorRate);
                    parent::setHighestValue($this->getNextColumn($lastDynamicColumn), $contractorTotal);
                }
                else
                {
                    parent::setValue($lastDynamicColumn, $contractorRate);
                    parent::setValue($this->getNextColumn($lastDynamicColumn), $contractorTotal);
                }
            }

            $lastDynamicColumn++;
        }
    }

    public function createFooter($printGrandTotal = false)
    {
        $this->newLine(true);

        $this->currentRow++;

        if( $printGrandTotal )
        {
            $this->printGrandTotal();
        }

        if( $this->currentPage >= 1 )
        {
            $this->activeSheet->setBreak($this->colDescription . $this->currentRow, PHPExcel_Worksheet::BREAK_ROW);

            $this->createFooterPageNo();
        }

        if( $printGrandTotal || $this->currentPage >= 1 )
        {
            $this->currentRow += 2;
        }
    }

    public function createFooterPageNo()
    {
        $this->currentRow++;

        $location = $this->colItem . $this->currentRow;

        $this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow);

        $this->currentRow++;

        $text = "Page {$this->currentPage} of {$this->totalPage}";

        $pageNoStyle = array(
            'font' => array(
                'bold' => true
            )
        );

        $this->activeSheet->setCellValue($location, $text);
        $this->activeSheet->getStyle($location)->applyFromArray($pageNoStyle);
    }

    public function printTotalText($title = false)
    {
        $this->activeSheet->mergeCells($this->colDescription . $this->currentRow . ':' . $this->colQuantity . $this->currentRow);
        $this->activeSheet->setCellValue($this->colDescription . $this->currentRow, ( $title ) ? $title : "{$this->currentBill} ({$this->getCurrency()}):");
    }

    public function printGrandTotalValue($style)
    {
        $row = $this->currentRow;
        $lastDynamicColumn = $this->colEstimateTotal;

        $estimateTotal = $this->estimateElementTotals[ $this->currentElementId ];
        $this->activeSheet->setCellValue($this->colEstimateRate . $row, $estimateTotal);
        $this->mergeColumns($this->colEstimateRate, $row);

        $this->activeSheet->getStyle($this->colEstimateRate . $row)->applyFromArray($this->getRateStyling());
        $this->activeSheet->getStyle($this->colEstimateRate . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

        foreach($this->subCons as $subCon)
        {
            $lastDynamicColumn++;

            $val = (array_key_exists($subCon['id'], $this->subConsElementTotals) && array_key_exists($this->currentElementId, $this->subConsElementTotals[ $subCon['id'] ])) ? $this->subConsElementTotals[ $subCon['id'] ][ $this->currentElementId ] : 0;
            $this->activeSheet->setCellValue($lastDynamicColumn . $row, $val);
            $this->mergeColumns($lastDynamicColumn, $row);

            $this->activeSheet->getStyle($lastDynamicColumn . $row)->applyFromArray($this->getRateStyling());
            $this->activeSheet->getStyle($lastDynamicColumn . $row)->getNumberFormat()->applyFromArray($this->getNumberFormatStandard());

            // For Contractor Total column.
            $lastDynamicColumn++;
        }

        $this->activeSheet->getStyle($this->colEstimate . $row . ":" . $this->lastCol . $row)->applyFromArray($style);
    }

    public function setBillHeader($billHeader = null, $topLeftTitle, $subTitle)
    {
        $billHeader = ( $billHeader ) ? $billHeader : $this->filename;

        //Set Top Header
        $this->activeSheet->setCellValue($this->firstCol . $this->currentRow, $billHeader);
        $this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow);
        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getProjectTitleStyle());
        $this->currentRow++;

        //Set SubTitle
        $this->activeSheet->setCellValue($this->firstCol . $this->currentRow, $subTitle);
        $this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow);
        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->lastCol . $this->currentRow)->applyFromArray($this->getSubTitleStyle());
        $this->currentRow++;

        //Set Top Left Title
        $this->activeSheet->setCellValue($this->firstCol . $this->currentRow, $topLeftTitle);
        $this->activeSheet->mergeCells($this->firstCol . $this->currentRow . ':' . $this->colDescription . $this->currentRow);
        $this->activeSheet->getStyle($this->firstCol . $this->currentRow . ':' . $this->colDescription . $this->currentRow)->applyFromArray($this->getLeftTitleStyle());
        $this->currentRow++;
    }

    public function startBillCounter()
    {
        $this->firstCol = $this->colItem;
        $lastDynamicColumn = $this->colEstimateTotal;

        array_walk($this->subCons, function () use (&$lastDynamicColumn)
        {
            $lastDynamicColumn++;
            $lastDynamicColumn++;
        });

        $this->lastCol = $lastDynamicColumn;
    }

    public function setParameters($estimateElementTotals, array $newSubCons, $subConsElementTotals, $subConRates, $itemQuantities)
    {
        $this->estimateElementTotals = $estimateElementTotals;
        $this->subCons = $newSubCons;
        $this->subConsElementTotals = $subConsElementTotals;
        $this->subConRates = $subConRates;
        $this->itemQuantities = $itemQuantities;
    }

    public function setCurrentElementId($elementId)
    {
        $this->currentElementId = $elementId;
    }

    public function getDescriptionStyling()
    {
        return array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );
    }

    public function setBill($bill)
    {
        $this->currentBill = $bill;
    }

    public function setTotalPage($totalPage)
    {
        $this->totalPage = $totalPage;
    }

    public function setIsLastPage($isLastPage)
    {
        $this->isLastPage = $isLastPage;
    }

    public function setCurrency($getCurrency)
    {
        $this->currency = $getCurrency;
    }

}