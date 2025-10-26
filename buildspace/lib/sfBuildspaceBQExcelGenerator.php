<?php
class sfBuildspaceBQExcelGenerator extends sfBuildspaceBQPageGenerator
{
    protected $itemCount = 0;

    protected $columnSettings;

    public $startRow = 0;
    public $newLineGap = 1;
    public $startChar = "A";
    public $currentElement = array();
    public $currentElementNo = 0;
    public $currentChar = 'A';
    public $currentPage = 0;
    public $currentPageHeader = array();
    public $currentRow = 0;
    public $currentItemType;

    protected $firstCol;
    protected $lastCol;
    protected $colItem;
    protected $colDescription;
    protected $colQty;
    protected $colUnit;
    protected $colRate;
    protected $colAmount;

    protected $colRowLumpSumPercent;
    protected $colRowType;
    protected $colItemType;
    protected $colLevel;
    protected $colLeft;
    protected $colRight;
    protected $titleColumnRange;

    protected $withRate = false;
    protected $withQuantity = false;
    protected $quantitiesColumnRange = array();
    protected $lock = true;

    protected $colIdToDimensionArray;

    protected $elementDescription;

    const ROW_TYPE_ELEMENT_TEXT = "Element";
    const ROW_TYPE_ITEM_TEXT = "Item";

    const EXCEL_PROPERTIES_TITLE = "TITLE";
    const EXCEL_PROPERTIES_DESCRIPTION = "DESCRIPTION";
    const EXCEL_PROPERTIES_PROJECT_STRUCTURE_TYPE = "PROJECT_STRUCTURE_TYPE";
    const EXCEL_PROPERTIES_BILL_TYPE = "BILL_TYPE";
    const EXCEL_PROPERTIES_BUILD_UP_QTY_ROUNDING_TYPE = "BUILD_UP_QTY_ROUNDING_TYPE";
    const EXCEL_PROPERTIES_BUILD_UP_RATE_ROUNDING_TYPE = "BUILD_UP_RATE_ROUNDING_TYPE";
    const EXCEL_PROPERTIES_UNIT_TYPE = "UNIT_TYPE";
    const EXCEL_PROPERTIES_ELEMENT_MARKUP_ENABLED = "ELEMENT_MARKUP_ENABLED";
    const EXCEL_PROPERTIES_ITEM_MARKUP_ENABLED = "ITEM_MARKUP_ENABLED";
    const EXCEL_PROPERTIES_MARKUP_ROUNDING_TYPE = "MARKUP_ROUNDING_TYPE";
    const EXCEL_PROPERTIES_BILL_COLUMN_SETTING = "BILL_COLUMN_SETTING";
    const EXCEL_PROPERTIES_BILL_COLUMN_SETTING_QTY = "BILL_COLUMN_SETTING-QTY";

    public function __construct(ProjectStructure $bill)
    {
        $this->objPHPExcel = new sfPhpExcel();

        $this->project = $bill->getRoot();

        $this->bill = $bill;
        $this->columnSettings = $bill->getBillColumnSettings()->toArray();

        $this->setGlobalStyling(); //Set Bill Default Global Style
    }

    protected function setExcelParameter( $lock = false, $withRate = false, $withQuantity = false ) 
    {
        $this->lock = ( $lock ) ? $lock : $this->lock;
        $this->withRate = ($withRate) ? $withRate : $this->withRate;
        $this->withQuantity = ($withQuantity) ? $withQuantity : $this->withQuantity;
    }

    public function protectSheet() 
    {
        $this->objPHPExcel->getActiveSheet()->getProtection()->setSheet( $this->lock );
        $this->objPHPExcel->getActiveSheet()->getProtection()->setPassword("Buildspace");
    }

    public function setupBill()
    {
        $lastColumn = 5 + (count($this->columnSettings) * 2);

        if(count($this->columnSettings) > 1)
        {
            $lastColumn += 2;//for multitype columns we add 2 additional columns for total qty and amount
        }

        $this->colRowLumpSumPercent = Utilities::generateCharFromNumber($lastColumn + 1, true);
        $this->colRowType = Utilities::generateCharFromNumber($lastColumn + 2, true);
        $this->colItemType = Utilities::generateCharFromNumber($lastColumn + 3, true);
        $this->colLeft = Utilities::generateCharFromNumber($lastColumn + 4, true);
        $this->colRight = Utilities::generateCharFromNumber($lastColumn + 5, true);
        $this->colLevel = Utilities::generateCharFromNumber($lastColumn + 6, true);

        $this->startBillCounter();

        $this->setBillProperties();

        $this->setBillColumnSettingProperties();
    }

    protected function startBillCounter()
    {
        $this->colRate = count($this->columnSettings) > 1 ? 5 : 6;

        $this->firstCol = $this->colItem = Utilities::generateCharFromNumber(2, true);
        $this->colDescription = Utilities::generateCharFromNumber(3, true);
        $this->colUnit = Utilities::generateCharFromNumber(4, true);

        if(count($this->columnSettings) == 1)
        {
            $this->colQty = Utilities::generateCharFromNumber(5, true);
            $this->colAmount = Utilities::generateCharFromNumber(7, true);
        }

        //each column setting has qty and rate and at the end we need to add 2 more columns for total qty and total amount (multitype columns)
        $lastColNumber = count($this->columnSettings) > 1 ? $this->colRate + (count($this->columnSettings) * 2) + 2 : $this->colRate + 1;
        $this->lastCol = Utilities::generateCharFromNumber($lastColNumber, true);

        $this->currentElementNo = 0;
    }

    protected function setBillProperties()
    {
        $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_TITLE, $this->bill->BillSetting->title);
        $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_DESCRIPTION, $this->bill->BillSetting->description);
        $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_PROJECT_STRUCTURE_TYPE, (int)$this->bill->type);
        $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_BILL_TYPE, (int)$this->bill->BillType->type);
        $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_BUILD_UP_QTY_ROUNDING_TYPE, (int)$this->bill->BillSetting->build_up_quantity_rounding_type);
        $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_BUILD_UP_RATE_ROUNDING_TYPE, (int)$this->bill->BillSetting->build_up_rate_rounding_type);
        $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_UNIT_TYPE, (int)$this->bill->BillSetting->unit_type);
        $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_ELEMENT_MARKUP_ENABLED, $this->bill->BillMarkupSetting->element_markup_enabled);
        $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_ITEM_MARKUP_ENABLED, $this->bill->BillMarkupSetting->item_markup_enabled);
        $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_MARKUP_ROUNDING_TYPE, (int)$this->bill->BillMarkupSetting->rounding_type);
    }

    private function setBillColumnSettingProperties()
    {
        foreach($this->columnSettings as $idx => $column)
        {
            $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_BILL_COLUMN_SETTING."_".$idx, $column['name']);
            $this->objPHPExcel->getProperties()->setCustomProperty(self::EXCEL_PROPERTIES_BILL_COLUMN_SETTING_QTY."_".$idx, $column['quantity']);
        }
    }

    public function setBillHeader($billHeader)
    {
        //Set Bill Header Here
        $this->objPHPExcel->getActiveSheet()->setCellValue( $this->colItem."1", $billHeader ); // Set Bill Title
    }

    public function startElement(Array $element)
    {
        $this->currentElement = $element;
        $this->currentPage = 1;
        $this->currentPageHeader = array();
        $this->currentElementNo++;
    }

    public function hideColumn() 
    {
        //hide Columns
        if ( $this->colRowLumpSumPercent )
        {
            $this->objPHPExcel->getActiveSheet()->getColumnDimension( $this->colRowLumpSumPercent )->setVisible( false );
        }

        $this->objPHPExcel->getActiveSheet()->getColumnDimension( $this->colRowType )->setVisible( false );
        $this->objPHPExcel->getActiveSheet()->getColumnDimension( $this->colItemType )->setVisible( false );
        $this->objPHPExcel->getActiveSheet()->getColumnDimension( $this->colLeft )->setVisible( false );
        $this->objPHPExcel->getActiveSheet()->getColumnDimension( $this->colRight )->setVisible( false );
        $this->objPHPExcel->getActiveSheet()->getColumnDimension( $this->colLevel )->setVisible( false );
    }

    /*
        Generate Default Header Setting and Call Single or Multitype header
        based on number of columnSettings
    */
    public function createNewPage( $pageNo = null ) {
        if ( !$pageNo )
            return;

        //create Footer
        $this->createFooter();

        //create new header
        $this->createHeader( true );

        //Update Current Page Counter
        $this->currentPage = $pageNo;

        $this->newLine();
    }

    public function createHeader( $new = false ) {
        $row = $this->currentRow;

        //set default column
        $this->objPHPExcel->getActiveSheet()->setCellValue( $this->colItem.$row, "Item" );
        $this->objPHPExcel->getActiveSheet()->setCellValue( $this->colDescription.$row, $this->printSettings['phrase']['descHeader'] );
        $this->objPHPExcel->getActiveSheet()->setCellValue( $this->colUnit.$row, $this->printSettings['phrase']['unitHeader'] );

        //reset Character Counter
        $this->currentChar = 'A';

        if ( ( $new == TRUE && count( $this->columnSettings ) == 1 ) ) {
            $this->excelType = self::EXCEL_TYPE_SINGLE;
            $this->createSingleTypeHeader();
        }else {
            $this->excelType = self::EXCEL_TYPE_MULTIPLE;
            $this->createMultiTypeHeader();
        }

        //Set Column Width
        $this->objPHPExcel->getActiveSheet()->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->objPHPExcel->getActiveSheet()->getColumnDimension( $this->colItem )->setWidth( 6 );
        $this->objPHPExcel->getActiveSheet()->getColumnDimension( $this->colDescription )->setWidth( 45 );
        $this->objPHPExcel->getActiveSheet()->getColumnDimension( $this->colUnit )->setWidth( 6 );
        $this->objPHPExcel->getActiveSheet()->getColumnDimension( Utilities::generateCharFromNumber($this->colRate, true) )->setWidth( 8 );
        $this->objPHPExcel->getActiveSheet()->getStyle( $this->firstCol.'1:'.$this->lastCol.'1' )->applyFromArray( $this->getProjectTitleStyle() );
        $this->objPHPExcel->getActiveSheet()->mergeCells($this->firstCol.'1:'.$this->lastCol.'1');
        $this->currentRow++;
    }

    /*
        Generate single type column Header
    */
    public function createSingleTypeHeader() {
        $row = $this->currentRow;

        $this->objPHPExcel->getActiveSheet()->setCellValue( $this->colQty.$row, $this->printSettings['phrase']['qtyHeader'] );
        $this->objPHPExcel->getActiveSheet()->setCellValue( $this->colAmount.$row, $this->printSettings['phrase']['amtHeader'] );
        $this->objPHPExcel->getActiveSheet()->setCellValue( Utilities::generateCharFromNumber($this->colRate, true).$row, $this->printSettings['phrase']['rateHeader'] );
        $this->objPHPExcel->getActiveSheet()->getStyle( $this->colItem.$row.':'.$this->colAmount.$row )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getColumnDimension( $this->colQty )->setWidth( 8 );
        $this->objPHPExcel->getActiveSheet()->getColumnDimension( $this->colAmount )->setWidth( 8 );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
    }

    /* Generate Multitype Column Header */

    public function createMultiTypeHeader() {
        $row = $this->currentRow;
        $currentCol = $this->colRate;

        $this->objPHPExcel->getActiveSheet()->setCellValue( Utilities::generateCharFromNumber($this->colRate, true).$row, $this->printSettings['phrase']['rateHeader'] );

        $this->colIdToDimensionArray = array();

        $currentCol++;

        $subColumns = array(
            $this->printSettings['phrase']['qtyHeader'],
            'Amount'
        );

        foreach($this->columnSettings as $column)
        {
            $this->objPHPExcel->getActiveSheet()->setCellValue( Utilities::generateCharFromNumber($currentCol, true).$row, $column['name'] );

            $this->objPHPExcel->getActiveSheet()->mergeCells( Utilities::generateCharFromNumber($currentCol, true).$row.':'.Utilities::generateCharFromNumber($currentCol+1, true).$row );

            $this->colIdToDimensionArray[$column['id']] = $currentCol;

            foreach($subColumns as $idx => $subColumn)
            {
                $currentCol += $idx;
                $this->objPHPExcel->getActiveSheet()->setCellValue( Utilities::generateCharFromNumber($currentCol, true).( $row+1 ), $subColumn );
            }

            $currentCol++;
        }

        //total amount & qty columns at the end of
        $this->objPHPExcel->getActiveSheet()->setCellValue( Utilities::generateCharFromNumber($currentCol, true).$row, 'Total' );

        $this->objPHPExcel->getActiveSheet()->mergeCells( Utilities::generateCharFromNumber($currentCol, true).$row.':'.Utilities::generateCharFromNumber($currentCol+1, true).$row );

        foreach($subColumns as $idx => $subColumn)
        {
            $currentCol += $idx;
            $this->objPHPExcel->getActiveSheet()->setCellValue( Utilities::generateCharFromNumber($currentCol, true).( $row+1 ), $subColumn );
        }

        $this->currentRow++;

        //Merge other Column
        $this->objPHPExcel->getActiveSheet()->mergeCells( $this->colItem.$row.':'.$this->colItem.( $row+1 ) );
        $this->objPHPExcel->getActiveSheet()->mergeCells( $this->colDescription.$row.':'.$this->colDescription.( $row+1 ) );
        $this->objPHPExcel->getActiveSheet()->mergeCells( $this->colUnit.$row.':'.$this->colUnit.( $row+1 ) );
        $this->objPHPExcel->getActiveSheet()->mergeCells( Utilities::generateCharFromNumber($this->colRate, true).$row.':'.Utilities::generateCharFromNumber($this->colRate, true).( $row+1 ) );

        $this->objPHPExcel->getActiveSheet()->getStyle( $this->firstCol.$row.':'.$this->lastCol.( $row+1 ) )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation( PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
    }

    public function createFooter()
    {
        $this->newLine( true );

        if ( $this->currentPage >= 1 )
        {
            //Set Page Break if Page Bigger than 0
            $this->objPHPExcel->getActiveSheet()->setBreak( $this->colDescription.$this->currentRow , PHPExcel_Worksheet::BREAK_ROW );

            $this->createFooterPageNo();
        }

        $this->currentRow+=2;
    }

    public function createFooterPageNo()
    {
        $coord = $this->colDescription.$this->currentRow;
        $this->currentRow++;
        $text = $this->printSettings['layoutSetting']['pageNoPrefix'].' '.$this->currentElementNo.'/'.$this->currentPage;

        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, $text );
        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray(array(
            'font' => array(
                'bold' => true
            )
        ));
    }

    public function newLine( $bottom = false ) {
        $newLineStyle = array(
            'borders' => array(
                'vertical' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array( 'argb' => 'FFFFFF' ),
                ),
                'bottom' => array(
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

        $this->objPHPExcel->getActiveSheet()
            ->getStyle( $this->firstCol.$this->currentRow.":".$this->lastCol.$this->currentRow )
            ->applyFromArray( $newLineStyle );

        $this->currentRow++;
    }

    public function setElementDescription(SplFixedArray $element)
    {
        if (!$element->offsetExists(self::ROW_BILL_ITEM_DESCRIPTION) )
            return;

        $description = str_replace("&nbsp;", ' ', trim($element[self::ROW_BILL_ITEM_DESCRIPTION]));

        $this->elementDescription .= $description." ";
        
        $coord = $this->colDescription.$this->currentRow;

        $rowTypeCoord = $this->colRowType.$this->currentRow;

        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $this->getElementTitleStyle() );

        //set row type element if element is new
        //this will ignore the continuos element
        $this->objPHPExcel->getActiveSheet()->setCellValue( $rowTypeCoord, self::ROW_TYPE_ELEMENT_TEXT );

        $this->lockCell( $coord );

        if($element[self::ROW_BILL_ITEM_ROW_IDX] == self::ELEMENT_DESC_LAST_ROW)
        {
            $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, preg_replace('!\s+!', ' ', trim($this->elementDescription))."\n");

            $this->elementDescription = "";
            $this->newLine();
        }
    }

    public function newItem() 
    {
        $this->newLine();
        //Increase Item Counter
        $this->itemCount++;
    }

    public function setItemStyle($itemType)
    {
        $coord = $this->colDescription.$this->currentRow;

        //Get And Set Item Style
        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $this->getDescriptionStyle($itemType) );
    }

    public function setItem( $description , $itemType, $itemLeft, $itemRight, $itemLvl )
    {
        $coord = $this->colDescription.$this->currentRow;

        $rowTypeCoord = $this->colRowType.$this->currentRow;

        $itemTypeCoord = $this->colItemType.$this->currentRow;

        $leftCoord = $this->colLeft.$this->currentRow;

        $rightCoord = $this->colRight.$this->currentRow;

        $levelCoord = $this->colLevel.$this->currentRow;
        
        if($itemType == BillItem::TYPE_ITEM_HTML_EDITOR or $itemType == BillItem::TYPE_NOID)
        {
            $wizard = new PHPExcel_Helper_HTML;
            $description = $wizard->toRichTextObject($description);
        }
        else
        {
            $description = str_replace("&nbsp;", ' ', $description);
            $description = preg_replace('!\s+!', ' ', trim($description))."\n";
        }
        
        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, $description );

        $this->setItemStyle($itemType);

        $this->objPHPExcel->getActiveSheet()->setCellValue( $rowTypeCoord, self::ROW_TYPE_ITEM_TEXT );

        $this->objPHPExcel->getActiveSheet()->setCellValue( $itemTypeCoord, $itemType);

        $this->objPHPExcel->getActiveSheet()->setCellValue( $leftCoord, $itemLeft );

        $this->objPHPExcel->getActiveSheet()->setCellValue( $rightCoord, $itemRight );

        $this->objPHPExcel->getActiveSheet()->setCellValue( $levelCoord, $itemLvl );

    }

    public function getPageInfoFromBillReference( $billReferences = array() )
    {
        if ( !( count( $billReferences ) > 0 ) )
            return null;

        $pageInfo = array();

        foreach ( $billReferences as $reference )
        {
            $pageInfo['reference_num'] = $reference['reference_num'];
            $pageInfo['page_no'] = $reference['BillPage']['page_no'];
            $pageInfo['element_no'] = $reference['BillPage']['element_no'];
            $pageInfo['bill_ref'] = $reference['bill_ref'];
            $pageInfo['bill_item_id'] = $reference['bill_item_id'];
        }

        return $pageInfo;
    }

    public function getHeaderStartPage( $billReferences = array() )
    {
        if ( !( count( $billReferences ) > 0 ) )
            return null;

        $pageInfo = array();

        $pageInfo['reference_num'] = $billReferences[0]['reference_num'];
        $pageInfo['page_no'] = $billReferences[0]['BillPage']['page_no'];
        $pageInfo['element_no'] = $billReferences[0]['BillPage']['element_no'];
        $pageInfo['bill_ref'] = $billReferences[0]['bill_ref'];
        $pageInfo['bill_item_id'] = $billReferences[0]['bill_item_id'];

        return $pageInfo;
    }

    public function setSubRow( $description, $unit = false, $rate = false, $padding = false )
    {
        $this->newLine();

        $coord = $this->colDescription.$this->currentRow;
        $rateCoord = Utilities::generateCharFromNumber($this->colRate, true).$this->currentRow;

        if($rate && $rate !=0)
        {
            $this->objPHPExcel->getActiveSheet()->setCellValue( $rateCoord, $rate );
        }

        if($unit)
        {
            $this->setUnit( $unit );
        }

        if ( $padding )
            $description.="\n";

        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, $description );
        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $this->getSubDescriptionStyle() );
        $this->objPHPExcel->getActiveSheet()->getStyle($rateCoord)->getNumberFormat()->setFormatCode("#,##0.00");

        $this->lockCell( $coord );
        
    }

    public function setUnit( $unit ) 
    {
        $coord = $this->colUnit.$this->currentRow;

        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, $unit );
        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $this->getUnitStyle() );
        $this->objPHPExcel->getActiveSheet()->getStyle($coord)->getAlignment()->setWrapText(false);

        $this->lockCell( $coord );
    }

    public function setChar( $char ) 
    {
        $coord = $this->colItem.$this->currentRow;

        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, $char );
        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $this->getUnitStyle() );

        $this->lockCell( $coord );
    }

    public function setQty( $qty ) 
    {
        $coord = $this->colQty.$this->currentRow;

        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, $qty );
        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $this->getQtyStyle() );

        $this->unlockCell($coord);
    }

    public function setRate( $rate )
    {
        $coord = Utilities::generateCharFromNumber($this->colRate, true).$this->currentRow;

        $this->objPHPExcel->getActiveSheet()->getStyle($coord)->getNumberFormat()->setFormatCode("#,##0.00");
        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, $rate );
        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $this->getQtyStyle() );
        $this->objPHPExcel->getActiveSheet()->getColumnDimension(Utilities::generateCharFromNumber($this->colRate, true))->setAutoSize(true);
        $this->objPHPExcel->getActiveSheet()->getStyle($coord)->getAlignment()->setWrapText(false);
    }

    public function setLumpSumPercentInfo($rate, $percentValue)
    {
        $coord = $this->colRowLumpSumPercent.$this->currentRow;

        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, '='.$rate.'*('.$percentValue.'/100)' );
    }

    public function setQuantity(Array $quantities, Array $includes, $overridingValue = null )
    {
        $style = $this->getQtyStyle();

        switch ( $this->excelType ) 
        {
            case self::EXCEL_TYPE_SINGLE:
                $coord = $this->colQty.$this->currentRow;

                $qty = 0;

                if ( $overridingValue )
                {
                    $qty = $overridingValue;
                }
                else
                {
                    foreach($quantities as $id => $quantity)
                    {
                        $quantity = is_null($quantity) ? 0 : $quantity;

                        $qty = (isset($includes[$id]) and !$includes[$id]) ? NULL : $quantity;
                    }
                }

                $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, $qty );
                $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $style );

                break;
            case self::EXCEL_TYPE_MULTIPLE:

                if ( $overridingValue )
                {
                    foreach($quantities as $id => $quantity)
                    {
                        $coord = Utilities::generateCharFromNumber($this->colIdToDimensionArray[$id], true).$this->currentRow;
                        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, "Rate-Only" );
                        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $style );
                    }
                }
                else
                {
                    $singleQtyCoords = array();

                    $count = 0;
                    foreach($quantities as $id => $quantity)
                    {
                        $coord = Utilities::generateCharFromNumber($this->colIdToDimensionArray[$id], true).$this->currentRow;

                        $quantity = is_null($quantity) ? 0 : $quantity;

                        if ( $overridingValue )
                        {
                            $qty = $overridingValue;
                        }
                        else
                        {
                            $qty = (isset($includes[$id]) and !$includes[$id]) ? NULL : $quantity;
                        }

                        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, $qty );
                        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $style );

                        if(!is_null($qty))
                        {
                            $singleQtyCoords[$count] = $coord;
                            $count++;
                        }
                    }

                    $lastColNumber = count($this->columnSettings) > 1 ? $this->colRate + (count($this->columnSettings) * 2) + 2 : $this->colRate + 1;

                    $this->objPHPExcel->getActiveSheet()->setCellValue(Utilities::generateCharFromNumber($lastColNumber - 1, true).$this->currentRow, '=SUM('. implode(", ", $singleQtyCoords) .')' );
                    $this->objPHPExcel->getActiveSheet()->getStyle(Utilities::generateCharFromNumber($lastColNumber - 1, true).$this->currentRow )->applyFromArray( $style );
                }

                break;
            default:
                break;
        }
    }

    public function setItemHead( $description , $itemType, $itemLeft, $itemRight, $itemLvl, $new = false)
    {
        $coord = $this->colDescription.$this->currentRow;

        $rowTypeCoord = $this->colRowType.$this->currentRow;

        $itemTypeCoord = $this->colItemType.$this->currentRow;

        $leftCoord = $this->colLeft.$this->currentRow;

        $rightCoord = $this->colRight.$this->currentRow;

        $levelCoord = $this->colLevel.$this->currentRow;

        $style = $this->getDescriptionStyle( BillItem::TYPE_HEADER_TEXT );

        $description = str_replace("&nbsp;", ' ', $description);

        $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, preg_replace('!\s+!', ' ', trim($description))."\n");

        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->applyFromArray( $style );

        if($new)
        {
            $this->objPHPExcel->getActiveSheet()->setCellValue( $rowTypeCoord, self::ROW_TYPE_ITEM_TEXT );
            $this->objPHPExcel->getActiveSheet()->setCellValue( $itemTypeCoord, $itemType );
            $this->objPHPExcel->getActiveSheet()->setCellValue( $leftCoord, $itemLeft );
            $this->objPHPExcel->getActiveSheet()->setCellValue( $rightCoord, $itemRight );
            $this->objPHPExcel->getActiveSheet()->setCellValue( $levelCoord, $itemLvl );
        }

        $this->lockCell( $coord );
    }

    public function setAmount( $overridingValue  = false )
    {
        $rateCoord = Utilities::generateCharFromNumber($this->colRate, true).$this->currentRow;
        $coord = null;
        $style = $this->getQtyStyle();

        switch ( $this->excelType ) 
        {
            case self::EXCEL_TYPE_SINGLE:
                $coord = $this->colAmount.$this->currentRow;
                $qtyCoord = $this->colQty.$this->currentRow;

                if($overridingValue)
                {
                    $formula = $overridingValue;
                }
                else
                {
                    //Set Formula
                    $formula = '=ROUND('.$qtyCoord.'*'.$rateCoord.',2)';
                }

                $this->objPHPExcel->getActiveSheet()->setCellValue( $coord, $formula );
                $this->objPHPExcel->getActiveSheet()->getStyle($coord)->getNumberFormat()->setFormatCode("#,##0.00");
                $this->objPHPExcel->getActiveSheet()->getColumnDimension($this->colQty)->setAutoSize(true);
                $this->objPHPExcel->getActiveSheet()->getColumnDimension($this->colAmount)->setAutoSize(true);
                $this->objPHPExcel->getActiveSheet()->getStyle($qtyCoord)->getAlignment()->setWrapText(false);
                $this->objPHPExcel->getActiveSheet()->getStyle($coord)->getAlignment()->setWrapText(false);

                break;
            case self::EXCEL_TYPE_MULTIPLE:
                $coord = $this->lastCol.$this->currentRow;

                $singleAmountCoords = new SplFixedArray(count($this->columnSettings));

                foreach($this->columnSettings as $idx => $column)
                {
                    //Get Column Dimension
                    $colDimension = $this->colIdToDimensionArray[$column['id']];
                    $qtyCoord = Utilities::generateCharFromNumber($colDimension, true).$this->currentRow;
                    $amountCoord =  Utilities::generateCharFromNumber($colDimension+1, true).$this->currentRow;

                    $formula = $overridingValue ? $overridingValue : '=ROUND('.$qtyCoord.'*'.$rateCoord.',2)';

                    $this->objPHPExcel->getActiveSheet()->setCellValue( $amountCoord, $formula );

                    $this->objPHPExcel->getActiveSheet()->getStyle($amountCoord)->getNumberFormat()->setFormatCode("#,##0.00");

                    $this->objPHPExcel->getActiveSheet()->getStyle( $amountCoord )->applyFromArray( $style );

                    $this->objPHPExcel->getActiveSheet()->getColumnDimension(Utilities::generateCharFromNumber($colDimension+1, true))->setAutoSize(true);
                    $this->objPHPExcel->getActiveSheet()->getStyle($amountCoord)->getAlignment()->setWrapText(false);

                    $singleAmountCoords[$idx] = $amountCoord;
                }

                $this->objPHPExcel->getActiveSheet()->setCellValue( $this->lastCol.$this->currentRow, '=SUM('. implode(", ", $singleAmountCoords->toArray()) .')' );
                $this->objPHPExcel->getActiveSheet()->getStyle($this->lastCol.$this->currentRow)->getNumberFormat()->setFormatCode("#,##0.00");
                $this->objPHPExcel->getActiveSheet()->getColumnDimension($this->lastCol)->setAutoSize(true);
                $this->objPHPExcel->getActiveSheet()->getStyle($this->lastCol.$this->currentRow)->getAlignment()->setWrapText(false);
                break;
        }
    }

    public function setGlobalStyling()
    {
        //Set Global Styling Styling
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName( 'Arial' );
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize( 10 );
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText( true );
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_CENTER );

        $pageMargins = $this->objPHPExcel->getActiveSheet()->getPageMargins();
        $pageMargins->setTop(0.04);
        $pageMargins->setBottom(0.04);
    }

    public function getColumnHeaderStyle()
    {
        return array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                ),
            ),
            'font' => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );
    }

    public function getElementTitleStyle() {
        return array('font' => array(
            'bold' => true,
            'underline' => true
        ));
    }

    public function getUnitStyle()
    {
        return array( 'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
        ));
    }


    public function getRateStyle()
    {
        return array('alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
        ));
    }

    public function getQtyStyle()
    {
        return array('alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
        ));
    }

    public function getDescriptionStyle( $billType )
    {
        switch ( $billType )
        {
            case BillItem::TYPE_HEADER_N_TEXT:
            case BillItem::TYPE_HEADER_TEXT:
                $descriptionStyle = array(
                    'font' => array(
                        'bold' => true,
                        'underline' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'wrapText' => true
                    )
                );
                break;
            default:
                $descriptionStyle = array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY, //PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'wrapText' => true
                    )
                );
                break;
        }

        return $descriptionStyle;
    }

    public function getSubDescriptionStyle()
    {
        return array(
            'font' => array(
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'wrapText' => true,
                'indent' => 1
            )
        );
    }

    public function getProjectTitleStyle()
    {
        return array(
            'font' => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        );
    }

    public function lockCell( $coord )
    {
        $this->objPHPExcel->getActiveSheet()->protectCells( $coord, 'PHPExcel' );
    }

    public function unlockCell( $coord )
    {
        $this->objPHPExcel->getActiveSheet()->getStyle( $coord )->getProtection()->setLocked( PHPExcel_Style_Protection::PROTECTION_UNPROTECTED );
    }

    public function write($writerType)
    {
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, $writerType);
        $tmpName = md5(date('dmYHis'));
        $tmpFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.$tmpName;

        $objWriter->save($tmpFile);

        unset($this->objPHPExcel);

        return $tmpFile;
    }
}
?>
