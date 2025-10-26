<?php

class sfRFQImportExcel extends sfBuildspaceRFQExcelParser
{
    public $colItem        = "E";
    public $colDescription = "F";
    public $colQty         = "K";
    public $colUnit        = "L";
    public $colRate        = "F";
    public $colAmount      = "N";
    public $colItemType    = 'L';
    public $colElement     = 'B';
    public $colRfqItemId   = 'P';

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

    public function __construct( RFQ $rfq, RFQSupplier $supplier, $filename = null, $extension = null, $uploadPath = null, $deleteFile = null )
    {
        parent::__construct( $rfq, $supplier, $filename, $extension, $uploadPath, $deleteFile );
    }

    public function loadBook()
    {
        $this->automaticLoad(); //Set Book To Automatic Load Slower but works for BT2 & Buildsoft
    }

    public function setItem()
    {
        $itemType = $this->getItemType();

        $item = array(
            'id'      => $this->getRFQItemId(),
            'rowType' => self::ROW_TYPE_ITEM_TEXT,
        );

        switch(strtolower($itemType))
        {
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEADA_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEADB_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEADC_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEAD1_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEAD2_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEAD3_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_HEAD4_TEXT):
                //If Type Header
                $item['type'] = (string) BillItem::TYPE_HEADER;
                break;
            case strtolower(self::BUILDSOFT_ITEM_TYPE_NOID_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_NOIDI_TEXT):
            case strtolower(self::BUILDSOFT_ITEM_TYPE_NOIDN_TEXT):
                $item['type'] = (string) BillItem::TYPE_NOID;
                //case type NoID
                break;
            default:
                $rate = $this->getValidatedRate();

                if ( ! $rate['is_empty'] )
                {
                    $item['rate-final_value'] = $rate['value'];
                    $item['rate-value']       = $rate['value'];
                    $item['rate-has_error']   = $rate['has_error'];
                    $item['rate-msg']         = $rate['msg'];
                }
                else
                {
                    $item['rate-final_value'] = 0;
                    $item['rate-value']       = 0;
                    $item['rate-has_error']   = 0;
                    $item['rate-msg']         = 0;
                }

                $item['type'] = (string) BillItem::TYPE_WORK_ITEM;

                $this->data['items'][] = $item;
                break;
        }

        unset($item);
    }
}
