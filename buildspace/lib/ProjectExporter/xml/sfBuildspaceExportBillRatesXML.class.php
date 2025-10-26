<?php
class sfBuildspaceExportBillRatesXML extends sfBuildspaceXMLGenerator
{
    public $xml;
    public $elements = false;
    public $items = false;
    public $currentElementChild;
    public $currentItemChild;
    public $billId;
    public $billColumnSettingUnits = array();

    protected $usedUnits = array();

    const TAG_BILL = "BILL";
    const TAG_ITEM = "item";
    const TAG_ITEMS = "ITEMS";
    const TAG_ELEMENTS = "ELEMENTS";
    const TAG_ITEM_LS_PERCENT = "LS_PERCENT";
    const TAG_ITEM_PRIME_COST = "PRIME_COST";
    const TAG_UNITOFMEASUREMENT = "UNITOFMEASUREMENT";
    const TAG_UNIT = "UNIT";
    const TAG_TYPEREFERENCES = "TYPEREFERENCES";
    const TAG_TYPE = "TYPE";
    const TAG_QTY = "QTY";

    function __construct( $filename = null, $uploadPath = null, $billId, $extension = null, $deleteFile = null )
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->billId = $billId;

        parent::__construct( $filename, $uploadPath, $extension, $deleteFile );
    }

    public function process( $billData = array(), $write = true )
    {
        parent::create( self::TAG_BILL, array('buildspaceId'=>sfConfig::get('app_register_buildspace_id'), 'billId' => $this->billId) );

        if ( array_key_exists('elementsAndItems', $billData))
        {
            $this->processElementAndItems($billData['elementsAndItems']);
        }

        $this->processBillColumnSettingUnits();

        if($write)
            parent::write();
    }

    public function processElementAndItems( $elementsAndItems )
    {
        if(count($elementsAndItems) > 0)
        {
            $this->createElementTag();

            $this->createItemTag();

            foreach( $elementsAndItems as $element )
            {
                $items = $element['items'];

                unset($element['items']);

                $this->addElementChildren( $element );

                if ( count($items) )
                {
                    $this->processItems($items);
                }

            }
        }

        $this->processUnits();
    }

    public function processItems($items)
    {
        if(count($items) > 0)
        {
            foreach($items as $item)
            {
                if(!($item['type'] == BillItem::TYPE_ITEM_NOT_LISTED && $item['uom_id'] == '' && $item['description'] == '' && $item['grand_total'] == 0))
                {
                    if(array_key_exists('origin_id', $item))
                    {
                        $originalItemId = $item['origin_id'];

                        unset($item['origin_id']);
                    }
                    else
                    {
                        $originalItemId = $item['id'];
                    }

                    $lumpSumpPercent = false;

                    $primeCost = false;

                    $newItem = false;

                    $typeRefs = false;

                    switch($item['type'])
                    {
                        case BillItem::TYPE_ITEM_LUMP_SUM_PERCENT:
                            $lumpSumpPercent = $this->getItemLumpSumPercentage($originalItemId);
                            break;
                        case BillItem::TYPE_ITEM_PC_RATE:
                            $primeCost = $this->getItemPrimeCost($originalItemId);
                            break;
                        case BillItem::TYPE_ITEM_NOT_LISTED:
                            if($item['uom_id'] && !array_key_exists($item['uom_id'], $this->usedUnits))
                            {
                                $this->usedUnits[$item['uom_id']] = $this->getUomById($item['uom_id']);
                            }

                            $typeRefs = $this->getItemTypeRef( $originalItemId );

                            $newItem = true;
                            break;
                        default:
                            break;

                    }

                    if(!$newItem)
                    {
                        unset($item['description']);
                        unset($item['uom_id']);
                    }

                    $this->addItemChildren($item);

                    if($lumpSumpPercent)
                    {
                        $this->addLumpSumpPercentChild( $lumpSumpPercent );
                    }

                    if($typeRefs)
                    {
                        $this->processTypeRef($typeRefs);
                    }

                    if($primeCost)
                    {
                        $this->addPrimeCostChild( $primeCost );
                    }
                }
            }
        }
    }

    public function getItemTypeRef($itemId)
    {
        $query = DoctrineQuery::create()->select('type.id, type.bill_item_id, type.bill_column_setting_id, , i.tender_origin_id, c.tender_origin_id, type_fc.id, type_fc.relation_id, type_fc.column_name, type_fc.final_value')
            ->from('BillItemTypeReference type')
            ->leftJoin('type.FormulatedColumns type_fc')
            ->leftJoin('type.BillItem i')
            ->leftJoin('type.BillColumnSetting c')
            ->where('type.bill_item_id = ?', $itemId)
            ->andWhere('type_fc.column_name = ? ', BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT)
            ->andWhere('type.include IS TRUE')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

        return $typeRefs = $query->execute();
    }

    public function processUnits()
    {
        if(!count($this->usedUnits))
            return false;

        $this->createUnitOfMeasurementTag();

        foreach($this->usedUnits as $unit)
        {
            $this->addUnitChildren($unit);
        }
    }

    public function processTypeRef( $typeRefs )
    {
        $this->createTypeRefTag();

        foreach($typeRefs as $typeRef)
        {
            $typeFc = array();

            if((array_key_exists('FormulatedColumns', $typeRef)) && count($typeRef['FormulatedColumns'] > 0))
            {
                $typeFc = $typeRef['FormulatedColumns'];

                unset($typeRef['FormulatedColumns']);
            }

            if(array_key_exists('BillColumnSetting', $typeRef) && count($typeRef['BillColumnSetting']))
            {
                $arrayOfIds = ProjectStructureTable::extractOriginId($typeRef['BillColumnSetting']['tender_origin_id']);

                $billColumnSettingOriginalId = $arrayOfIds['origin_id'];

                $typeRef['bill_column_setting_id'] = $billColumnSettingOriginalId;

                unset($typeRef['BillColumnSetting']);
            }

            if(array_key_exists('BillItem', $typeRef) && count($typeRef['BillItem']))
            {
                $arrayOfIds = ProjectStructureTable::extractOriginId($typeRef['BillItem']['tender_origin_id']);

                $billItemOriginalId = $arrayOfIds['origin_id'];

                $typeRef['bill_item_id'] = $billItemOriginalId;

                unset($typeRef['BillItem']);
            }

            $this->addTypeRefChildren( $typeRef );

            $count = 0;

            foreach($typeFc as $fc)
            {
                $this->createQtyTag( $fc, $count );

                $count++;
            }
        }
    }

    public function getUomById($uomId)
    {
        $sql = "SELECT  uom.id, uom.name, uom.symbol, uom.type FROM ".UnitOfMeasurementTable::getInstance()->getTableName()." uom
        WHERE uom.id = :uom_id AND uom.deleted_at IS NULL";

        $params = array(
            'uom_id' => $uomId
        );

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return $uom = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItemLumpSumPercentage($itemId )
    {
        $sql = "SELECT  ls.rate, ls.percentage, ls.amount FROM ".BillItemLumpSumPercentageTable::getInstance()->getTableName()." ls
        WHERE ls.bill_item_id = :item_id AND ls.deleted_at IS NULL";

        $params = array(
            'item_id' => $itemId
        );

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return $itemLumpSumPercentage = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItemPrimeCost( $itemId )
    {
        $sql = "SELECT pc.supply_rate, pc.wastage_percentage, pc.wastage_amount, pc.labour_for_installation, pc.other_cost,
        pc.profit_percentage, pc.profit_amount, pc.total FROM ".BillItemPrimeCostRateTable::getInstance()->getTableName()." pc
        WHERE pc.bill_item_id = :item_id AND pc.deleted_at IS NULL";

        $params = array(
            'item_id' => $itemId
        );

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return $itemPrimeCost = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createTypeRefTag()
    {
        $this->currentTypeRefTag = parent::addChildTag( $this->currentItemChild, self::TAG_TYPEREFERENCES );
    }

    public function addTypeRefChildren( $fieldAndValues )
    {
        $this->currentTypeRefChildTag = parent::addChildTag( $this->currentTypeRefTag, self::TAG_TYPE, $fieldAndValues );
    }

    public function createQtyTag( $fieldAndValues, $count = false )
    {
        $tagName = ($count) ? self::TAG_QTY.'_'.$count : self::TAG_QTY;

        $this->currentQtyTag = parent::addChildTag( $this->currentTypeRefChildTag, $tagName , $fieldAndValues );
    }

    public function createElementTag()
    {
        $this->elements = parent::createTag( self::TAG_ELEMENTS );
    }

    public function addElementChildren( $fieldAndValues )
    {
        $this->currentElementChild = parent::addChildTag( $this->elements, self::TAG_ITEM, $fieldAndValues );
    }

    public function createItemTag()
    {
        $this->items = parent::createTag( self::TAG_ITEMS );
    }

    public function addItemChildren( $fieldAndValues )
    {
        $this->currentItemChild = parent::addChildTag( $this->items, self::TAG_ITEM, $fieldAndValues );
    }

    public function addLumpSumpPercentChild( $fieldAndValues )
    {
        return parent::addChildTag( $this->currentItemChild, self::TAG_ITEM_LS_PERCENT, $fieldAndValues );
    }

    public function addPrimeCostChild( $fieldAndValues )
    {
        return parent::addChildTag( $this->currentItemChild, self::TAG_ITEM_PRIME_COST, $fieldAndValues );
    }

    public function createUnitOfMeasurementTag()
    {
        $this->units = parent::createTag( self::TAG_UNITOFMEASUREMENT );
    }

    public function addUnitChildren( $fieldAndValues )
    {
        $this->currentUnitChildTag = parent::addChildTag( $this->units, self::TAG_UNIT, $fieldAndValues );
    }

    public function specifyBillColumnSettingUnits(array $units)
    {
        $this->billColumnSettingUnits = $units;
    }

    protected function processBillColumnSettingUnits()
    {
        if( ! ( count($this->billColumnSettingUnits) > 0 ) ) return;

        $billColumnSettingTag = parent::addChildTag($this->xml, sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTING, array());

        foreach($this->billColumnSettingUnits as $billColumnSettingId => $info)
        {
            $columnTag = parent::addChildTag($billColumnSettingTag, sfBuildspaceExportBillXML::TAG_COLUMN, array('id' => $billColumnSettingId, 'tender_origin_id' => $info['tender_origin_id']));

            $counterTag = parent::addChildTag($columnTag, sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTINGUNITS, array());

            foreach($info['billColumnSettingUnits'] as $unitNumber)
            {
                $counterTag->addChild(self::TAG_UNIT, $unitNumber);
            }
        }
    }

}
