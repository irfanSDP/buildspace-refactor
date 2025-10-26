<?php

class sfSubPackageExcelParser {

    public $filename;

    public $extension;

    public $uploadPath;

    public $pathToFile;

    public $error;

    public $deleteFile                   = false;

    public $data                         = array();

    public $startRow                     = 3;

    // information that will be needed to import
    public $colRowType                   = 'A';
    public $colItemType                  = 'B';
    public $colLevel                     = 'C';
    public $colItemId                    = 'D';

    // table placement
    public $colItem                      = 'F';
    public $colDescription               = 'G';
    public $colUnit                      = 'H';
    public $colRate                      = 'I';

    public $currentRow                   = 0;

    const BUILDSOFT_ITEM_TYPE_HEAD1      = 1;
    const BUILDSOFT_ITEM_TYPE_HEAD2      = 2;
    const BUILDSOFT_ITEM_TYPE_HEAD3      = 4;
    const BUILDSOFT_ITEM_TYPE_HEAD4      = 512;
    const BUILDSOFT_ITEM_TYPE_NOID       = 8;
    const BUILDSOFT_ITEM_TYPE_NOIDI      = 16;
    const BUILDSOFT_ITEM_TYPE_NOIDN      = 32 ;
    const BUILDSOFT_ITEM_TYPE_HEADA      = 64;
    const BUILDSOFT_ITEM_TYPE_HEADB      = 128;
    const BUILDSOFT_ITEM_TYPE_HEADC      = 256;
    const BUILDSOFT_ITEM_TYPE_HEAD1_TEXT = "HEAD1";
    const BUILDSOFT_ITEM_TYPE_HEAD2_TEXT = "HEAD2";
    const BUILDSOFT_ITEM_TYPE_HEAD3_TEXT = "HEAD3";
    const BUILDSOFT_ITEM_TYPE_HEAD4_TEXT = "HEAD4";
    const BUILDSOFT_ITEM_TYPE_HEADA_TEXT = "HEADA";
    const BUILDSOFT_ITEM_TYPE_HEADB_TEXT = "HEADB";
    const BUILDSOFT_ITEM_TYPE_HEADC_TEXT = "HEADC";
    const BUILDSOFT_ITEM_TYPE_NOID_TEXT  = "NOID";
    const BUILDSOFT_ITEM_TYPE_NOIDI_TEXT = "NOIDI";
    const BUILDSOFT_ITEM_TYPE_NOIDN_TEXT = "NOIDN";

    public function setFileInformation($filename = null, $extension = null, $uploadPath = null)
    {
        $this->filename   = $filename;
        $this->extension  = $extension;
        $this->uploadPath = $uploadPath;
        $this->pathToFile = $this->uploadPath.$this->filename.'.'.$this->extension;
    }

    public function endReader()
    {
        if ($this->deleteFile) unlink($this->pathToFile);
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
        $startRow = $this->startRow;

        for ( $row = $startRow; $row <= $this->highestRow; $row++ )
        {
            //  Iterate Column
            $this->currentRow = $row;

            $this->readRowItem();

            $this->currentRow++;
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

    public function getValidatedRate()
    {
        $rate = array(
            'value'     => 0,
            'has_error' => false,
            'msg'       => false,
            'is_empty'  => true
        );

        $value = $this->currentSheet->getCell($this->colRate.$this->currentRow)->getCalculatedValue();

        if(!$this->isEmpty($value))
        {
            $rate['value']    = $value;
            $rate['is_empty'] = false;

            if(!is_numeric($value))
            {
                $rate['value']     = 0;
                $rate['has_error'] = true;
                $rate['msg']       = "Rate is not a number : ".$value;
            }
        }

        return $rate;
    }

    public function getItemType()
    {
        $coord = $this->colItemType.$this->currentRow;

        return $this->currentSheet->getCell($coord)->getCalculatedValue();
    }

    public function getDescription()
    {
        $coord = $this->colDescription.$this->currentRow;

        $desc = $this->currentSheet->getCell($coord)->getCalculatedValue();

        return $this->stripString($desc);
    }

    public function getItemId()
    {
        $coord = $this->colItemId.$this->currentRow;

        return $this->currentSheet->getCell($coord)->getCalculatedValue();
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

    public function isEmpty($value = null)
    {
        if($value == null && $value == '') return true;
    }

    public function calcHighestDimension( $activeSheetIndex = 0 )
    {
        $this->highestRow    = $this->currentSheet->getHighestRow() + 1;
        $this->highestColumm = $this->currentSheet->getHighestColumn();
    }

    public function setActiveSheet( $activeSheetIndex = 0 )
    {
        $this->objPHPExcel->setActiveSheetIndex( $activeSheetIndex );
        $this->currentSheet = $this->objPHPExcel->getActiveSheet();
        $this->calcHighestDimension();
    }

    public function loadBook()
    {
        $this->explicitLoad();
    }

    public function automaticLoad()
    {
        /*
            This automotic Load is slower than explicit
            but will automatically detect suitable reader for file
        */
        $this->objPHPExcel = PHPExcel_IOFactory::load( $this->pathToFile );
        $this->loadSheets();
    }

    public function explicitLoad()
    {
        /*
            Manually define reader for file
            In this case we set it to Excel 2007
        */
        $this->objReader   = PHPExcel_IOFactory::createReader( "Excel2007" );
        $this->objPHPExcel = $this->objReader->load( $this->pathToFile );
        $this->loadSheets();
    }

    public function loadSheets()
    {
        $this->sheets = $this->objPHPExcel->getSheetNames();
    }

    public function getProcessedData()
    {
        return $this->data;
    }

}