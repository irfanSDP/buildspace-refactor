<?php

class sfBuildspaceRFQExcelGenerator extends sfBuildspaceBQMasterFunction {

    public $filename              = "New File";
    public $savePath;
    public $project;
    public $bill;
    public $totalItems            = 0;
    public $itemCount             = 0;
    public $excelType;
    public $lock                  = true;
    public $billId                = 0;
    public $columnSetting;

    public $startRow              = 0;
    public $newLineGap            = 1;
    public $startChar             = "A";
    public $currentElement        = array();
    public $currentElementNo      = 0;
    public $currentChar           = 'A';
    public $currentPage           = 0;
    public $currentPageHeader     = array();
    public $currentRow            = 0;
    public $currentItemType;

    public $colItem               = "B";
    public $colDescription        = "C";
    public $colQty                = "E";
    public $colUnit               = "D";
    public $colRate               = "F";
    public $colAmount             = "G";
    public $colRowType            = 'M';
    public $colItemType           = 'N';
    public $colLevel              = 'O';
    public $colRfqItemId          = 'P';
    public $titleColumnRange;

    // coordinates so store RFQ and Supplier's ID
    public $colImportMetaInfo     = 'I';
    public $colRFQId              = 'I1';
    public $colRFQType            = 'I2';
    public $colRFQSupplierId      = 'I10';

    public $withRate              = false;
    public $withQuantity          = false;
    public $quantitiesColumnRange = array();

    protected $editableCellColor  = 'FFFF00';

    const ROW_TYPE_ELEMENT_TEXT   = "Element";
    const ROW_TYPE_ITEM_TEXT      = "Item";

    public function __construct( $rfq = null, $savePath = null, $filename )
    {
        $this->objPHPExcel = new sfPhpExcel();
        $this->getActiveSheet(); //Set to current Active Sheet

        //Set Project & Path Information
        $this->rfq         = $rfq;
        $this->savePath    = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads';
        $this->filename    = ( $filename ) ? $filename : $this->filename;

        $this->setGlobalStyling(); //Set Bill Default Global Style
        $this->setTitle(); //Set Bill Meta title
    }

    public function setExcelParameter( $lock = false, $withRate = false, $withQuantity = false )
    {
        $this->lock         = $lock;
        $this->withRate     = ($withRate) ? $withRate : $this->withRate;
        $this->withQuantity = ($withQuantity) ? $withQuantity : $this->withQuantity;
    }

    public function protectSheet()
    {
        $this->activeSheet->getProtection()->setSheet( $this->lock );
        $this->activeSheet->getProtection()->setPassword("Buildspace");
    }

    public function createBill($rfqReferenceNo)
    {
        $this->setActiveSheet();

        $this->startBillCounter();

        $this->setBillHeader($rfqReferenceNo);

        $this->protectSheet();

        $this->hideColumn();
    }

    public function setBillHeader($rfqReferenceNo)
    {
        self::setSenderInformation();

        self::setRequestDateAndRFQReferenceNo($rfqReferenceNo);

        self::setSupplierInformation();

        self::setItemSendingLocation();

        self::setNotesInput();

        self::setHiddenRFQId();

        self::setHiddenRFQType();

        self::setHiddenRFQSupplierId();

        $this->currentRow = $this->currentRow + $this->tableStartingCoordinates;
    }

    public function setSenderInformation()
    {
        $regionInfo  = "{$this->currentCompany->Regions->country}, {$this->currentCompany->Subregions->name}, {$this->currentCompany->zipcode}";
        $phoneNumber = ($this->currentCompany->phone_number) ? $this->currentCompany->phone_number : '-';
        $faxNumber   = ($this->currentCompany->fax_number) ? $this->currentCompany->fax_number : '-';

        $this->activeSheet->setCellValue( $this->colItem."1", $this->currentUser->name );
        $this->activeSheet->getStyle( $this->colItem."1" )->applyFromArray( $this->getAddressComposerNameStyling() );
        $this->activeSheet->mergeCells('B1:G1');

        $this->activeSheet->setCellValue( $this->colItem."2", $this->currentCompany->name );
        $this->activeSheet->getStyle( $this->colItem."2" )->applyFromArray( $this->getAddressStyling() );
        $this->activeSheet->mergeCells('B2:G2');

        $this->activeSheet->setCellValue( $this->colItem."3", $this->currentCompany->address );
        $this->activeSheet->getStyle( $this->colItem."3" )->applyFromArray( $this->getAddressStyling() );
        $this->activeSheet->mergeCells('B3:G3');

        $this->activeSheet->setCellValue( $this->colItem."4", $regionInfo );
        $this->activeSheet->getStyle( $this->colItem."4" )->applyFromArray( $this->getAddressStyling() );
        $this->activeSheet->mergeCells('B4:G4');

        $this->activeSheet->setCellValue( $this->colItem."5", "Contact Number: {$phoneNumber}");
        $this->activeSheet->getStyle( $this->colItem."5" )->applyFromArray( $this->getAddressStyling() );
        $this->activeSheet->mergeCells('B5:G5');

        $this->activeSheet->setCellValue( $this->colItem."6", "Fax Number: {$faxNumber}" );
        $this->activeSheet->getStyle( $this->colItem."6" )->applyFromArray( $this->getAddressStyling() );
        $this->activeSheet->mergeCells('B6:G6');
    }

    public function setRequestDateAndRFQReferenceNo($rfqReferenceNo)
    {
        $this->activeSheet->setCellValue( $this->colItem."8", "DATE: ".date('d/m/y') );
        $this->activeSheet->getStyle( $this->colItem."8" )->applyFromArray( $this->getAddressComposerNameStyling() );
        $this->activeSheet->mergeCells('B8:C8');

        $this->activeSheet->setCellValue( $this->colUnit."8", "RFQ Reference No: ".$rfqReferenceNo );
        $this->activeSheet->mergeCells('D8:G8');
    }

    public function setSupplierInformation()
    {
        $supplierCompanyInfo = $this->rfqSupplier->getCompany();

        $this->activeSheet->setCellValue( $this->colItem."10", "ATTN: ".$supplierCompanyInfo->contact_person_name );
        $this->activeSheet->getStyle( $this->colItem."10" )->applyFromArray( $this->getAddressStyling() );
        $this->activeSheet->mergeCells('B10:G10');

        $this->activeSheet->setCellValue( $this->colItem."11", "TO: ".$supplierCompanyInfo->name );
        $this->activeSheet->getStyle( $this->colItem."11" )->applyFromArray( $this->getAddressStyling() );
        $this->activeSheet->mergeCells('B11:G11');
    }

    public function setItemSendingLocation()
    {
        // if type project then include site address
        if ( $this->rfq->type == RFQ::TYPE_PROJECT )
        {
            $this->activeSheet->setCellValue( $this->colItem."13", "Deliver To: ".$this->rfq->Project->MainInformation->site_address );
            $this->activeSheet->getStyle( $this->colItem."13" )->applyFromArray( $this->getAddressStyling() );
            $this->activeSheet->mergeCells('B13:G13');

            $this->activeSheet->setCellValue( $this->colItem."14", "Country: ".$this->rfq->Project->MainInformation->Regions->country );
            $this->activeSheet->getStyle( $this->colItem."14" )->applyFromArray( $this->getAddressStyling() );
            $this->activeSheet->mergeCells('B14:G14');

            $this->activeSheet->setCellValue( $this->colItem."15", "State: ".$this->rfq->Project->MainInformation->Subregions->name );
            $this->activeSheet->getStyle( $this->colItem."15" )->applyFromArray( $this->getAddressStyling() );
            $this->activeSheet->mergeCells('B15:G15');

            $this->tableStartingCoordinates = 19;
        }
        else
        {
            $this->activeSheet->setCellValue( $this->colItem."13", "Country: ".$this->rfq->Region->country );
            $this->activeSheet->getStyle( $this->colItem."13" )->applyFromArray( $this->getAddressStyling() );
            $this->activeSheet->mergeCells('B13:G13');

            $this->activeSheet->setCellValue( $this->colItem."14", "State: ".$this->rfq->SubRegion->name );
            $this->activeSheet->getStyle( $this->colItem."14" )->applyFromArray( $this->getAddressStyling() );
            $this->activeSheet->mergeCells('B14:G14');

            $this->tableStartingCoordinates = 18;
        }
    }

    public function setNotesInput()
    {
        $coords = ( $this->rfq->type == RFQ::TYPE_PROJECT ) ? 17 : 16;

        $notesInputCoord = "C{$coords}:G{$coords}";

        $this->activeSheet->setCellValue( $this->colItem.$coords, 'Notes:' );
        $this->activeSheet->getStyle( $this->colItem.$coords )->applyFromArray( $this->getAddressStyling() );

        $this->activeSheet->getStyle($notesInputCoord)->applyFromArray( $this->setNotesInputStyling() );
        $this->activeSheet->mergeCells($notesInputCoord);
        $this->unlockCell($notesInputCoord);
    }

    public function setHiddenRFQId()
    {
        $this->activeSheet->setCellValue( $this->colRFQId, $this->rfq->id );
    }

    public function setHiddenRFQType()
    {
        $this->activeSheet->setCellValue( $this->colRFQType, $this->rfq->type );
    }

    public function setHiddenRFQSupplierId()
    {
        $this->activeSheet->setCellValue( $this->colRFQSupplierId, $this->rfqSupplier->id );
    }

    public function setNotesInputStyling()
    {
        $styling = array(
            'borders' => array(
                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => $this->editableCellColor)
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
        );

        return $styling;
    }

    public function setTitle()
    {
        // Load Text Helper
        sfContext::getInstance()->getConfiguration()->loadHelpers( array( 'Text' ) );

        $this->activeSheet->setTitle('Sheet 1');
    }

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
        $this->currentPage = 1;
        $this->currentPageHeader = array();
    }

    function setActiveSheet( $index = null )
    {
        $index = ( $index ) ? $index : 0;

        //Set Active Index
        $this->objPHPExcel->setActiveSheetIndex( $index );
        //Get Current Active Sheet
        $this->getActiveSheet();
    }

    function getActiveSheet()
    {
        $this->activeSheet = $this->objPHPExcel->getActiveSheet();
    }

    public function hideColumn()
    {
        // hide Columns
        $this->activeSheet->getColumnDimension( $this->colRowType )->setVisible( false );
        $this->activeSheet->getColumnDimension( $this->colItemType )->setVisible( false );
        $this->activeSheet->getColumnDimension( $this->colLevel )->setVisible( false );
        $this->activeSheet->getColumnDimension( $this->colRfqItemId )->setVisible( false );

        // for RFQ and RFQ's Supplier's importing
        $this->activeSheet->getColumnDimension( $this->colImportMetaInfo )->setVisible( false );
    }

    /*
        Generate Default Header Setting and Call Single or Multitype header
        based on number of columnSetting
    */
    public function createNewPage( $pageNo = null )
    {
        if ( !$pageNo )
        {
            return;
        }

        //create new header
        $this->createHeader( true );

        //Update Current Page Counter
        $this->currentPage = $pageNo;

        $this->newLine();
    }

    public function createHeader( $new = false )
    {
        $row = $this->currentRow;

        //set default column
        $this->activeSheet->setCellValue( $this->colItem.$row, "Item" );
        $this->activeSheet->setCellValue( $this->colDescription.$row, "Description" );
        $this->activeSheet->setCellValue( $this->colUnit.$row, "Unit" );

        //reset Character Counter
        $this->currentChar = 'A';

        $this->excelType = self::EXCEL_TYPE_SINGLE;
        $this->createSingleTypeHeader();

        //Set Column Width
        $this->activeSheet->getColumnDimension( "A" )->setWidth( 1.3 );
        $this->activeSheet->getColumnDimension( $this->colItem )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colDescription )->setWidth( 45 );
        $this->activeSheet->getColumnDimension( $this->colUnit )->setWidth( 6 );
        $this->activeSheet->getColumnDimension( $this->colRate )->setWidth( 8 );
        $this->currentRow++;
    }

    /*
        Generate single type column Header
    */
    public function createSingleTypeHeader()
    {
        $row = $this->currentRow;

        $this->activeSheet->setCellValue( $this->colQty.$row, 'Quantity');
        $this->activeSheet->setCellValue( $this->colAmount.$row, 'Amount' );
        $this->activeSheet->setCellValue( $this->colRate.$row, 'Rate' );
        $this->activeSheet->getStyle( $this->colItem.$row.':'.$this->colAmount.$row )->applyFromArray( $this->getColumnHeaderStyle() );
        $this->activeSheet->getColumnDimension( $this->colQty )->setWidth( 8 );
        $this->activeSheet->getColumnDimension( $this->colAmount )->setWidth( 8 );
        $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize( PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 );
    }

    public function createFooter()
    {
        $this->newLine( true );

        if ( $this->currentPage >= 1 ) { //Set Page Break if Page Bigger than 0
            $this->activeSheet->setBreak( $this->colDescription.$this->currentRow , PHPExcel_Worksheet::BREAK_ROW );
        }

        $this->currentRow+=2;
    }

    public function newLine( $bottom = false )
    {
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

        if ( $bottom ) {
            $newLineStyle['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
            $newLineStyle['borders']['bottom']['color'] = array( 'argb' => '000000' );
        }

        $this->activeSheet->getStyle( $this->firstCol.$this->currentRow.":".$this->lastCol.$this->currentRow )
        ->applyFromArray( $newLineStyle );

        $this->currentRow++;
    }

    public function setElement( $element = null )
    {
        if ( !$element )
            return;

        $this->currentElement = $element;

        $this->currentElementNo++;
        //Create New Page

        //Reset Element Page Counter
        $this->startElementCounter();

        //set element title
        $this->setElementTitle( null, true );
    }

    public function setElementTitle( $appendString = null, $new = false )
    {
        if ( !$this->currentElement['description'] ) return;

        $description = ( $appendString ) ? $this->currentElement['description'].' '.$appendString : $this->currentElement['description'];

        $coord = $this->colDescription.$this->currentRow;

        $rowTypeCoord = $this->colRowType.$this->currentRow;

        $style = $this->getElementTitleStyle();

        $this->activeSheet->setCellValue( $coord, $description );

        $this->activeSheet->getStyle( $coord )->applyFromArray( $style );

        //set row type element if element is new
        //this will ignore the continuos element
        if ( $new ) $this->activeSheet->setCellValue( $rowTypeCoord, self::ROW_TYPE_ELEMENT_TEXT );

        $this->lockCell( $coord );

        $this->newLine();
    }

    public function newItem()
    {
        $this->newLine();
        //Increase Item Counter
        $this->itemCount++;
    }

    public function getAddressComposerNameStyling()
    {
        $styling = array(
            'font' => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        return $styling;
    }

    public function getAddressStyling()
    {
        $styling = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        );

        return $styling;
    }

    public function setItemStyle()
    {
        $coord = $this->colDescription.$this->currentRow;

        //Get And Set Item Style
        $this->activeSheet->getStyle( $coord )->applyFromArray( $this->getDescriptionStyle( $this->itemType ) );
    }

    public function setItem( $rfqItemId, $itemIndex, $description, $remarks, $itemType, $itemLvl )
    {
        $itemIndexCoord = $this->colItem . $this->currentRow;
        $coord          = $this->colDescription . $this->currentRow;
        $rowTypeCoord   = $this->colRowType . $this->currentRow;
        $itemTypeCoord  = $this->colItemType . $this->currentRow;
        $levelCoord     = $this->colLevel . $this->currentRow;
        $rfqItemIdCoord = $this->colRfqItemId . $this->currentRow;
        $this->itemType = $itemType;

        if ( ! empty($remarks) )
        {
            $this->activeSheet->setCellValue( $coord, $description."\n"."({$remarks})" );
        }
        else
        {
            $this->activeSheet->setCellValue( $coord, $description );
        }

        $this->setItemStyle();

        $this->activeSheet->setCellValue( $itemIndexCoord, $itemIndex );
        $this->activeSheet->setCellValue( $rowTypeCoord, self::ROW_TYPE_ITEM_TEXT );
        $this->activeSheet->setCellValue( $itemTypeCoord, BillItemTable::getItemTypeText($itemType) );
        $this->activeSheet->setCellValue( $levelCoord, $itemLvl );
        $this->activeSheet->setCellValue( $rfqItemIdCoord, $rfqItemId );
    }

    public function getPageInfoFromBillReference( $billReferences = array() ) {
        if ( !( count( $billReferences ) > 0 ) )
            return;

        $pageInfo = array();

        foreach ( $billReferences as $k => $reference ) {
            $pageInfo['reference_num'] = $reference['reference_num'];
            $pageInfo['page_no'] = $reference['BillPage']['page_no'];
            $pageInfo['element_no'] = $reference['BillPage']['element_no'];
            $pageInfo['bill_ref'] = $reference['bill_ref'];
            $pageInfo['bill_item_id'] = $reference['bill_item_id'];
        }

        return $pageInfo;
    }

    public function getHeaderStartPage( $billReferences = array() ) {
        if ( !( count( $billReferences ) > 0 ) )
            return;

        $pageInfo['reference_num'] = $billReferences[0]['reference_num'];
        $pageInfo['page_no'] = $billReferences[0]['BillPage']['page_no'];
        $pageInfo['element_no'] = $billReferences[0]['BillPage']['element_no'];
        $pageInfo['bill_ref'] = $billReferences[0]['bill_ref'];
        $pageInfo['bill_item_id'] = $billReferences[0]['bill_item_id'];

        return $pageInfo;
    }

    public function setSubRow( $description, $unit = false, $rate = false, $padding = false ) {
        $this->newLine();
        $coord = $this->colDescription.$this->currentRow;
        $rateCoord = $this->colRate.$this->currentRow;

        $style = $this->getSubDescriptionStyle();

        if($rate)
        {
            $this->activeSheet->setCellValue( $rateCoord, $rate );
        }

        if($unit)
        {
            $this->setUnit( $unit );
        }

        if ( $padding )
            $description.="\n";

        $this->activeSheet->setCellValue( $coord, $description );
        $this->activeSheet->getStyle( $coord )->applyFromArray( $style );
        $this->lockCell( $coord );

    }

    public function setUnit( $unit )
    {
        $coord = $this->colUnit.$this->currentRow;
        $style = $this->getUnitStyle();

        $this->activeSheet->setCellValue( $coord, $unit );
        $this->activeSheet->getStyle( $coord )->applyFromArray( $style );
        $this->activeSheet->getStyle( $coord )->getNumberFormat()->setFormatCode('#,##0.00_-');
        $this->lockCell( $coord );
    }

    public function setChar( $char )
    {
        $coord = $this->colItem.$this->currentRow;
        $style = $this->getUnitStyle();

        $this->activeSheet->setCellValue( $coord, $char );
        $this->activeSheet->getStyle( $coord )->applyFromArray( $style );
        $this->lockCell( $coord );
    }

    public function setQty( $qty )
    {
        $coord = $this->colQty.$this->currentRow;
        $style = $this->getQtyStyle();

        $this->activeSheet->setCellValue( $coord, $qty );
        $this->activeSheet->getStyle( $coord )->applyFromArray( $style );
        $this->activeSheet->getStyle( $coord )->getNumberFormat()->setFormatCode('#,##0.00_-');
    }

    public function setRate( $rate )
    {
        $coord = $this->colRate.$this->currentRow;
        $style = $this->getRateStyle();

        $this->activeSheet->setCellValue( $coord, $rate );
        $this->activeSheet->getStyle( $coord )->applyFromArray( $style );
        $this->activeSheet->getStyle( $coord )->getNumberFormat()->setFormatCode('#,##0.00_-');
        $this->unlockCell($coord);
    }

    public function setQuantity( $quantities, $overridingValue = null )
    {
        $style = $this->getQtyStyle();

        switch ( $this->excelType )
        {
            case self::EXCEL_TYPE_SINGLE:
                $coord = $this->colQty.$this->currentRow;
                $qty   = ( $overridingValue ) ? $overridingValue : $quantities[0];

                $this->activeSheet->setCellValue( $coord, $qty );
                $this->activeSheet->getStyle( $coord )->applyFromArray( $style );
                $this->activeSheet->getStyle( $coord )->getNumberFormat()->setFormatCode('#,##0.00_-');
                break;
        }
    }

    public function setItemHead( $description , $itemType, $itemLvl, $new = false)
    {
        $coord = $this->colDescription.$this->currentRow;

        $rowTypeCoord = $this->colRowType.$this->currentRow;

        $itemTypeCoord = $this->colItemType.$this->currentRow;

        $levelCoord = $this->colLevel.$this->currentRow;

        $style = $this->getDescriptionStyle( ResourceItem::TYPE_HEADER_TEXT );

        $this->activeSheet->setCellValue( $coord, $description );

        $this->activeSheet->getStyle( $coord )->applyFromArray( $style );

        if($new)
        {
            $this->activeSheet->setCellValue( $rowTypeCoord, self::ROW_TYPE_ITEM_TEXT );

            $this->activeSheet->setCellValue( $itemTypeCoord, BillItemTable::getItemTypeText($itemType) );

            $this->activeSheet->setCellValue( $levelCoord, $itemLvl );
        }

        $this->lockCell( $coord );
    }

    public function setAmount( $overridingValue  = false )
    {
        $rateCoord = $this->colRate.$this->currentRow;
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

                $style = $this->getQtyStyle();

                $this->activeSheet->setCellValue( $coord, $formula );

                $this->activeSheet->getStyle( $coord )->applyFromArray( $style );
                $this->activeSheet->getStyle( $coord )->getNumberFormat()->setFormatCode('#,##0.00_-');
                break;
        }
    }

    public function setGlobalStyling()
    {
        // Set Global Styling Styling
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName( 'Arial' );
        $this->objPHPExcel->getDefaultStyle()->getFont()->setSize( 9 );
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText( true );
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_CENTER );

        $pageMargins = $this->activeSheet->getPageMargins();
        $pageMargins->setTop(0.04);
        $pageMargins->setBottom(0.04);
    }

    public function getColumnHeaderStyle() {
        $columnHeadStyle = array(
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

        return $columnHeadStyle;
    }

    public function getElementTitleStyle() {
        $elementTitleStyle = array(
            'font' => array(
                'bold' => true,
                'underline' => true
            )
        );

        return $elementTitleStyle;
    }

    public function getUnitStyle() {
        $unitStyle = array( 'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            ) );
        return $unitStyle;
    }


    public function getRateStyle() {
        $rateStyle = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => $this->editableCellColor)
            )
        );

        return $rateStyle;
    }

    public function getQtyStyle() {
        $qtyStyle = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
            )
        );

        return $qtyStyle;
    }

    public function getDescriptionStyle( $billType ) {
        switch ( $billType ) {
        case ResourceItem::TYPE_HEADER_TEXT:
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

    public function getSubDescriptionStyle() {
        $descriptionStyle = array(
            'font' => array(
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'wrapText' => true,
                'indent' => 1
            )
        );

        return $descriptionStyle;
    }

    public function getProjectTitleStyle() {
        $projectTitleStyle = array(
            'font' => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        );

        return $projectTitleStyle;
    }

    public function lockCell( $coord ) {
        $this->activeSheet->protectCells( $coord, 'PHPExcel' );
    }

    public function unlockCell( $coord ) {
        $this->activeSheet->getStyle( $coord )->getProtection()->setLocked( PHPExcel_Style_Protection::PROTECTION_UNPROTECTED );
    }

    public function writeExcel() {
        $extension = '.xlsx';
        $objWriter = new PHPExcel_Writer_Excel2007( $this->objPHPExcel );
        $objWriter->save( $this->savePath.DIRECTORY_SEPARATOR.$this->filename.$extension );

        return array(
            'filename' => $this->filename,
            'extension' => $extension,
            'type' => ExportedFile::FILE_TYPE_EXCEL_TEXT
        );
    }

    public function writeCSV() {
        $extension = '.csv';
        $objWriter = PHPExcel_IOFactory::createWriter( $this->objPHPExcel, 'CSV' );
        $objWriter->save( $this->savePath.DIRECTORY_SEPARATOR.$this->filename.$extension );

        return array(
            'filename' => $this->filename,
            'extension' => $extension,
            'type' => 'Excel'
        );
    }
}