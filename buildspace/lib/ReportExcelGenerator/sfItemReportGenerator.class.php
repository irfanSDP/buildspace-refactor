<?php
class sfItemReportGenerator extends sfBuildspaceExcelReportGenerator
{
    public $colQty = "E";
    public $colEstimate = "E";

    public $printQty;

    public $billColumnSettings;
    public $tenderers;
    public $contractorRates;
    public $contractorElementTotals;
    public $estimateElementTotals;
    public $currentElementId;

    public $currentNotListedId;
    public $currentNotListedCount;

    function __construct( $project = null, $estimateElementTotals, $savePath = null, $filename = null, $printQty = false, $printSettings )
    {
        $filename = ( $filename ) ? $filename : $this->bill->title.'-'.date( 'dmY H_i_s' );

        $savePath = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads';

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->estimateElementTotals = $estimateElementTotals;

        $this->printQty = $printQty;

        $this->calculateColumn();

        parent::__construct( $project, $savePath, $filename, $printSettings );
    }

    public function calculateColumn()
    {
        if($this->printQty)
        {
            $this->colQty = "E";

            $this->colEstimate = "F";
        }
    }

    public function setParameter($billColumnSettings, $tenderers, $contractorRates, $contractorElementTotals)
    {
        $this->billColumnSettings = $billColumnSettings;

        $this->tenderers = $tenderers;

        $this->contractorRates = $contractorRates;

        $this->contractorElementTotals = $contractorElementTotals;
    }

    public function printTotalText($title = false)
    {
        if($this->printQty)
        {
            $this->activeSheet->setCellValue( $this->colQty.$this->currentRow, "Total:" );
        }
        else
        {
            $this->activeSheet->setCellValue( $this->colDescription.$this->currentRow, "Total:" );
        }
    }

    public function printGrandTotalValue($style)
    {
        $this->setValue( $this->colEstimate, $this->estimateElementTotals[$this->currentElementId]);

        $currCol = $this->colEstimate;

        if(count($this->tenderers))
        {
            foreach($this->tenderers as $tenderer)
            {
                ++$currCol;

                $grandTotal = ($this->contractorElementTotals && array_key_exists($this->currentElementId, $this->contractorElementTotals) && array_key_exists($tenderer['id'], $this->contractorElementTotals[$this->currentElementId]) && $this->contractorElementTotals[$this->currentElementId][$tenderer['id']] != 0) ? $this->contractorElementTotals[$this->currentElementId][$tenderer['id']] : 0;

                $this->setValue( $currCol, $grandTotal);
            }
        }

        $this->activeSheet->getStyle( $this->colEstimate.$this->currentRow.":".$currCol.$this->currentRow )
            ->applyFromArray( $style );
    }

    public function startBillCounter()
    {
        $this->currentRow = $this->startRow;
        $this->firstCol = $this->colItem;

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

        $this->currentElementNo = 0;
        $this->columnSetting = null;
    }

    public function createHeader( $new = false )
    {
        $this->currentRow++;
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, self::COL_NAME_NO );
        $this->activeSheet->setCellValue( $this->colDescription.$row, self::COL_NAME_DESCRIPTION );
        $this->activeSheet->setCellValue( $this->colUnit.$row, self::COL_NAME_UNIT );
        $this->activeSheet->setCellValue( $this->colEstimate.$row, self::COL_NAME_ESTIMATE );

        if($this->printQty)
        {
            $this->activeSheet->setCellValue( $this->colQty.$row, self::COL_NAME_QTY );
            $this->activeSheet->getColumnDimension( $this->colQty )->setWidth( 13 );
        }

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

        //Set Column Sizing
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 9 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
        $this->activeSheet->getColumnDimension( $this->colUnit )->setWidth( 13 );
        $this->activeSheet->getColumnDimension( $this->colEstimate )->setWidth( 15 );
    }

    public function process( $pages, $lock = false, $header, $subTitle, $topLeftTitle, $withoutCents, $totalPage)
    {
        $this->setExcelParameter( $lock, $withoutCents );

        $this->totalPage = $totalPage;

        $description = '';
        $char = '';
        $prevItemType = '';
        $prevItemLevel = 0;

        $this->createSheet($header, $subTitle, $topLeftTitle);

        $pageNo = 1;

        foreach($pages as $key => $page)
        {
            $this->currentElementId = $key;

            for($i=1;$i<=$page['item_pages']->count(); $i++)
            {
                if($page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i))
                {
                    $printGrandTotal = (($i+1) == $page['item_pages']->count()) ? true : false;

                    $this->createNewPage($pageNo, false, 0);

                    $itemPage = $page['item_pages']->offsetGet($i);

                    $this->currentNotListedId = null;
                    $this->currentNotListedCount = 1;

                    foreach($itemPage as $item)
                    {
                        $itemType = $item[4];

                        switch($itemType)
                        {
                            case self::ROW_TYPE_BLANK:

                                if($description != '' && $prevItemType != '')
                                {
                                    if($prevItemType == BillItem::TYPE_HEADER_N || $prevItemType == BillItem::TYPE_HEADER)
                                    {
                                        $this->newItem();

                                        if (strpos($description,$this->printSettings['layoutSetting']['contdPrefix']) !== false)
                                        {
                                            $this->setItemHead( $description,  $prevItemType, $prevItemLevel );
                                        }
                                        else
                                        {
                                            $this->setItemHead( $description,  $prevItemType, $prevItemLevel, true );
                                        }
                                    }

                                    $description = '';
                                }
                            break;
                            case self::ROW_TYPE_ELEMENT:

                                if (strpos($item[2],$this->printSettings['layoutSetting']['contdPrefix']) !== false)
                                {
                                    $this->setElementTitle($this->printSettings['layoutSetting']['contdPrefix']);
                                }
                                else
                                {
                                    $this->setElement(array('description' => $item[2]));
                                }

                            break;
                            case BillItem::TYPE_HEADER_N:
                            case BillItem::TYPE_HEADER:

                                $description.=$item[2]."\n";
                                $prevItemType = $item[4];
                                $prevItemLevel = $item[3];

                            break;
                            case BillItem::TYPE_ITEM_NOT_LISTED:

                                $description.= str_replace(array('&nbsp;'),"\t\t",$item[2])."\n";

                                if($item[0])
                                {
                                    if($this->currentNotListedId != $item[0])
                                    {
                                        $this->currentNotListedId = $item[0];
                                        $this->currentNotListedCount = 1;
                                    }
                                    else
                                    {
                                        $this->currentNotListedId = $item[0];
                                        $this->currentNotListedCount+=1;
                                    }

                                    $this->newItem();

                                    $this->setItem( $description,  $itemType , $item[3]);

                                    $this->processItems($item);

                                    $description = '';
                                }

                            break;
                            default:

                                $description.=$item[2]."\n";
                                $char.=$item[1];

                                if($item[0])
                                {
                                    $this->newItem();

                                    $this->setItem( $description,  $itemType , $item[3], $char);

                                    $this->processItems($item);

                                    $description = '';
                                    $char = '';
                                }

                            break;
                        }

                    }

                    if($printGrandTotal)
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

    public function processItems($item)
    {
        parent::setUnit($item[ self::ROW_BILL_ITEM_UNIT ]);

        if($this->printQty)
        {
            if($item && is_array($item[7]))
            {
                $quantity = 0;

                foreach($this->billColumnSettings as $column)
                {
                    $qtyField = array_key_exists($column['id'], $item[7]) ? $item[7][$column['id']] : 0;
                    $quantity+= ($qtyField * $column['quantity']);
                }
            }
            else
            {
                $quantity = 0;
            }

            parent::setNormalQtyValue($this->colQty, $quantity);
        }

        if($item[4] == BillItem::TYPE_ITEM_NOT_LISTED)
        {
            parent::setValue( $this->colEstimate, $item[6][0]);
        }
        else
        {
            parent::setValue( $this->colEstimate, $item[6]);
        }

        $currCol = $this->colEstimate;

        if(count($this->tenderers))
        {
            $itemId = $item[0];
            $lowestTendererId  = null;
            $highestTendererId = null;
            $listOfRates       = array();

            foreach($this->tenderers as $k => $tenderer)
            {
                if(array_key_exists($tenderer['id'], $this->contractorRates) && array_key_exists($itemId, $this->contractorRates[$tenderer['id']]))
                {
                    $listOfRates[$tenderer['id']] = $this->contractorRates[$tenderer['id']][$itemId];
                }
            }

            $lowestRate  = count($listOfRates) ? min($listOfRates) : 0;
            $highestRate = count($listOfRates) ? max($listOfRates) : 0;

            $lowestTendererId  = array_search($lowestRate, $listOfRates);
            $highestTendererId = array_search($highestRate, $listOfRates);

            $counter = 1;

            foreach($this->tenderers as $tenderer)
            {
                ++$currCol;

                if($item[4] == BillItem::TYPE_ITEM_NOT_LISTED)
                {
                    $contractorValue = $item[6][$counter];

                    parent::setValue( $currCol, $contractorValue);
                }
                else
                {
                    $value = isset($this->contractorRates[$tenderer['id']][$item[0]]) ? $this->contractorRates[$tenderer['id']][$item[0]] : null;

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

                $counter++;
            }
        }
    }
}