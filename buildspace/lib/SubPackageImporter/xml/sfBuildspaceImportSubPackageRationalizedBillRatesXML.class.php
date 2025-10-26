<?php
class sfBuildspaceImportSubPackageRationalizedBillRatesXML extends sfBuildspaceImportRationalizedBillRatesXML
{
	public $quantities;
	public $subPackageId;
	public $itemToElementIds = array();
	
	public function setSubPackageId($subPackageId)
	{
		$this->subPackageId = $subPackageId;
	}
	
	public function process()
    {
        $this->getElementIds();
        $this->getItemIds();
        $this->getBillColumnSettingIds();
		$this->getBillColumnSettings();
		$this->quantities = $this->getQuantities();

        if($this->units)
            $this->processUnits();

        if($this->items)
            $this->processItems();
    }
	
	public function processItems()
    {
    	$elementGrandTotal = array();
		
    	foreach($this->items as $item)
        {
            if($item->id && array_key_exists((int) $item->id, $this->billItemIds))
            {
                $itemId = $this->billItemIds[(int) $item->id];
				$elementId = null;
				
				$grandTotal = 0;
				$rate = (float) $item->rate;
				
				if(count($this->billColumnSettings) && count($this->quantities))
				{
					foreach($this->billColumnSettings as $columnSettingId => $columnSetting)
					{
						$grandTotalPerUnit = 0;
						
						if(array_key_exists($columnSettingId, $this->quantities) && array_key_exists($itemId, $this->quantities[$columnSettingId]))
						{
							$grandTotalPerUnit = number_format($rate * $this->quantities[$columnSettingId][$itemId][0], 2, '.', '');
						}
						
						$grandTotal+= $grandTotalPerUnit * $columnSetting['quantity'];
					}	
				}
	
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
				
				if(array_key_exists($itemId, $this->itemToElementIds))
				{
					$elementId = $this->itemToElementIds[$itemId];
					
					if(!array_key_exists($elementId, $elementGrandTotal))
					{
						$elementGrandTotal[$elementId] = array(
							'grand_total' => 0
						);
					}
					
					$elementGrandTotal[$elementId]['grand_total']+= $grandTotal;
				}
	
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

		if(count($elementGrandTotal))
		{
			foreach($elementGrandTotal as $elementId => $value)
			{
				$data = new stdClass();

	            $data->bill_element_id = $elementId;
	            $data->grand_total = (float) $value['grand_total'];
	            $data->created_at = 'NOW()';
	            $data->updated_at = 'NOW()';
	            $data->created_by = $this->userId;
	            $data->updated_by = $this->userId;
	
	            $dataAndStructure = parent::generateArrayOfSingleData( $data, true );
	
	            $stmt = new sfImportStatementGenerator();
	
	            $stmt->createInsert(TenderBillElementRationalizedGrandTotalTable::getInstance()->getTableName(), $dataAndStructure['structure']);
	
	            $stmt->addRecord($dataAndStructure['data']);
	
	            $stmt->save();
			}
		}
		
    }

	public function getItemIds()
    {
        $sql = "SELECT i.id, i.tender_origin_id, i.element_id FROM ".BillItemTable::getInstance()->getTableName()." i
        LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.id = i.element_id AND e.deleted_at IS NULL
        LEFT JOIN ".ProjectStructureTable::getInstance()->getTableName()." p ON p.id = e.project_structure_id AND p.deleted_at IS NULL
        WHERE p.root_id = :project_id AND i.deleted_at IS NULL";

        $params = array(
            'project_id' => $this->projectId
        );

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        $roots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($roots as $root)
        {
            $arrayOfIds = ProjectStructureTable::extractOriginId($root['tender_origin_id']);
			
			$this->itemToElementIds[$root['id']] = $root['element_id'];

            $this->billItemIds[$arrayOfIds['origin_id']] = $root['id'];
        }
    }

	public function getBillColumnSettings()
	{
		$extractedIds = ProjectStructureTable::extractOriginId($this->project['tender_origin_id']);
		
		$billOriginId = ProjectStructureTable::generateSubPackageOriginId($this->buildspaceId, $this->billId, $extractedIds['project_id'], $this->subPackageId);
		
		$stmt = $this->pdo->prepare("SELECT c.id , c.id, c.quantity, c.name, c.use_original_quantity
			FROM ".ProjectStructureTable::getInstance()->getTableName()." s
			LEFT JOIN ".BillColumnSettingTable::getInstance()->getTableName()." c ON c.project_structure_id = s.id AND c.deleted_at IS NULL
            WHERE s.tender_origin_id = '".$billOriginId."' AND s.deleted_at IS NULL");
		
		$stmt->execute();
			
		$this->billColumnSettings = array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC));
	}

	public function getQuantities()
    {
        $implodedItemIds = null;
		
		$result = array();

        if(is_array($this->billItemIds))
        {
            $implodedItemIds .= implode(',', $this->billItemIds);
            $implodedItemIds .= ",";
        }

        $implodedItemIds = rtrim($implodedItemIds, ",");

        foreach($this->billColumnSettings as $column)
        {
            $quantityFieldName = $column['use_original_quantity'] ? BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT : BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT_REMEASUREMENT;

            if ( ! empty($implodedItemIds) )
            {
                $stmt = $this->pdo->prepare("SELECT r.bill_item_id, COALESCE(fc.final_value, 0) AS value 
                FROM ".BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName()." fc
                JOIN ".BillItemTypeReferenceTable::getInstance()->getTableName()." r ON fc.relation_id = r.id
                WHERE r.bill_item_id IN (".$implodedItemIds.") AND r.bill_column_setting_id = ".$column['id']."
                AND r.include IS TRUE AND fc.column_name = '".$quantityFieldName."' AND fc.final_value <> 0
                AND r.deleted_at IS NULL AND fc.deleted_at IS NULL");

                $stmt->execute();

                $quantities = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

                $result[$column['id']] = $quantities;

                unset($quantities);
            }
            else
            {
                $result[$column['id']] = 0;
            }
        }

        return $result;
    }
}
