<?php

class sfBuildspaceRFQExcelParser
{
    public $filename;
    public $uploadPath;
    public $extension;
    public $pathToFile;
    public $data                = array();
    public $startColumn         = 'A';
    public $highestColumm;
    public $highestRow;
    public $printSettings;
    public $error               = false;
    public $colItem             = "E";
    public $colDescription      = "F";
    public $colQty              = "K";
    public $colUnit             = "L";
    public $colRate             = "M";
    public $colAmount           = "N";
    public $colItemType         = 'L';
    public $colElement          = 'B';
    public $currentElementId    = -1;
    public $currentElementDescription;
    public $currentItemId       = 0;
    public $currentRow          = 0;
    public $currentLevel        = 0;
    public $headerRootId        = array();
    public $currentHeaderRootId;
    public $currentSubHeaderRootId;
    public $sfExportXML         = null;
    public $elementItemCount    = array();
    public $elementErrorCount   = array();
    public $colSlugArray        = array();
    public $sheets              = array();
    public $deleteFile          = true;
    public $prevHeader          = array();
    public $errorMsg            = array();

    // coordinates so store RFQ and Supplier's ID
    public $colImportMetaInfo   = 'I';
    public $colRFQId            = 'I1';
    public $colRFQType          = 'I2';
    public $colRFQSupplierId    = 'I10';

    const ROW_TYPE_ELEMENT      = 1;
    const ROW_TYPE_ITEM         = 1;
    const EXCEL_TYPE_SINGLE     = 1;
    const EXCEL_TYPE_MULTIPLE   = 2;
    const ROW_TYPE_ELEMENT_TEXT = "Element";
    const ROW_TYPE_ITEM_TEXT    = "Item";

    public function __construct( RFQ $rfq, RFQSupplier $supplier, $filename = null, $extension = null, $uploadPath = null, $deleteFile = null )
    {
        $this->rfq        = $rfq;
        $this->supplier   = $supplier;

        $this->filename   = ( $filename ) ? $filename : $this->filename;
        $this->uploadPath = ( $uploadPath ) ? $uploadPath : sfConfig::get( 'sf_upload_dir' ).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        $this->extension  = ( $extension ) ? $extension : $this->extension;
        $this->pathToFile = $this->uploadPath.$this->filename.'.'.$this->extension;
        $this->deleteFile = ( $deleteFile ) ? $deleteFile : $this->deleteFile;

        $this->determineExcelType();
    }

    public function startRead()
    {
        //For Now Defaulted to Single Sheet Excel
        if ( !$this->validate() )
            return false;

        $this->loadBook(); // load objPHPExcel
        $this->setActiveSheet(); //For now we'll defaulted to 0

        $this->getRFQCredentials();

        $this->setupImportDataStructure();

        $this->iterateSheet();
        $this->endReader();
    }

    public function endReader()
    {
        if ($this->deleteFile)
        {
            unlink($this->pathToFile);
        }
    }

    public function getRFQCredentials()
    {
        $this->rfqId         = self::getRFQId();
        $this->rfqType       = self::getRFQType();
        $this->rfqSupplierId = self::getRFQSupplierId();

        // if current imported rfq id doesn't match then throw expection
        if ( $this->rfq->id != $this->rfqId )
        {
            throw new Exception('Invalid RFQ Id from imported excel file.');
        }

        // if current imported rfq supplier id doesn't match then throw expection
        if ( $this->supplier->id != $this->rfqSupplierId )
        {
            throw new Exception('Invalid RFQ Supplier Id from imported excel file.');
        }
    }

    public function setupImportDataStructure()
    {
        $this->data['rfqInformation'] = array(
            'rfqId'         => $this->rfqId,
            'rfqType'       => $this->rfqType,
            'rfqSupplierId' => $this->rfqSupplierId,
        );

        return $this->data;
    }

    public function iterateSheet()
    {
        $this->iterateRow(); // default is only 1 sheet so there is no iteration here
    }

    public function iterateRow()
    {
        // iterate rows
        $item = array();

        // start row determine by which type of RFQ
        $startRow = ( $this->rfqType == RFQ::TYPE_PROJECT ) ? 19 : 18;

        for ( $row = $startRow; $row <= $this->highestRow; $row++ )
        {
            //  Iterate Column
            $this->currentRow = $row;

            $this->readRowItem();
        }
    }

    public function readRowItem()
    {
        $rowDescription = $this->getDescription();

        // If Description Row Not NULL or Empty Set New Item
        if($rowDescription != null && $rowDescription != '')
        {
            $this->setItem(); //Set Item
        }
    }

    public function setItem(){
        // Later to put default Set Item & Element Function
        // Override Set Item Method
    }

    public function determineExcelType($excelType = null)
    {
        $this->excelType = self::EXCEL_TYPE_SINGLE; //for now defaulted to single
    }

    public function getRFQItemId($coord = NULL)
    {
        $coord = ($coord) ? $coord : $this->colRfqItemId.$this->currentRow;

        $value = $this->currentSheet->getCell($coord)->getCalculatedValue();

        return $value;
    }

    public function getValidatedRate($coord = NULL){
        $coord = ($coord) ? $coord : $this->colRate.$this->currentRow;

        $rate = array(
            'value' => 0,
            'has_error' => false,
            'msg' => false,
            'is_empty' => true
        );

        $value = $this->currentSheet
            ->getCell($coord)
            ->getCalculatedValue();

        if(!$this->isEmpty($value)){
            $rate['is_empty'] = false;

            if(!is_numeric($value)){
                $rate['value'] = 0;
                $rate['has_error'] = true;
                $rate['msg'] = "Rate is not a number : ".$value;
            }else{
                $rate['value'] = $value;
            }
        }

        return $rate;
    }

    public function getRFQId()
    {
        return $this->currentSheet->getCell($this->colRFQId)->getCalculatedValue();
    }

    public function getRFQType()
    {
        return $this->currentSheet->getCell($this->colRFQType)->getCalculatedValue();
    }

    public function getRFQSupplierId()
    {
        return $this->currentSheet->getCell($this->colRFQSupplierId)->getCalculatedValue();
    }

    public function getItemType(){
        $coord = $this->colItemType.$this->currentRow;

        return $this->currentSheet
            ->getCell($coord)
            ->getCalculatedValue();
    }

    public function getDescription(){
        $coord = $this->colDescription.$this->currentRow;

        $desc = $this->currentSheet
            ->getCell($coord)
            ->getCalculatedValue();

        return $this->stripString($desc);
    }

    public function stripString( $str )
    {
        $newStr = strip_tags($str);
        //Remove Non ASCII Characters
        // $newStr = preg_replace('/\p{Cc}+/u', ' ', $newStr);
        $newStr = preg_replace('/[^(\x20-\x7F)]*/', '', $str);
        // Replace Multiple spaces with single space
        $newStr = preg_replace('/ +/', ' ', $newStr);
        // Trim the string of leading/trailing space
        $newStr = trim($newStr);

        return $newStr;
    }

    public function isEmpty($value = null){
        if($value == null && $value == '')
            return true;
    }

    public function validate() {
        return true;
    }

    public function calcHighestDimension( $activeSheetIndex = 0 ) {
        $this->highestRow = $this->currentSheet->getHighestRow() + 1;
        $this->highestColumm = $this->currentSheet->getHighestColumn();
    }

    public function setActiveSheet( $activeSheetIndex = 0 ) {
        $this->objPHPExcel->setActiveSheetIndex( $activeSheetIndex );
        $this->currentSheet = $this->objPHPExcel->getActiveSheet();
        $this->calcHighestDimension();
    }

    public function loadBook(){
        $this->explicitLoad();
    }

    public function automaticLoad() {
        /*
            This automotic Load is slower than explicit
            but will automatically detect suitable reader for file
        */
        $fileType        = PHPExcel_IOFactory::identify($this->pathToFile);
        $this->objReader = PHPExcel_IOFactory::createReader($fileType);
        
        $this->objReader->setReadDataOnly(true);
        
        $this->objPHPExcel = $this->objReader->load($this->pathToFile);

        $this->loadSheets();
    }

    public function explicitLoad() {
        /*
            Manually define reader for file
            In this case we set it to Excel 2007
        */
        $this->objReader = PHPExcel_IOFactory::createReader( "Excel2007" );
        $this->objReader->setReadDataOnly(true);
        $this->objPHPExcel = $this->objReader->load( $this->pathToFile );
        $this->loadSheets();
    }

    public function customLoad() {
        /*
            This will custom load Reader for Excel with
            common attribute In this case we set it to Excel 2007
        */
    }

    public function loadSheets(){
        $this->sheets = $this->objPHPExcel->getSheetNames();
    }

    public function getProcessedData() {
        return $this->data;
    }
}
