<?php
class sfBuildspaceExportBillXML extends sfBuildspaceXMLGenerator
{
    public $xml;
    public $elements = false;
    public $items = false;
    public $units = false;
    public $currentElementChild;
    public $currentItemChild;
    public $billColumnSettings;
    public $columnName = array();
    public $usedUnits = array();
    public $billId;
    public $exportElementsAndItems;
    public $billLayoutSetting;
    public $billType;
    protected $billMarkupSetting = false;

    const TAG_BILL = "BILL";
    const TAG_ITEM = "item";
    const TAG_ITEMS = "ITEMS";
    const TAG_ELEMENTS = "ELEMENTS";
    const TAG_BILLSETTING = "BILLSETTING";
    const TAG_BILLCOLUMNSETTING = "BILLCOLUMNSETTING";
    const TAG_COLUMN = "COLUMN";
    const TAG_BILLCOLUMNSETTINGUNITS = "BILLCOLUMNSETTINGUNITS";
    const TAG_LAYOUTSETTING = "LAYOUTSETTING";
    const TAG_PHRASE = "PHRASE";
    const TAG_HEADSETTING = "HEADSETTING";
    const TAG_BILLTYPE = "BILLTYPE";
    const TAG_TYPEREFERENCES = "TYPEREFERENCES";
    const TAG_TYPE = "TYPE";
    const TAG_QTY = "QTY";
    const TAG_UNITOFMEASUREMENT = "UNITOFMEASUREMENT";
    const TAG_UNIT = "UNIT";
    const TAG_ITEM_LS_PERCENT = "LS_PERCENT";
    const TAG_ITEM_PC_RATE = "PC_RATE";
    const TAG_RATES = "RATES";
    const TAG_RATE = "RATE";
    const TAG_BILLPAGES = "BILLPAGES";
    const TAG_BILLPAGE = "BILLPAGE";
    const TAG_BILLPAGEITEMS = "BILLPAGEITEMS";
    const TAG_COLLECTIONPAGES = "COLLECTIONPAGES";


    function __construct( $filename = null, $uploadPath = null, $billId, $extension = null, $deleteFile = false )
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->billId = $billId;

        parent::__construct( $filename, $uploadPath, $extension, $deleteFile );
    }

    public function process( $billData = array(), $exportElementsAndItems = true, $write = true )
    {
        parent::create( self::TAG_BILL, array('buildspaceId'=>sfConfig::get('app_register_buildspace_id'), 'billId' => $this->billId) );

        if ( array_key_exists('billSetting', $billData) && count($billData['billSetting']) > 0 )
        {
            parent::addChildren(parent::createTag( self::TAG_BILLSETTING ), $billData['billSetting']);
        }

        if ( array_key_exists('billColumnSettings', $billData) && count($billData['billColumnSettings']) > 0 )
        {
            $this->billColumnSettings = parent::createTag( self::TAG_BILLCOLUMNSETTING );

            foreach($billData['billColumnSettings'] as $column)
            {
                $this->columnName[$column['id']] = ($column['use_original_quantity']) ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

                $columnTag = parent::addChildTag( $this->billColumnSettings, self::TAG_COLUMN, $column );

                if( array_key_exists('counters', $column) && count($column['counters']) > 0 )
                {
                    $counterTag = parent::addChildTag($columnTag, self::TAG_BILLCOLUMNSETTINGUNITS, array());

                    foreach($column['counters'] as $key => $counter)
                    {
                        $counterTag->addChild(self::TAG_UNIT, $counter);
                    }
                }
            }
        }

        if ( array_key_exists('billMarkupSetting', $billData))
        {
            $this->billMarkupSetting = $billData['billMarkupSetting'];
        }

        $this->exportElementsAndItems = $exportElementsAndItems;

        if ( array_key_exists('billType', $billData) && count($billData['billType']) > 0 )
        {
            $this->billType = $billData['billType']['type'];

            parent::addChildren(parent::createTag( self::TAG_BILLTYPE ), $billData['billType']);
        }

        if ( array_key_exists('billLayoutSetting', $billData) && count($billData['billLayoutSetting']) > 0 )
        {
            $this->processBillLayoutSetting($billData['billLayoutSetting']);
        }

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

                //Process Bill Pages
                if ( array_key_exists('billPages', $element))
                {
                    $billPages = $element['billPages'];

                    unset($element['billPages']);
                }
                else
                {
                    $billPages = false;
                }

                //Process Bill Collection Pages
                if ( array_key_exists('collectionPages', $element))
                {
                    $collectionPages = $element['collectionPages'];

                    unset($element['collectionPages']);
                }
                else
                {
                    $collectionPages = false;
                }

                $this->addElementChildren( $element );

                if ( count($items) > 0 )
                {
                    if($this->exportElementsAndItems)
                    {
                        $this->processItems($items);
                    }
                    else
                    {
                        $this->exportRates($items);
                    }   
                }

                if ($billPages && count($billPages) )
                {
                    $this->processBillPages($billPages);

                    unset($billPages);
                }

                if ($collectionPages && count($collectionPages))
                {
                    $this->processCollectionPages($collectionPages);

                    unset($collectionPages);
                }
                    
            }
        }

        $this->processUnits();
        
    }

    public function exportRates( $items )
    {
        if(is_array($items))
        {
            foreach($items as $item)
            {   
                $this->addItemChildren($item);
            }
        }
    }

    public function processItems($items)
    {
        if(count($items) > 0)
        {
            foreach($items as $item)
            {
                $uom = (array_key_exists('UnitOfMeasurement', $item)) ? true : false;

                if($uom)
                {
                    $uom = $item['UnitOfMeasurement'];

                    if($uom['id'] && !array_key_exists($uom['id'], $this->usedUnits))
                    {
                        $this->usedUnits[$uom['id']] = $uom;
                    }

                    unset($item['UnitOfMeasurement']);
                }

                $lumpSumpPercent = false;
                $primeCostRate = false;
                $rate = false;
                $typeRefGrandTotal = false;

                //CheckItemType
                switch($item['type'])
                {
                    case BillItem::TYPE_ITEM_PC_RATE:
                        if(array_key_exists('PrimeCostRate', $item))
                        {
                            $primeCostRate = $item['PrimeCostRate'];

                            $primeCostRate['total'] = $primeCostRate['supply_rate'];

                            $grandTotalType = 0;

                            if($item['BillItemTypeReferences'])
                            {
                                foreach($item['BillItemTypeReferences'] as $type)
                                {
                                    if(isset($type['FormulatedColumns'][0]))
                                    {
                                        $totalPerUnit = number_format($type['FormulatedColumns'][0]['final_value'] * $item['PrimeCostRate']['supply_rate'], 2,'.','');
                                    }
                                    else
                                    {
                                        $totalPerUnit = 0;
                                    }

                                    foreach($this->billColumnSettings as $column)
                                    {
                                        if((int) $column->id == $type['bill_column_setting_id'])
                                        {
                                            $grandTotalType += (float)number_format($totalPerUnit * (int) $column->quantity, 2,'.','');
                                        }

                                        unset($column);
                                    }

                                    unset($type);
                                }
                            }

                            $item['grand_total_after_markup'] = $item['grand_total'] = $grandTotalType;

                            $rate = array(
                                'relation_id' => $item['id'],
                                'value'       => $item['PrimeCostRate']['supply_rate'],
                                'final_value' => $item['PrimeCostRate']['supply_rate'],
                                'column_name' => BillItem::FORMULATED_COLUMN_RATE
                            );

                            unset($item['PrimeCostRate']);
                        }

                        break;
                    case BillItem::TYPE_ITEM_LUMP_SUM_PERCENT:

                        if(array_key_exists('LumpSumPercentage', $item))
                        {
                            $lumpSumpPercent = $item['LumpSumPercentage'];
                            unset($item['LumpSumPercentage']);
                        }

                        if(array_key_exists('grand_total_after_markup', $item))
                        {
                            unset($item['grand_total_after_markup']);
                        }

                        break;
                    case BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE:
                        //Do nothing for now
                        if(array_key_exists('grand_total_after_markup', $item))
                        {
                            $item['grand_total'] = $item['grand_total_after_markup'];

                            $typeRefGrandTotal = true;

                            $finalRates = $this->getRatesByItemId($item['id']);
                            
                            $rate = array(
                                'relation_id' => $item['id'],
                                'value' => $finalRates,
                                'final_value' => $finalRates,
                                'column_name' => BillItem::FORMULATED_COLUMN_RATE
                            );
                            
                        }

                        if(array_key_exists('LumpSumPercentage', $item))
                        {
                            unset($item['LumpSumPercentage']);
                        }
                        break;
                    case BillItem::TYPE_ITEM_NOT_LISTED:

                        if(array_key_exists('grand_total_after_markup', $item))
                        {
                            unset($item['grand_total_after_markup']);
                        }

                        if(array_key_exists('LumpSumPercentage', $item))
                        {
                            unset($item['LumpSumPercentage']);
                        }

                        if(array_key_exists('uom_id', $item))
                        {
                            unset($item['uom_id']);
                        }

                        if(array_key_exists('BillItemTypeReferences', $item))
                        {
                            unset($item['BillItemTypeReferences']);
                        }
                        break;
                    default:
                        if(array_key_exists('grand_total_after_markup', $item))
                        {
                            unset($item['grand_total_after_markup']);
                        }

                        if(array_key_exists('LumpSumPercentage', $item))
                        {
                            unset($item['LumpSumPercentage']);
                        }
                        break;
                }

                $typeRefs = (array_key_exists('BillItemTypeReferences', $item)) ? true : false;

                if($typeRefs && count($item['BillItemTypeReferences'] > 0))
                {
                    $typeRefs = $item['BillItemTypeReferences'];

                    unset($item['BillItemTypeReferences']);
                }
                else
                {
                    $typeRefs = false;
                }
                
                $this->addItemChildren($item);

                if($lumpSumpPercent)
                {
                    $this->addLumpSumpPercentChild( $lumpSumpPercent );
                }

                if($primeCostRate)
                {
                    $this->addPrimeCostRateChild( $primeCostRate );
                }

                if($rate && count($rate))
                {
                    $this->addRateChild( $rate );
                }

                if($typeRefs)
                {
                    $this->processTypeRef($typeRefs, $typeRefGrandTotal);
                }
            }
        }
    }

    public function processBillPages( $billPages )
    {
        $pageNode = parent::addChildTag( $this->currentElementChild, self::TAG_BILLPAGES );

        foreach($billPages as $page)
        {
            //Process Bill Page Item
            if ( array_key_exists('Items', $page))
            {
                $pageItems = $page['Items'];

                unset($page['Items']);
            }
            else
            {
                $pageItems = false;
            }

            $pageChildNode = parent::addChildTag( $pageNode, self::TAG_ITEM, $page );

            if(count($pageItems))
            {
                foreach($pageItems as $item)
                {
                    $billPageNode = parent::addChildTag( $pageChildNode, self::TAG_BILLPAGE );

                    parent::addChildTag( $billPageNode, self::TAG_ITEM, $item );
                }
            }
        }
    }

    public function processCollectionPages( $collectionPages )
    {
        $collectionNode = parent::addChildTag( $this->currentElementChild, self::TAG_COLLECTIONPAGES );

        foreach($collectionPages as $page)
        {
            parent::addChildTag( $collectionNode, self::TAG_ITEM, $page );
        }
    }

    public function getTypeRefGrandTotalById($typeRefId)
    {
        $stmt = $this->pdo->prepare("SELECT type.id, type.grand_total_after_markup AS grand_total, type.grand_total_after_markup AS grand_total_after_markup FROM ".BillItemTypeReferenceTable::getInstance()->getTableName()." type
        WHERE type.id = :type_id AND type.deleted_at IS NULL");

        $stmt->execute(array(
            'type_id' => $typeRefId
        ));

        return $typeRef = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function processTypeRef( $typeRefs , $typeRefGrandTotal = false)
    {
        $this->createTypeRefTag();

        foreach($typeRefs as $typeRef)
        {
            $typeFc = (array_key_exists('FormulatedColumns', $typeRef)) ? true : false;

            if($typeRefGrandTotal)
            {
                $typeRefTotals = $this->getTypeRefGrandTotalById($typeRef['id']);
                
                if($typeRefTotals)
                {
                    $typeRef['grand_total'] = $typeRefTotals['grand_total'];
                    $typeRef['grand_total_after_markup'] = $typeRefTotals['grand_total_after_markup'];
                }
            }

            if($typeFc && count($typeRef['FormulatedColumns'] > 0))
            {
                $typeFc = $typeRef['FormulatedColumns'];

                unset($typeRef['FormulatedColumns']);
            }
            else
            {
                $typeFc = false;
            }

            $this->addTypeRefChildren( $typeRef );

            $columnName = (array_key_exists($typeRef['bill_column_setting_id'], $this->columnName)) ? $this->columnName[$typeRef['bill_column_setting_id']] : null;

            $count = 0;
            
            foreach($typeFc as $fc)
            {
                if($fc['column_name'] == $columnName)
                {
                    $this->createQtyTag( $fc, $count );

                    $count++;
                }
            }
        }
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

    public function processBillLayoutSetting( $billLayoutSetting )
    {
        $this->createLayoutSettingTag();

        $billPhrase = (array_key_exists('BillPhrase', $billLayoutSetting)) ? true : false;

        if($billPhrase)
        {
            $billPhrase = $billLayoutSetting['BillPhrase'];

            unset($billLayoutSetting['BillPhrase']);
        }
        else
        {
            $billPhrase = false;
        }


        $headSetting = (array_key_exists('BillHeadSettings', $billLayoutSetting)) ? true : false;

        if($headSetting && count($headSetting))
        {
            $headSetting = $billLayoutSetting['BillHeadSettings'];

            unset($billLayoutSetting['BillHeadSettings']);
        }
        else
        {
            $headSetting = false;
        }

        parent::addChildren($this->billLayoutSetting, $billLayoutSetting);

        if($billPhrase)
        {
            parent::addChildTag( $this->billLayoutSetting, self::TAG_PHRASE, $billPhrase );
        }

        if($headSetting)
        {
            $this->processHeadSetting($headSetting);
        }
    }

    public function processHeadSetting( $headSetting )
    {
        $headSettingNode = $this->createHeadSettingTag();

        foreach($headSetting as $head)
        {
            parent::addChildTag( $headSettingNode, self::TAG_ITEM, $head );
        }
    }

    public function getElementMarkupPercentageByElementId( $elementId )
    {
        $stmt = $this->pdo->prepare("SELECT efc.relation_id, COALESCE(efc.final_value,0) AS markup_percentage FROM ".BillElementFormulatedColumnTable::getInstance()->getTableName()." efc
        WHERE efc.relation_id = :element_id AND efc.deleted_at IS NULL AND efc.column_name = :markup_percent_column");

        $stmt->execute(array(
            'element_id' => $elementId,
            'markup_percent_column' => BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE
        ));

        $elementFormulatedColumn = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($elementFormulatedColumn) ? $elementFormulatedColumn['markup_percentage'] : false;
    }

    public function getRatesByItemId( $itemId )
    {
        $elementId = (int) $this->currentElementChild->id;

        $stmt = $this->pdo->prepare("SELECT ifc.relation_id, COALESCE(ifc.final_value,0) AS rate, COALESCE(markup.final_value,0) AS markup_percentage FROM ".BillItemFormulatedColumnTable::getInstance()->getTableName()." ifc
        LEFT JOIN ".BillItemFormulatedColumnTable::getInstance()->getTableName()." markup on markup.relation_id = ifc.relation_id AND markup.deleted_at IS NULL AND markup.column_name = :markup_percent_column
        WHERE ifc.relation_id = :item_id AND ifc.deleted_at IS NULL AND ifc.column_name = :rate_column");

        $stmt->execute(array(
            'item_id' => $itemId,
            'rate_column' => BillItem::FORMULATED_COLUMN_RATE,
            'markup_percent_column' => BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE
        ));

        $itemFormulatedColumn = $stmt->fetch(PDO::FETCH_ASSOC);

        if($itemFormulatedColumn)
        {
            $rate = $itemFormulatedColumn['rate'];

            if($this->billMarkupSetting['element_markup_enabled'])
            {
                $elementMarkupPercentage = $this->getElementMarkupPercentageByElementId($elementId);
            }
            else
            {
                $elementMarkupPercentage = 0;   
            }


            if($this->billMarkupSetting['item_markup_enabled'])
            {
                $itemMarkupPercentage = $itemFormulatedColumn['markup_percentage'];
            }
            else
            {
                $itemMarkupPercentage = 0;   
            }

            return $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup( $rate, $itemMarkupPercentage, array(
                'bill_markup_enabled'       => $this->billMarkupSetting['bill_markup_enabled'],
                'bill_markup_percentage'    => $this->billMarkupSetting['bill_markup_percentage'],
                'element_markup_enabled'    => $this->billMarkupSetting['element_markup_enabled'],
                'element_markup_percentage' => $elementMarkupPercentage,
                'item_markup_enabled'       => $this->billMarkupSetting['item_markup_enabled'],
                'rounding_type'             => $this->billMarkupSetting['rounding_type']
            ));
        }
        else
        {
            return 0;
        }
    }

    public function createBillXML( $elements = false, $items = false ) 
    {   
        //@deprecated: later to change to use constant
        parent::create( 'bill' );

        if ( $elements )
            $this->createElementTag();

        if ( $items )
            $this->createItemTag();
    }

    public function createElementTag() 
    {
        $this->elements = parent::createTag( self::TAG_ELEMENTS );
    }

    public function createLayoutSettingTag() 
    {
        $this->billLayoutSetting = parent::createTag( self::TAG_LAYOUTSETTING );
    }

    public function createHeadSettingTag()
    {
        return parent::addChildTag( $this->billLayoutSetting, self::TAG_HEADSETTING );
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

    public function addPrimeCostRateChild( $fieldAndValues ) 
    {
        return parent::addChildTag( $this->currentItemChild, self::TAG_ITEM_PC_RATE, $fieldAndValues );
    }

    public function addRateChild( $fieldAndValues ) 
    {
        return parent::addChildTag( $this->currentItemChild, self::TAG_RATES, $fieldAndValues );
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

    public function createUnitOfMeasurementTag() 
    {
        $this->units = parent::createTag( self::TAG_UNITOFMEASUREMENT );
    }

    public function addUnitChildren( $fieldAndValues )
    {
        $this->currentUnitChildTag = parent::addChildTag( $this->units, self::TAG_UNIT, $fieldAndValues );
    }

}
