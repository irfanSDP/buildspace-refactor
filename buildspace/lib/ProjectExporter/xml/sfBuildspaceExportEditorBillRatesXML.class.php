<?php
class sfBuildspaceExportEditorBillRatesXML extends sfBuildspaceXMLGenerator
{
    public $xml;
    public $elements = false;
    public $items = false;
    public $currentElementChild;
    public $currentItemChild;
    public $billId;

    protected $usedUnits = [];

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

    public function process( $billData = [], $write = true )
    {
        parent::create( self::TAG_BILL, array('buildspaceId'=>sfConfig::get('app_register_buildspace_id'), 'billId' => $this->billId) );

        if ( array_key_exists('elementsAndItems', $billData))
        {
            $this->processElementAndItems($billData['elementsAndItems']);
        }

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
                $billInfoId = $item['bill_info_id'];

                unset($item['bill_info_id']);

                $lumpSumpPercent = false;

                $primeCost = false;

                $newItem = false;

                $typeRefs = false;

                switch($item['type'])
                {
                    case BillItem::TYPE_ITEM_LUMP_SUM_PERCENT:
                        $lumpSumpPercent = $this->getItemLumpSumPercentage($billInfoId);
                        break;
                    case BillItem::TYPE_ITEM_PC_RATE:
                        $primeCost = $this->getItemPrimeCost($billInfoId);
                        break;
                    case BillItem::TYPE_ITEM_NOT_LISTED:
                        if($item['uom_id'] && !array_key_exists($item['uom_id'], $this->usedUnits))
                        {
                            $this->usedUnits[$item['uom_id']] = $this->getUomById($item['uom_id']);
                        }

                        $typeRefs = $this->getItemTypeRef( $billInfoId );
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

    public function getItemTypeRef($itemInfoId)
    {
        $stmt = $this->pdo->prepare("SELECT type_ref.id, type_ref.bill_item_id, type_ref.bill_column_setting_id, item.id AS tender_origin_id,
            type_ref.id AS relation_id, type.quantity_per_unit As final_value
            FROM ".BillItemTypeReferenceTable::getInstance()->getTableName()." AS type_ref
            JOIN " . EditorBillItemTypeReferenceTable::getInstance()->getTableName() . " AS type ON type_ref.bill_column_setting_id = type.bill_column_setting_id
            JOIN " . EditorBillItemInfoTable::getInstance()->getTableName() . " AS info ON type.bill_item_info_id = info.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " AS item ON info.bill_item_id = item.id AND type_ref.bill_item_id = item.id
            WHERE info.id = :bill_item_info_id  AND item.project_revision_deleted_at IS NULL
            AND item.type <> ".BillItem::TYPE_HEADER." AND item.type <> ".BillItem::TYPE_NOID." AND item.type <> ".BillItem::TYPE_HEADER_N."
            AND type_ref.include IS TRUE AND type_ref.deleted_at IS NULL
            AND item.deleted_at IS NULL ORDER BY info.id");

        $stmt->execute([
            'bill_item_info_id' => $itemInfoId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function processTypeRef( $typeRefs )
    {
        $this->createTypeRefTag();

        foreach($typeRefs as $typeRef)
        {
            $this->addTypeRefChildren( $typeRef );

            $this->createQtyTag([
                'relation_id' => $typeRef['relation_id'],
                'column_name' => BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT,
                'final_value' => $typeRef['final_value']
            ], 0 );
        }
    }

    public function getItemLumpSumPercentage($itemInfoId )
    {
        $stmt = $this->pdo->prepare("SELECT  ls.rate, ls.percentage, ls.amount
            FROM ".EditorBillItemLumpSumPercentageTable::getInstance()->getTableName()." ls
            WHERE ls.bill_item_info_id = :bill_item_info_id"
        );

        $stmt->execute([
            'bill_item_info_id' => $itemInfoId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItemPrimeCost( $itemInfoId )
    {
        $stmt = $this->pdo->prepare("SELECT pc.supply_rate, pc.wastage_percentage, pc.wastage_amount, pc.labour_for_installation, pc.other_cost,
            pc.profit_percentage, pc.profit_amount, pc.total
            FROM ".EditorBillItemPrimeCostRateTable::getInstance()->getTableName()." pc
            WHERE pc.bill_item_info_id = :bill_item_info_id"
        );

        $stmt->execute([
            'bill_item_info_id' => $itemInfoId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUomById($uomId)
    {
        $stmt = $this->pdo->prepare("SELECT  uom.id, uom.name, uom.symbol, uom.type
            FROM ".UnitOfMeasurementTable::getInstance()->getTableName()." uom
            WHERE uom.id = :uom_id AND uom.deleted_at IS NULL");

        $stmt->execute([
            'uom_id' => $uomId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
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
}
