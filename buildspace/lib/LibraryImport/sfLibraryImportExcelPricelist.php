<?php
class sfLibraryImportExcelPricelist extends sfBuildspaceLibraryExcelParser
{
    public $colItem = "E";
    public $colDescription = "Q";
    public $colUnit = "S";
    public $colRate = "T";
    public $colItemType = 'S';
    public $colTrade = 'N';

    const BUILDSOFT_ITEM_TYPE_HEAD1 = 1;
    const BUILDSOFT_ITEM_TYPE_HEAD2 = 2;
    const BUILDSOFT_ITEM_TYPE_HEAD3 = 4;
    const BUILDSOFT_ITEM_TYPE_NOID = 8;
    const BUILDSOFT_ITEM_TYPE_NOIDI = 16;
    const BUILDSOFT_ITEM_TYPE_NOIDN = 32 ;
    const BUILDSOFT_ITEM_TYPE_NOTE = 64 ;
    const BUILDSOFT_ITEM_TYPE_HEAD1_TEXT = "HEAD1";
    const BUILDSOFT_ITEM_TYPE_HEAD2_TEXT = "HEAD2";
    const BUILDSOFT_ITEM_TYPE_HEAD3_TEXT = "HEAD3";
    const BUILDSOFT_ITEM_TYPE_NOID_TEXT = "NOID";
    const BUILDSOFT_ITEM_TYPE_NOIDI_TEXT = "NOIDI";
    const BUILDSOFT_ITEM_TYPE_NOIDN_TEXT = "NOIDN";
    const BUILDSOFT_ITEM_TYPE_NOTE_TEXT = "NOTE";

    function __construct( $filename = null, $extension = null, $uploadPath = null, $generateXML = null, $deleteFile = null ) {
        parent::__construct( $filename, $extension, $uploadPath, $generateXML, $deleteFile );
    }

    public function determineExcelType($excelType = null){
        $this->excelType = self::EXCEL_TYPE_SINGLE; //for now defaulted to single
    }

    public function loadBook(){
        $this->automaticLoad(); //Set Book To Automatic Load Slower but works for BT2 & Buildsoft
    }

    public function setItem(){
        $this->newItem();

        $itemType = $this->getItemType();

        $item = array(
            'id' => $this->currentItemId,
            'rowType' => self::ROW_TYPE_ITEM_TEXT,
            'tradeId' => $this->currentTradeId,
            'rate-has_build_up' => false,
            'rate-has_cell_reference' => false,
            'rate-has_formula' => false,
            'rate-linked' => false,
            'quantity_per_unit-value' => '',
            'quantity_per_unit-final_value' => '',
            'quantity_per_unit-has_build_up' => false,
            'quantity_per_unit-has_cell_reference' => false,
            'quantity_per_unit-has_formula' => false,
            'quantity_per_unit-linked' => false,
            'rate-final_value' => 0,
            'rate-value' => '',
            'amount' => '',
            'uom_id' => null,
            'uom_symbol' => '---',
            'description' => htmlspecialchars(preg_replace('/\s+/', ' ', trim($this->getDescription())))
        );

        switch(strtolower($itemType)){
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEAD1_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEAD2_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEAD3_TEXT):
                //If Type Header
                $item['type'] = (string) BillItem::TYPE_HEADER;

                $levelInfo = $this->getHeaderRootAndLevel($itemType);
                $item['root_id'] = $levelInfo['rootId'];
                $item['level'] = $levelInfo['lvl'];
                break;
            case strtolower(self::BUILDSOFT_ITEM_TYPE_NOID_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_NOTE_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_NOIDI_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_NOIDN_TEXT):
                $item['type'] = (string) BillItem::TYPE_NOID;

                $levelInfo = $this->getItemRootAndLevel();
                $item['root_id'] = $levelInfo['rootId'];
                $item['level'] = $levelInfo['lvl'];
                //case type NoID
                break;
            default:
                $uom = $this->getValidatedUOM();
                if(!$uom['is_empty']){
                    $item['uom_id'] = $uom['id'];
                    $item['uom_symbol'] = $uom['symbol'];
                    $item['uom_id-has_error'] = $uom['has_error'];
                    $item['uom_id-msg'] = $uom['msg'];

                    if ( isset($uom['new_symbol']) )
                    {
                        $item['new_symbol'] = $uom['new_symbol'];
                    }
                }

                $rate = $this->getValidatedRate();
                if(!$rate['is_empty']){
                    $item['rate-final_value'] = $rate['value'];
                    $item['rate-value'] = $rate['value'];
                    $item['rate-has_error'] = $rate['has_error'];
                    $item['rate-msg'] = $rate['msg'];
                }

                $item['type'] = (string) BillItem::TYPE_WORK_ITEM;
                $item['amount'] = '';

                $levelInfo = $this->getItemRootAndLevel();
                $item['root_id'] = $levelInfo['rootId'];
                $item['level'] = $levelInfo['lvl'];

                break;
        }

        //ifXML Enable create Item Children
        if($this->generateXML)
            $this->sfExportXML->addItemChildren($item);

        $this->tradeItemCount[$this->currentTradeId]+=1;

        array_push($this->data[$this->currentTradeId]['_child'], $item);
    }

    public function setTrade(){
        //Setup Trade Coordinate
        $tradeCoord = $this->colTrade.$this->currentRow;

        //Read Current Row Trade Description
        $tradeDesc = $this->currentSheet->getCell($tradeCoord)->getCalculatedValue();

        if($tradeDesc == $this->currentTradeDescription)
            return;

        $this->newTrade();

        $this->tradeItemCount[$this->currentTradeId] = 0;
        
        $trade = array(
            'id' => $this->currentTradeId,
            'rowType' => parent::ROW_TYPE_ELEMENT_TEXT,
            'description' => htmlspecialchars ($tradeDesc)
        );

        //ifXML Enable create Trade Item
        if($this->generateXML)
            $this->sfExportXML->addTradeChildren($trade);

        $trade['_child'] = array(); //create Child Array for Trade

        $this->data[$this->currentTradeId] = $trade;
        $this->currentTradeDescription = $tradeDesc;
    }
}
