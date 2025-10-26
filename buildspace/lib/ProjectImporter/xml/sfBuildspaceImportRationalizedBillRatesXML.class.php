<?php
class sfBuildspaceImportRationalizedBillRatesXML extends sfBuildspaceXMLParser
{
    public $project;
    public $company;
    public $billId;
    public $userId;
    public $companyId;
    public $projectId;
    public $projectUniqueId;
    public $buildspaceId;
    public $unitIds = array();

    protected $billSetting;
    protected $elements;
    protected $elementIds;
    protected $billItemIds;
    protected $billColumnSettingIds;
    protected $items;
    protected $units;

    function __construct( $userId, $project, $filename = null, $uploadPath = null, $extension = null, $deleteFile = null ) 
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        
        $this->userId = $userId;

        $this->project = $project;

        parent::__construct( $filename, $uploadPath, $extension, $deleteFile );

        $this->extractData();
        
        if($project)
        {
            $this->projectId = $project['id'];
            
            $this->projectUniqueId = $project['MainInformation']['unique_id'];
        }
    }

    public function extractData()
    {
        parent::read();

        $xmlData = parent::getProcessedData();

        $this->buildspaceId = $xmlData->attributes()->buildspaceId;

        $this->billId = (int) $xmlData->attributes()->billId;

        $this->units = ($xmlData->{sfBuildspaceExportBillXML::TAG_UNITOFMEASUREMENT}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_UNITOFMEASUREMENT}->children() : false;

        $this->elements = ($xmlData->{sfBuildspaceExportBillXML::TAG_ELEMENTS}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_ELEMENTS}->children() : false;

        $this->items = ($xmlData->{sfBuildspaceExportBillXML::TAG_ITEMS}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_ITEMS}->children() : false;
        
    }

    public function process()
    {
        $this->getElementIds();
        $this->getItemIds();
        $this->getBillColumnSettingIds();

        if($this->elements)
        {
            $this->processElements();
        }

        if($this->units)
            $this->processUnits();

        if($this->items)
            $this->processItems();
    }

    public function processElements()
    {
    	foreach($this->elements as $element)
        {
            if($element->id && array_key_exists((int) $element->id, $this->elementIds))
            {
                $elementId = $this->elementIds[(int) $element->id];
				
				$data = new stdClass();

	            $data->bill_element_id = $elementId;
	            $data->grand_total = (float) $element->total_amount;
	            $data->created_at = 'NOW()';
	            $data->updated_at = 'NOW()';
	            $data->created_by = $this->userId;
	            $data->updated_by = $this->userId;
	
	            $dataAndStructure = parent::generateArrayOfSingleData( $data, true );
	
	            $stmt = new sfImportStatementGenerator();
	
	            $stmt->createInsert(TenderBillElementRationalizedGrandTotalTable::getInstance()->getTableName(), $dataAndStructure['structure']);
	
	            $stmt->addRecord($dataAndStructure['data']);
	
	            $stmt->save();
	
	            unset($data);
            }
			            
            unset($element);
        }

        unset($this->elements);
    }

    public function getElementIds()
    {
        $stmt = $this->pdo->prepare("SELECT e.tender_origin_id, e.id FROM ".BillElementTable::getInstance()->getTableName()." e
        LEFT JOIN ".ProjectStructureTable::getInstance()->getTableName()." p ON p.id = e.project_structure_id AND p.deleted_at IS NULL
        WHERE p.root_id = :project_id");

        $stmt->execute(array(
            'project_id' => $this->projectId
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($elements as $element)
        {
            $arrayOfIds = ProjectStructureTable::extractOriginId($element['tender_origin_id']);

            $this->elementIds[$arrayOfIds['origin_id']] = $element['id'];

            unset($element);
        }

        unset($elements);
    }

    public function processItems()
    {
        foreach($this->items as $item)
        {
            if($item->id && array_key_exists((int) $item->id, $this->billItemIds))
            {
                $itemId = $this->billItemIds[(int) $item->id];
                
				$grandTotal = (float) $item->grand_total;
	            $rate = (float) $item->rate;
	
	            unset($item->grand_total, $item->rate);
	
	            $itemNotListedId = false;
	
	            if((int) $item->type == BillItem::TYPE_ITEM_NOT_LISTED)
	            {
	                if(array_key_exists('type', $item))
	                {
	                    unset($item->type);
	                }
	                
	                $itemNotListedId = $this->processItemTypeNotListed( $item ); 
	            }
	
	            //Process Item LumpSumpPercent
	            $lumpSumpPercent = (array_key_exists(sfBuildspaceExportBillRatesXML::TAG_ITEM_LS_PERCENT, $item)) ? true : false;
	
	            if($lumpSumpPercent && ($item->type == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT))
	            {
	                $lumpSumpPercent = $item->{sfBuildspaceExportBillRatesXML::TAG_ITEM_LS_PERCENT}->children();
	            }
	            else
	            {
	                $lumpSumpPercent = false;
	            }
	
	            //Process Item LumpSumpPercent
	            $primeCostRate = (array_key_exists(sfBuildspaceExportBillRatesXML::TAG_ITEM_PRIME_COST, $item)) ? true : false;
	
	            if($primeCostRate && ($item->type == BillItem::TYPE_ITEM_PC_RATE))
	            {
	                $primeCostRate = $item->{sfBuildspaceExportBillRatesXML::TAG_ITEM_PRIME_COST}->children();
	            }
	            else
	            {
	                $primeCostRate = false;
	            }
	
	            $data = new stdClass();
	
	            if($itemNotListedId)
	            {
	                $data->tender_bill_not_listed_item_rationalized_id = $itemNotListedId;
	            }
	
	            $data->bill_item_id = $itemId;
	            $data->grand_total = $grandTotal;
	            $data->rate = $rate;
	            $data->created_at = 'NOW()';
	            $data->updated_at = 'NOW()';
	            $data->created_by = $this->userId;
	            $data->updated_by = $this->userId;
	
	            $dataAndStructure = parent::generateArrayOfSingleData( $data, true );
	
	            $stmt = new sfImportStatementGenerator();
	
	            $stmt->createInsert(TenderBillItemRationalizedRatesTable::getInstance()->getTableName(), $dataAndStructure['structure']);
	
	            $stmt->addRecord($dataAndStructure['data']);
	
	            $stmt->save();
	
	            $returningId = $stmt->returningIds[0];
	
	            if($lumpSumpPercent)
	            {
	                $this->processLumpSumpPercent($lumpSumpPercent, $returningId);
	                unset($lumpSumpPercent);
	            }
	
	            if($primeCostRate)
	            {
	                $this->processPrimeCostRate($primeCostRate, $returningId);
	                unset($primeCostRate);
	            }
	
	            unset($data);
            }
			
			unset($item);
        }
    }

    public function processLumpSumpPercent( $lumpSumpPercent, $tenderBillItemRationalizedRateId = false )
    {
        if($tenderBillItemRationalizedRateId)
        {
            $lumpSumpPercent->tender_bill_item_rationalized_rates_id = $tenderBillItemRationalizedRateId;
        }

        $lumpSumpPercent->created_at = 'NOW()';
        $lumpSumpPercent->updated_at = 'NOW()';
        $lumpSumpPercent->created_by = $this->userId;
        $lumpSumpPercent->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $lumpSumpPercent, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(TenderBillItemRationalizedLumpSumPercentageTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset($lumpSumpPercent);

        return true;
    }

    public function processPrimeCostRate( $primeCostRate, $tenderBillItemRationalizedRateId = false )
    {
        if($tenderBillItemRationalizedRateId)
        {
            $primeCostRate->tender_bill_item_rationalized_rates_id = $tenderBillItemRationalizedRateId;
        }

        $primeCostRate->created_at = 'NOW()';
        $primeCostRate->updated_at = 'NOW()';
        $primeCostRate->created_by = $this->userId;
        $primeCostRate->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $primeCostRate, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(TenderBillItemRationalizedPrimeCostRateTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset($primeCostRate);

        return true;
    }

    public function getItemIds()
    {
        $stmt = $this->pdo->prepare("SELECT i.id, i.tender_origin_id FROM ".BillItemTable::getInstance()->getTableName()." i
        LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.id = i.element_id AND e.deleted_at IS NULL
        LEFT JOIN ".ProjectStructureTable::getInstance()->getTableName()." p ON p.id = e.project_structure_id AND p.deleted_at IS NULL
        WHERE p.root_id = :project_id AND i.deleted_at IS NULL");

        $stmt->execute(array(
            'project_id' => $this->projectId
        ));

        $roots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($roots as $root)
        {
            $arrayOfIds = ProjectStructureTable::extractOriginId($root['tender_origin_id']);

            $this->billItemIds[$arrayOfIds['origin_id']] = $root['id'];
        }
    }


    public function getBillColumnSettingIds()
    {
        $stmt = $this->pdo->prepare("SELECT c.id, c.tender_origin_id FROM ".BillColumnSettingTable::getInstance()->getTableName()." c
        LEFT JOIN ".ProjectStructureTable::getInstance()->getTableName()." p ON p.id = c.project_structure_id AND p.deleted_at IS NULL
        WHERE p.root_id = :project_id AND c.deleted_at IS NULL");

        $stmt->execute(array(
            'project_id' => $this->projectId
        ));

        $billColumnSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($billColumnSettings as $column)
        {
            $arrayOfIds = ProjectStructureTable::extractOriginId($column['tender_origin_id']);

            $this->billColumnSettingIds[$arrayOfIds['origin_id']] = $column['id'];
        }
    }

    public function processItemTypeNotListed( $item )
    {
        if($item->id)
        {
            $billItemId = $this->billItemIds[(int) $item->id];
            unset($item->id);
        }
        else
        {
            return;
        }

        $uomId = (int) $item->uom_id;

        //Process Item LumpSumpPercent
        $typeRefs = (array_key_exists(sfBuildspaceExportBillRatesXML::TAG_TYPEREFERENCES, $item)) ? true : false;

        if($typeRefs)
        {
            $typeRefs = $item->{sfBuildspaceExportBillRatesXML::TAG_TYPEREFERENCES}->children();
        }
        else
        {
            $typeRefs = false;
        }

        if($uomId)
        {
            $item->uom_id = $this->unitIds[$uomId];
        }

        if($item->grand_total)
        {
            unset($item->grand_total); 
        }
        
        if($item->rate)
        {
            unset($item->rate);
        }

        $item->bill_item_id = $billItemId;
        $item->created_at = 'NOW()';
        $item->updated_at = 'NOW()';
        $item->created_by = $this->userId;
        $item->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $item, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(TenderBillItemNotListedRationalizedTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        $itemNotListedId = $stmt->returningIds[0];

        if($typeRefs)
        {
            foreach($typeRefs as $type)
            {
                $qty = (array_key_exists(sfBuildspaceExportBillXML::TAG_QTY, $type)) ? true : false;

                if($qty)
                {
                    $qty = $type->{sfBuildspaceExportBillXML::TAG_QTY}->children();
                }
                else
                {
                    $qty = false;
                }

                $data = new stdClass(); 

                $data->tender_bill_not_listed_item_rationalized_id = $itemNotListedId;
                $data->bill_column_setting_id = $this->billColumnSettingIds[(int) $type->bill_column_setting_id];
                $data->final_value = ($qty) ? (float) $qty->final_value : 0;
                $data->created_at = 'NOW()';
                $data->updated_at = 'NOW()';
                $data->created_by = $this->userId;
                $data->updated_by = $this->userId;

                $dataAndStructure = parent::generateArrayOfSingleData( $data, true );

                $stmt = new sfImportStatementGenerator();

                $stmt->createInsert(TenderBillItemNotListedRationalizedQuantityTable::getInstance()->getTableName(), $dataAndStructure['structure']);

                $stmt->addRecord($dataAndStructure['data']);

                $stmt->save();

                unset($type);
            }

            unset($typeRef);
        }

        return $itemNotListedId;
    }

    public function processUnits()
    {
        $originalId = null;

        foreach($this->units as $unit)
        {
            if($unit->id)
            {
                $originalId = (int) $unit->id;

                unset($unit->id);
            }

            if(!array_key_exists($originalId, $this->unitIds))
            {
            	
				if($uom = $this->getUnitBySymbolAndType((string) $unit->symbol, (int) $unit->type))
                {
                    $this->unitIds[$originalId] = $uom['id'];
                }
				else 
				{
					$unit->display = false;
	                $unit->created_at = 'NOW()';
	                $unit->updated_at = 'NOW()';
	                $unit->created_by = $this->userId;
	                $unit->updated_by = $this->userId;
	
	                $dataAndStructure = parent::generateArrayOfSingleData( $unit, true );
	
	                $stmt = new sfImportStatementGenerator();
	
	                $stmt->createInsert(UnitOfMeasurementTable::getInstance()->getTableName(), $dataAndStructure['structure']);
	
	                $stmt->addRecord($dataAndStructure['data']);
	
	                $stmt->save();
	
	                $this->unitIds[$originalId] = $stmt->returningIds[0];	
				}
            }
            else
            {
                unset($unit);
            }

        }

        unset($this->units);
    }

	public function getUnitBySymbolAndType( $symbol, $type)
    {
        $stmt = $this->pdo->prepare("SELECT uom.id, uom.name, uom.symbol FROM ".UnitOfMeasurementTable::getInstance()->getTableName()." uom
        WHERE LOWER(uom.symbol) = :uom_symbol AND uom.type = :type");

        $stmt->execute(array(
            'uom_symbol' => strtolower($symbol),
            'type' => $type
        ));

        $uom = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($uom) ? $uom : false;
    }

}
