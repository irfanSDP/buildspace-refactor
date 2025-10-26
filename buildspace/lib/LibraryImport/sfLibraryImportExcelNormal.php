<?php
class sfLibraryImportExcelNormal extends sfBuildspaceLibraryExcelParser
{
    public $colItem = "A";
    public $colDescriptionFrom = "B";
    public $colDescriptionTo = "B";
    public $colQty = "D";
    public $colUnit = "C";
    public $colRate = "E";
    public $colAmount = "F";
    public $colItemType = 'M';
    public $colTrade = '';
    public $startRow = 3;
    public $tradeTitleCoordinate = 'A1';
    public $inCurrentItem = false;
    public $currentItemArray = array();

    function __construct( $filename = null, $extension = null, $uploadPath = null, $generateXML = null, $deleteFile = null ) {
        parent::__construct( $filename, $extension, $uploadPath, $generateXML, $deleteFile );
    }

    public function determineExcelType($excelType = null){
        $this->excelType = self::EXCEL_TYPE_SINGLE; //for now defaulted to single
    }

    public function loadBook(){
        $this->automaticLoad(); //Set Book To Automatic Load Slower but works for BT2 & Buildsoft
    }

    public function getCurrentItemRootAndLevel(){
        $levelInfo = array();

        if($this->currentSubHeaderRootId){
            $levelInfo['rootId'] = $this->currentSubHeaderRootId;
            $levelInfo['level'] = $this->currentLevel+2;
        }else if($this->currentHeaderRootId){
            $levelInfo['rootId'] = $this->currentHeaderRootId;
            $levelInfo['level'] = $this->currentLevel+1;
        }else{
            $levelInfo['rootId'] = $this->currentItemId;
            $levelInfo['level'] = $this->currentLevel = 0;
        }

        return $levelInfo;
    }

    public function getCurrentHeaderRootAndLevel(){
        $levelInfo = array();

        if($this->currentHeaderRootId){
            $levelInfo['rootId'] = $this->currentHeaderRootId;
            $levelInfo['level'] = $this->currentLevel+1;
            $this->currentSubHeaderRootId = $this->currentItemId;
        }else{
            $this->currentHeaderRootId = $levelInfo['rootId'] = $this->currentItemId;
            $levelInfo['level'] = $this->currentLevel = 0;
        }

        return $levelInfo;
    }

    public function iterateSheet(){
        /* 
            Override the default iterate Sheet Function
            since this gonna have more than 1 sheet
        */

        foreach($this->sheets as $index => $name){
            $this->setActiveSheet($index); //Set Current Active Sheet
            $this->setTrade(); //Set New Trade
            parent::iterateRow();
        }
    }

    public function readRowItem()
    {
        $rowDescription = $this->getDescription();

        //If Description Row Not NULL or Empty Set New Item
        if($rowDescription != null || $rowDescription != '')
        {
            $this->setItem(); //Set Item
        }
        else
        {
            //Save Current Item before reset
            $this->saveCurrentItem();

            //Reset Current Item
            $this->resetCurrentItem();
        }
    }

    public function resetCurrentItem(){
        //Reset Current Item
        $this->inCurrentItem = false;
        $this->currentItemArray = array();
    }

    public function getDescription(){
        $description = array();

        // will get column description numbering first
        $colDescriptionFromNum = PHPExcel_Cell::columnIndexFromString($this->colDescriptionFrom);
        $colDescriptionToNum   = PHPExcel_Cell::columnIndexFromString($this->colDescriptionTo);

        // get the range of column to be processed
        $colDescriptionRange = range($colDescriptionFromNum, $colDescriptionToNum);

        // the system will then loop selected range in order to construct the description
        foreach ( $colDescriptionRange as $columnIndex )
        {
            $columnString = PHPExcel_Cell::stringFromColumnIndex($columnIndex-1);

            $rowDescription = $this->currentSheet->getCell($columnString.$this->currentRow)
                ->getCalculatedValue();

            if ( empty($rowDescription) )
            {
                continue;
            }

            $description[] = trim($rowDescription);

            unset($columnIndex);
        }

        unset($colDescriptionRange);

        return implode(' ', $description);
    }

    public function setTrade()
    {
        $this->newTrade();

        $tradeTitle = $this->currentSheet
            ->getCell($this->colDescriptionFrom.'1')
            ->getCalculatedValue();

        //Set Item Count Counter to 0
        $this->tradeItemCount[$this->currentTradeId] = 0;
        
        //Prepare trade Item
        $trade = array(
            'id' => $this->currentTradeId,
            'rowType' => parent::ROW_TYPE_ELEMENT_TEXT,
            'description' => htmlspecialchars ($tradeTitle)
        );

        //ifXML Enable create Trade Item
        if($this->generateXML && ($trade['description'] != null && $trade['description'] != ''))
        {
            $this->sfExportXML->addTradeChildren($trade);
        }

        $trade['_child'] = array(); //create Child Array for Trade

        //Save Trade to Data Array
        $this->data[$this->currentTradeId] = $trade;
    }

    public function setItem()
    {
        $unitCoord = ($this->colUnit != null) ? $this->colUnit.$this->currentRow : null;
        $qtyCoord = ($this->colQty != null) ? $this->colQty.$this->currentRow : null;
        $amountCoord = ($this->colAmount != null) ? $this->colAmount.$this->currentRow : null;
        $rateCoord = ($this->colRate != null) ? $this->colRate.$this->currentRow : null;
        $itemCoord = ($this->colItem != null) ? $this->colItem.$this->currentRow : null;

        if($this->inCurrentItem == false){
            $this->newItem();

            //Set Current Item Flag
            $this->inCurrentItem = true;

            $this->currentItemArray = array(
                'id' => $this->currentItemId,
                'description' => '',
                'rowType' => self::ROW_TYPE_ITEM_TEXT,
                'bill_ref' => '',
                'tradeId' => $this->currentTradeId,
                'rate-has_build_up' => false,
                'rate-has_cell_reference' => false,
                'rate-has_formula' => false,
                'rate-linked' => false,
                'quantity_per_unit-value' => 0,
                'quantity_per_unit-final_value' => 0,
                'quantity_per_unit-has_build_up' => false,
                'quantity_per_unit-has_cell_reference' => false,
                'quantity_per_unit-has_formula' => false,
                'quantity_per_unit-linked' => false,
                'quantity_per_unit-has_error' => false,
                'quantity_per_unit-msg' => false,
                'rate-final_value' => 0,
                'rate-value' => '',
                'rate-has_error' => false,
                'rate-msg' => false,
                'amount' => '',
                'uom_id' => null,
                'uom_id-has_error' => false,
                'uom_id-msg' => false,
                'uom_symbol' => '---'
            );
        }

        $this->currentItemArray['description'].= ' '.htmlspecialchars(preg_replace('/\s+/', ' ', trim($this->getDescription())));

        /* 
            For Now Normal Excel Import will determine Header
            And Item Structure Based on :

            1) First and current Header Structure
            Unit & Item Indicate Item else is Header

            2) Only Work Item type available

            3) Only Standard Header Available

            4) No Bill Reference

            Later to implement : 

            1) Can detect Based on Tag Input
            2) Can detect Based on Cell Style
        */

        $amount = ($amountCoord) ? $this->currentSheet
            ->getCell($amountCoord)
            ->getCalculatedValue() : 0;

        $billRef = ($itemCoord) ? $this->currentSheet
            ->getCell($itemCoord)
            ->getCalculatedValue() : null;

        $uom = ($unitCoord) ? $this->getValidatedUOM($unitCoord) : null;

        if($uom && !$uom['is_empty'])
        {
            $this->currentItemArray['uom_id'] = $uom['id'];
            $this->currentItemArray['uom_symbol'] = $uom['symbol'];
            $this->currentItemArray['uom_id-has_error'] = $uom['has_error'];
            $this->currentItemArray['uom_id-msg'] = $uom['msg'];

            if ( isset($uom['new_symbol']) )
            {
                $this->currentItemArray['new_symbol'] = $uom['new_symbol'];
            }
        }

        $qtyPerUnit = ($qtyCoord) ? $this->getValidatedQty($qtyCoord) : null;

        if($qtyPerUnit && !$qtyPerUnit['is_empty'])
        {
            $this->currentItemArray['quantity_per_unit-final_value'] = $qtyPerUnit['value'];
            $this->currentItemArray['quantity_per_unit-value'] = $qtyPerUnit['value'];
            $this->currentItemArray['quantity_per_unit-has_error'] = $qtyPerUnit['has_error'];
            $this->currentItemArray['quantity_per_unit-msg'] = $qtyPerUnit['msg'];
        }

        $rate = ($rateCoord) ? $this->getValidatedRate($rateCoord) : null;

        if($rate && !$rate['is_empty'])
        {
            $this->currentItemArray['rate-final_value'] = $rate['value'];
            $this->currentItemArray['rate-value'] = $rate['value'];
            $this->currentItemArray['rate-has_error'] = $rate['has_error'];
            $this->currentItemArray['rate-msg'] = $rate['msg'];
        }

        if(!$this->isEmpty($amount))
        {
            $this->currentItemArray['amount'] = $amount;
        }

        if(!$this->isEmpty($billRef))
        {
            $this->currentItemArray['bill_ref'] = $billRef;
        }
        
    }

    public function saveCurrentItem()
    {
        if(!(count($this->currentItemArray) > 0))
            return;

        $item = $this->currentItemArray;

        $billRef = $item['bill_ref'];
        $description = $item['description'];
        $uomId = $item['uom_id'];


        //Save First Description found as trade name
        if($description != null && $description != '')
        {
            if(!$this->data[$this->currentTradeId]['description'] || $this->data[$this->currentTradeId]['description'] == null || $this->data[$this->currentTradeId]['description'] == '')
            {
                $this->data[$this->currentTradeId]['description'] = $description;

                $trade = array(
                    'id' => $this->data[$this->currentTradeId]['id'],
                    'rowType' => parent::ROW_TYPE_ELEMENT_TEXT,
                    'description' => $this->data[$this->currentTradeId]['description']
                );

                if($this->generateXML)
                    $this->sfExportXML->addTradeChildren($trade);
            }
                
        }

        //Before Save item Assign Root Id and Level
        if(($uomId != null && $uomId != '') && ($description != null && $description != ''))
        {
            $item['type'] = (string) BillItem::TYPE_WORK_ITEM;

            $levelInfo = $this->getCurrentItemRootAndLevel();
            $item['root_id'] = $levelInfo['rootId'];
            $item['level'] = $levelInfo['level'];
        }
        else
        {
            //If None of the Above then this item is Head
            $levelInfo = $this->getCurrentHeaderRootAndLevel();

            $item['uom_id'] = -1;

            $item['type'] = (string) BillItem::TYPE_HEADER;
            $item['root_id'] = $levelInfo['rootId'];
            $item['level'] = $levelInfo['level'];
        }

        //save Item into Trade Array
        array_push($this->data[$this->currentTradeId]['_child'], $item);
        $this->tradeItemCount[$this->currentTradeId]+=1;

        //ifXML Enable create Item Children
        if($this->generateXML)
            $this->sfExportXML->addItemChildren($item);
    }
}
