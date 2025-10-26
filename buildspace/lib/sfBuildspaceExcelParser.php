<?php
class sfBuildspaceExcelParser
{
    public $filename;
    public $uploadPath;
    public $extension;
    public $excelType;
    public $pathToFile;
    public $data = array();
    public $startColumn = 'A';
    public $startRow = 1;
    public $highestColumm;
    public $highestRow;
    public $printSettings;
    public $error = false;
    public $colItem = "E";
    public $colDescription = "F";
    public $colQty = "K";
    public $colUnit = "L";
    public $colRate = "M";
    public $colAmount = "N";
    public $colItemType = 'L';
    public $colElement = 'B';
    public $generateXML = false;
    public $currentElementId = -1;
    public $currentElementDescription;
    public $currentItemId = 0;
    public $currentRow = 0;
    public $currentLevel = 0;
    public $headerRootId = array();
    public $currentHeaderRootId;
    public $currentSubHeaderRootId;
    public $sfExportXML = null;
    public $elementItemCount = array();
    public $elementErrorCount = array();
    public $colSlugArray = array();
    public $sheets = array();
    public $deleteFile = true;
    public $prevHeader = array();
    public $errorMsg = array();

    const ROW_TYPE_ELEMENT = 1;
    const ROW_TYPE_ITEM = 1;
    const EXCEL_TYPE_SINGLE = 1;
    const EXCEL_TYPE_MULTIPLE = 2;
    const ROW_TYPE_ELEMENT_TEXT = "Element";
    const ROW_TYPE_ITEM_TEXT = "Item";

    protected $objPHPExcel;
    protected $currentSheet;

    function __construct( $filename = null, $extension = null, $uploadPath = null, $generateXML = false, $deleteFile = false )
    {
        $this->filename = ( $filename ) ? $filename : $this->filename;
        $this->uploadPath = ( $uploadPath ) ? $uploadPath : sfConfig::get( 'sf_upload_dir' ).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        $this->extension = ( $extension ) ? $extension : $this->extension;
        $this->pathToFile = $this->uploadPath.$this->filename.'.'.$this->extension;
        $this->generateXML = ( $generateXML ) ? $generateXML : $this->generateXML;
        $this->deleteFile = ( $deleteFile ) ? $deleteFile : $this->deleteFile;

        if($this->generateXML) //Create XML if true
            $this->createXML();
        
        $this->determineExcelType();
    }

    public function startRead() 
    {
        //For Now Defaulted to Single Sheet Excel
        if ( !$this->validate() )
            return false;

        $this->loadBook(); // load objPHPExcel
        $this->setActiveSheet(); //For now we'll defaulted to 0

        $this->iterateSheet();

        if($this->generateXML)
            $this->sfExportXML->write();

        $this->endReader();
    }

    public function endReader()
    {
        if($this->deleteFile)
            unlink($this->pathToFile);
    }

    public function iterateSheet()
    {
        $this->iterateRow(); // default is only 1 sheet so there is no iteration here
    }

    public function iterateRow()
    {
        for ( $row = $this->startRow; $row <= $this->highestRow; $row++ )
        {
            //  Iterate Column
            $this->currentRow = $row;

            $this->readRowItem();
        }
    }

    public function readRowItem()
    {
        $rowDescription = $this->getDescription();

        //If Description Row Not NULL or Empty Set New Item
        if($rowDescription != null && $rowDescription != ''){
            $this->setElement(); //Set Element
            $this->setItem(); //Set Item
        }
    }

    public function createXML(){
        $this->sfExportXML = new sfBuildspaceExportBillXML($this->filename, $this->uploadPath, null, false);
        $this->sfExportXML->createBillXML(true, true);
    }

    public function getHeaderRootAndLevel($headerString)
    {
        $str = str_split($headerString, 4);
        $parent = array();
        
        //Assign Current Header Lvl
        $headNo = (count($str) > 0) ? $str[1] : 0;

        if(!(count($this->prevHeader) > 0))
        {
            $lvl = 0;
            $rootId = $this->currentItemId;
        }
        else
        {
            if($headNo > $this->prevHeader['headNo'])
            {
                // If Greater than prev add as child header
                $lvl = $this->prevHeader['lvl']+1;
                $rootId = $this->prevHeader['id'];
                $headNo = $this->prevHeader['headNo']+1;
                $parent = $this->prevHeader;

            }
            else if($headNo == $this->prevHeader['headNo'])
            {
                //if Equal add as a child of parent previous
                $lvl = $this->prevHeader['lvl'];
                $rootId = $this->prevHeader['rootId'];

                if(array_key_exists('_parent', $this->prevHeader) && (count($this->prevHeader['_parent']) > 0))
                {
                    $parent = $this->prevHeader['_parent'];
                }

            }
            else
            {
                $lvlCount = $this->prevHeader['headNo'] - $headNo;

                //Assign prev Header Parent to current Header
                $header = $this->prevHeader['_parent'];

                for($i = 1; $i < $lvlCount ; $i++)
                {
                    $header = $this->prevHeader['_parent'];
                }

                if(array_key_exists('_parent', $header) && (count($header['_parent']) > 0))
                {
                    //Set to follow 
                    $lvl = $header['lvl'];
                    $parent = $header['_parent'];
                    $rootId = $header['rootId'];
                }
                else
                {
                    //Set as New Root Parent If No Parent Found
                    $lvl = 0;
                    $rootId = $this->currentItemId;
                }
                
            }
        }

        $this->prevHeader = array(
            'headNo' => $headNo,
            'id' => $this->currentItemId,
            'rootId' => $rootId,
            'lvl' => $lvl
        );

        if(count($parent) > 0)
            $this->prevHeader['_parent'] = $parent;

        return array('rootId' => $rootId, 'lvl' => $lvl);
    }

    public function getItemRootAndLevel(){

        if(!(count($this->prevHeader) > 0)){
            $lvl = 0;
            $rootId = $this->currentItemId;
        }else{
            $lvl = $this->prevHeader['lvl']+1;
            $rootId = $this->prevHeader['id'];
        }

        return array('rootId' => $rootId, 'lvl' => $lvl);
    }

    public function resetHeaderInfo(){
        $this->prevHeader = array();
    }

    public function setElement(){
        //Setup Element Coordinate
        //Override Set Element Method
        /* This is Buildspace default setElement Method */
        $this->newElement();
        $descCoord = $this->colDescription.$this->currentRow;

        $this->elementItemCount[$this->currentElementId] = 0;
        
        $element = array(
            'id' => $this->currentElementId,
            'rowType' => self::ROW_TYPE_ELEMENT_TEXT,
            'description' => $this->currentSheet->getCell($descCoord)->getCalculatedValue()
        );

        //ifXML Enable create Element Item
        if($this->generateXML)
            $this->sfExportXML->addElementChildren($element);

        $element['_child'] = array(); //create Child Array for Element

        $this->data[$this->currentElementId] = $element;
    }

    public function setItem(){
        // Later to put default Set Item & Element Function
        // Override Set Item Method
    }

    public function determineExcelType($excelType = null){
        if(!$excelType){
            $this->excelType = self::EXCEL_TYPE_SINGLE; //for now defaulted to single
        }else{
            $this->excelType = $excelType;
        }
    }

    public function getValidatedUOM($coord = NULL){
        $coord = ($coord) ? $coord : $this->colUnit.$this->currentRow;

        $uom = array(
            'has_error'  => false,
            'msg'        => false,
            'id'         => - 1,
            'symbol'     => '---',
            'new_symbol' => null,
            'is_empty'   => true
        );

        $uomSymbol = $this->currentSheet
            ->getCell($coord)
            ->getCalculatedValue();

        if(!$this->isEmpty($uomSymbol)){
            $uomBuildspace = UnitOfMeasurementTable::getUnitOfMeasurementBySymbol($uomSymbol);
            $uom['is_empty'] = false;

            if($uomBuildspace)
            {
                $uom['id'] = $uomBuildspace['id'];
                $uom['symbol'] = $uomBuildspace['symbol'];
            }
            else
            {
                $uom['has_error']  = true;
                $uom['msg']        = 'Unit Not Found in Buildspace : ' . $uomSymbol;
                $uom['new_symbol'] = $uomSymbol;

                $this->elementErrorCount[$this->currentElementId]+=1;
            }
        }

        return $uom;
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
                $this->elementErrorCount[$this->currentElementId]+=1;
            }else{
                $rate['value'] = $value;
            }
        }

        return $rate;
    }

    public function getValidatedQty($coord = NULL)
    {
        $coord = ($coord) ? $coord : $this->colQty.$this->currentRow;

        $qty = array(
            'value' => 0,
            'has_error' => false,
            'msg' => false,
            'is_empty' => true
        );

        $value = $this->currentSheet
            ->getCell($coord)
            ->getCalculatedValue();

        if(!$this->isEmpty($value))
        {
            $qty['is_empty'] = false;

            if(!is_numeric($value)){
                $qty['value'] = 0;
                $qty['has_error'] = true;
                $qty['msg'] = "Quantity is not a number : ".$value;
                $this->elementErrorCount[$this->currentElementId]+=1;
            }else{
                $qty['value'] = $value;
            }
        }

        return $qty;
    }

    public function getItemType(){
        $coord = $this->colItemType.$this->currentRow;

        return $this->currentSheet
            ->getCell($coord)
            ->getCalculatedValue();
    }

    public function getDescription()
    {
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

    public function getAmount(){
        $coord = $this->colAmount.$this->currentRow;

        return $this->currentSheet
            ->getCell($coord)
            ->getCalculatedValue();
    }

    public function isEmpty($value = null){
        if($value == null && $value == '')
            return true;
    }

    public function newElement()
    {
        $this->currentElementId++;
        $this->elementErrorCount[$this->currentElementId] = 0;
        $this->resetLevelCounter(); //reset Level Counter
        $this->resetHeaderInfo();
    }

    public function resetLevelCounter(){
        /* Old ways of detecting Element */
        $this->currentHeaderRootId = 0;
        $this->currentSubHeaderRootId = 0;
    }

    public function newItem(){
        $this->currentItemId++;
    }

    public function validate() {
        return true;
    }

    public function calcHighestDimension( $activeSheetIndex = 0 ) {
        $this->highestRow = $this->currentSheet->getHighestDataRow() + 1;
        $this->highestColumm = $this->currentSheet->getHighestDataColumn();
    }

    public function setActiveSheet( $activeSheetIndex = 0 ) {
        $this->objPHPExcel->setActiveSheetIndex($activeSheetIndex);
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

    public function processPreviewData(){
        //For Now Defaulted to Single Sheet Excel
        if ( !$this->validate() )
            return false;

        $this->automaticLoad(); // load objPHPExcel

        $sheetCount = $this->objPHPExcel->getSheetCount();

        $defaultSheetIndex = ($sheetCount) ? $sheetCount-1: 0;

        $this->setActiveSheet($defaultSheetIndex); //For now we'll defaulted to the last sheet

        //Generate Column
        for( $col = 'A'; $col <= $this->highestColumm; $col++){
                $colSlug = 'col_'.$col;

                array_push($this->colSlugArray, array(
                    'name' => $col,
                    'slug' => $colSlug,
                    'width' => $this->currentSheet->getColumnDimensionByColumn($col)->getWidth()
                ));
        }

        //Iterate Column And Row To Retrieve cell Value

        for ( $row = 1; $row <= 30; $row++ ){ //default Maximum is 30
            $this->newItem();

            $rowItem = array(
                'id' => $this->currentItemId
            );

            for( $col = 'A'; $col <= $this->highestColumm; $col++){
                $colSlug = 'col_'.$col;

                $cellValue = $this->currentSheet
                    ->getCell($col.$row)
                    ->getCalculatedValue();

                $rowItem[$colSlug] = $cellValue;
            }

            array_push($this->data, $rowItem);
        }

        return $this->data;
    }
}
