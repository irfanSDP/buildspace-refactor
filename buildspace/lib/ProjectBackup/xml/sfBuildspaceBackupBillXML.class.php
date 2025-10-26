<?php
class sfBuildspaceBackupBillXML extends sfBuildspaceXMLGenerator
{
	public $bill;
	public $exportType;
	public $savePath;
	
	const TAG_BILL = "BILL";
	const TAG_BILL_SETTING = "BILL_SETTING";
	const TAG_BILL_MARKUP_SETTING = "BILL_MARKUP_SETTING";
	const TAG_BILL_COLUMN_SETTING = "BILL_COLUMN_SETTING";
	const TAG_BILL_BUILD_UP_FLOOR_AREA = "BILL_BUILD_UP_FLOOR_AREA";
	const TAG_BILL_BUILD_UP_FLOOR_AREA_FC = "BILL_BUILD_UP_FLOOR_AREA_FC";
	const TAG_BILL_BUILD_UP_FLOOR_AREA_SUMMARY = "BILL_BUILD_UP_FLOOR_AREA_SUMMARY";
	const TAG_BILL_TYPE = "BILL_TYPE";
	const TAG_BILL_LAYOUT_SETTING = "BILL_LAYOUT_SETTING";
	const TAG_BILL_LAYOUT_HEAD_SETTING = "BILL_LAYOUT_HEAD_SETTING";
	const TAG_BILL_LAYOUT_PHRASE = "BILL_LAYOUT_PHRASE";
	const TAG_BILL_ELEMENT = "BILL_ELEMENT";
	const TAG_BILL_ITEM = "BILL_ITEM";
	const TAG_BILL_ITEM_TYPE_REF = "BILL_ITEM_TYPE_REF";
	const TAG_BILL_ITEM_TYPE_REF_FC = "BILL_ITEM_TYPE_REF_FC";
	const TAG_BILL_ITEM_FC = "BILL_ITEM_FC";
	const TAG_BILL_ITEM_LS_PERCENT = "BILL_ITEM_LS_PERCENT";
	const TAG_BILL_ITEM_PC_RATE = "BILL_ITEM_PC_RATE";
	const TAG_BILL_ELEMENT_FC = "BILL_ELEMENT_FC";
	const TAG_BILL_ITEM_BUILD_UP_QTY = "BILL_ITEM_BUILD_UP_QTY";
	const TAG_BILL_ITEM_BUILD_UP_QTY_FC = "BILL_ITEM_BUILD_UP_QTY_FC";
	const TAG_BILL_ITEM_BUILD_UP_SUMMARY = "BILL_ITEM_BUILD_UP_SUMMARY";
	const TAG_BILL_ITEM_BUILD_UP_RATE = "BILL_ITEM_BUILD_UP_RATE";
	const TAG_BILL_ITEM_BUILD_UP_RATE_FC = "BILL_ITEM_BUILD_UP_RATE_FC";
	const TAG_BILL_ITEM_BUILD_UP_RATE_RESOURCE = "BILL_ITEM_BUILD_UP_RATE_RESOURCE";
	const TAG_BILL_ITEM_BUILD_UP_RATE_TRADE = "BILL_ITEM_BUILD_UP_RATE_TRADE";
	const TAG_BILL_ITEM_BUILD_UP_RATE_SUMMARY = "BILL_ITEM_BUILD_UP_RATE_SUMMARY";
	const TAG_UOM = "UOM";
	const TAG_DIMENSIONS = "DIMENSIONS";
	const TAG_DIMENSION = "DIMENSION";
	const TAG_ITEM = "ITEM";
	
    function __construct( $filename = null, ProjectStructure $bill, $exportType = null, $savePath = null, $extension = null, $deleteFile = false ) 
    {
        $this->exportType = ($exportType) ? $exportType : false;
		
		$this->bill = $bill;
		
		$this->pdo	= ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $savePath = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;

        parent::__construct( $filename, $savePath, $extension, $deleteFile );
    }
	
	public function process( $write = true )
	{
		$billStructure = $this->getBillStructure();
		
    	parent::create( self::TAG_BILL, array(
    		'exportType' => $this->exportType, 
    		'title' => $billStructure['title'],
			'type' => $billStructure['type'])
		);
		
		if(array_key_exists('BillSetting', $billStructure))
		{
			parent::addChildren(parent::createTag( self::TAG_BILL_SETTING ), $billStructure['BillSetting']);
			
			unset($billStructure['BillSetting']);
		}
		
		if(array_key_exists('BillMarkupSetting', $billStructure))
		{
			parent::addChildren(parent::createTag( self::TAG_BILL_MARKUP_SETTING ), $billStructure['BillMarkupSetting']);
			
			unset($billStructure['BillMarkupSetting']);
		}
		
		if(array_key_exists('BillType', $billStructure))
		{
			parent::addChildren(parent::createTag( self::TAG_BILL_TYPE ), $billStructure['BillType']);
			
			unset($billStructure['BillType']);
		}
		
		if(array_key_exists('BillColumnSettings', $billStructure) && count($billStructure['BillColumnSettings']))
		{
			$columnSettingTag = parent::createTag( self::TAG_BILL_COLUMN_SETTING );
			
			foreach($billStructure['BillColumnSettings'] as $k => $column)
			{
				parent::addChildTag( $columnSettingTag, self::TAG_ITEM , $column );
			}
			
			unset($billStructure['BillColumnSettings']);

			$buildUpFloorAreas = $this->getBuildUpFloorArea();

			if(count($buildUpFloorAreas))
			{
				$floorAreaItemTag = parent::createTag( self::TAG_BILL_BUILD_UP_FLOOR_AREA );
				
				$ffcs = array();
				
				foreach($buildUpFloorAreas as $k => $floorArea)
				{
					if(array_key_exists('FormulatedColumns', $floorArea))
					{
						if(count($floorArea['FormulatedColumns']))
						{
							foreach($floorArea['FormulatedColumns'] as $k => $fc)
							{
								array_push($ffcs, $fc);
							}
						}
						
						unset($floorArea['FormulatedColumns']);
					}

					parent::addChildTag( $floorAreaItemTag, self::TAG_ITEM , $floorArea );
				}
				
				unset($buildUpFloorAreas);

				if(count($ffcs))
				{
					$formulatedColumnTag = parent::createTag( self::TAG_BILL_BUILD_UP_FLOOR_AREA_FC );
					
					foreach($ffcs as $k => $fc)
					{
						parent::addChildTag( $formulatedColumnTag, self::TAG_ITEM , $fc );
						
						unset($fc);
					}
					
					unset($ffcs);
				}

				$floorAreaSummaries = $this->getFloorAreaSummary();

				if(count($floorAreaSummaries))
				{
					$floorAreaSummaryTag = parent::createTag( self::TAG_BILL_BUILD_UP_FLOOR_AREA_SUMMARY );
					
					foreach($floorAreaSummaries as $k => $summary)
					{
						parent::addChildTag( $floorAreaSummaryTag, self::TAG_ITEM , $summary );
						
						unset($summary);
					}
					
					unset($floorAreaSummaries);
				}
			}
		}
		
		if(array_key_exists('BillLayoutSetting', $billStructure))
		{
			$billPhrase = false;
			$billHeadSettings = false;
			
			if(array_key_exists('BillPhrase', $billStructure['BillLayoutSetting']))
			{
				$billPhrase = $billStructure['BillLayoutSetting']['BillPhrase'];
				
				unset($billStructure['BillLayoutSetting']['BillPhrase']);
			}
			
			if(array_key_exists('BillHeadSettings', $billStructure['BillLayoutSetting']) 
				&& count($billStructure['BillLayoutSetting']['BillHeadSettings']))
			{
				$billHeadSettings = $billStructure['BillLayoutSetting']['BillHeadSettings'];
				
				unset($billStructure['BillLayoutSetting']['BillHeadSettings']);
			}
			
			parent::addChildren(parent::createTag( self::TAG_BILL_LAYOUT_SETTING ), $billStructure['BillLayoutSetting']);
			
			if($billPhrase)
			{
				parent::addChildren(parent::createTag( self::TAG_BILL_LAYOUT_PHRASE ), $billPhrase);
			}
			
			if($billHeadSettings)
			{
				$billHeadSettingsTag = parent::createTag( self::TAG_BILL_LAYOUT_HEAD_SETTING );
				
				foreach($billHeadSettings as $k => $head)
				{
					parent::addChildTag( $billHeadSettingsTag, self::TAG_ITEM , $head );
				}
			}
			
			unset($billStructure['BillLayoutSetting'], $billPhrase, $billHeadSettings);
		}

		unset($billStructure);

		$elements = $this->getElements();
		
		if(count($elements))
		{
			$elementTag = parent::createTag( self::TAG_BILL_ELEMENT );
			$efcs = array();
			
			foreach($elements as $k => $element)
			{
				if(array_key_exists('FormulatedColumns', $element))
				{
					if(count($element['FormulatedColumns']))
					{
						foreach($element['FormulatedColumns'] as $k => $fc)
						{
							array_push($efcs, $fc);
						}
					}
					
					unset($element['FormulatedColumns']);
				}

				parent::addChildTag( $elementTag, self::TAG_ITEM , $element );
			}
			
			unset($elements);

			if(count($efcs))
			{
				$formulatedColumnTag = parent::createTag( self::TAG_BILL_ELEMENT_FC );
				
				foreach($efcs as $k => $fc)
				{
					parent::addChildTag( $formulatedColumnTag, self::TAG_ITEM , $fc );
					
					unset($fc);
				}
				
				unset($efcs);
			}
		}
		
		$billItems = $this->getItemList();
		
		if(count($billItems))
		{
			$uoms 		 = array();
			$typeRefs	 = array();
			$buildUpQtys = array();
			$ifcs		 = array();
			$bqSummaries = array();
			$buildUpRates= array();
			$brSummaries = array();
			$lsItems	 = array();
			$pcItems     = array();
			$itemTag	 = parent::createTag( self::TAG_BILL_ITEM );
			
			foreach($billItems as $k => $item)
			{
				if(array_key_exists('UnitOfMeasurement', $item))
				{
					if(is_array($item['UnitOfMeasurement']) && !array_key_exists($item['UnitOfMeasurement']['id'], $uoms))
					{
						$uoms[$item['UnitOfMeasurement']['id']] = $item['UnitOfMeasurement'];
					}
					
					unset($item['UnitOfMeasurement']);
				}
				
				if(array_key_exists('LumpSumPercentage', $item))
				{
					if(is_array($item['LumpSumPercentage']) && !array_key_exists($item['LumpSumPercentage']['id'], $lsItems))
					{
						$lsItems[$item['LumpSumPercentage']['id']] = $item['LumpSumPercentage'];
					}

					unset($item['LumpSumPercentage']);
				}
				
				if(array_key_exists('PrimeCostRate', $item))
				{
					if(is_array($item['PrimeCostRate']) && !array_key_exists($item['PrimeCostRate']['id'], $pcItems))
					{
						$pcItems[$item['PrimeCostRate']['id']] = $item['PrimeCostRate'];
					}

					unset($item['PrimeCostRate']);
				}
				
				if(array_key_exists('BillItemTypeReferences', $item))
				{
					if(is_array($item['BillItemTypeReferences']) && count($item['BillItemTypeReferences']))
					{
						foreach($item['BillItemTypeReferences'] as $k => $typeReference)
						{
							array_push($typeRefs, $typeReference);
						}
					}
					
					unset($item['BillItemTypeReferences']);
				}
				
				if(array_key_exists('FormulatedColumns', $item) && count($item['FormulatedColumns']))
				{
					foreach($item['FormulatedColumns'] as $k => $fc)
					{
						array_push($ifcs, $fc);
					}
					
					unset($item['FormulatedColumns']);
				}
				
				if(array_key_exists('BuildUpQuantities', $item))
				{
					if(is_array($item['BuildUpQuantities']) && count($item['BuildUpQuantities']))
					{
						foreach($item['BuildUpQuantities'] as $k => $quantity)
						{
							array_push($buildUpQtys, $quantity);
						}
					}
					
					unset($item['BuildUpQuantities']);
				}
				
				if(array_key_exists('BuildUpQuantitySummaries', $item) && count($item['BuildUpQuantitySummaries']))
				{
					foreach($item['BuildUpQuantitySummaries'] as $k => $summary)
					{
						array_push($bqSummaries, $summary);
					}
					
					unset($item['BuildUpQuantitySummaries']);
				}
				
				if(array_key_exists('BuildUpRateSummary', $item) && is_array($item['BuildUpRateSummary']))
				{
					array_push($brSummaries, $item['BuildUpRateSummary']);
					
					unset($item['BuildUpRateSummary']);
				}
				
				if(array_key_exists('BuildUpRateItems', $item) && count($item['BuildUpRateItems']))
				{
					foreach($item['BuildUpRateItems'] as $k => $rate)
					{
						if(is_array($rate['UnitOfMeasurement']) && $rate['UnitOfMeasurement']['id'] != null && !array_key_exists($rate['UnitOfMeasurement']['id'], $uoms))
						{
							$uoms[$rate['UnitOfMeasurement']['id']] = $rate['UnitOfMeasurement'];
						}
						
						unset($rate['UnitOfMeasurement']);
						
						array_push($buildUpRates, $rate);
					}
				}
				
				parent::addChildTag( $itemTag, self::TAG_ITEM , $item );
				
				unset($item);
			}
			
			if(count($uoms))
			{
				$this->processUOM($uoms);
			}

			if(count($lsItems))
			{
				$this->processLSItems($lsItems);
			}

			if(count($pcItems))
			{
				$this->processPCItems($pcItems);
			}
			
			if(count($typeRefs))
			{
				$this->processTypeRef($typeRefs);
			}
			
			if(count($buildUpQtys))
			{
				$this->processBuildUpQty($buildUpQtys);
			}
			
			if(count($buildUpRates))
			{
				$this->processBuildUpRate($buildUpRates);
			}
			
			if(count($ifcs))
			{
				$formulatedColumnTag = parent::createTag( self::TAG_BILL_ITEM_FC );
				
				foreach($ifcs as $k => $fc)
				{
					parent::addChildTag( $formulatedColumnTag, self::TAG_ITEM , $fc );
					
					unset($fc);
				}
				
				unset($ifcs);
			}
			
			if(count($bqSummaries))
			{
				$bqSummaryTag = parent::createTag( self::TAG_BILL_ITEM_BUILD_UP_SUMMARY );
				
				foreach($bqSummaries as $k => $summary)
				{
					parent::addChildTag( $bqSummaryTag, self::TAG_ITEM , $summary );
					
					unset($summary);
				}
				
				unset($bqSummaries);
			}
			
			if(count($brSummaries))
			{
				$brSummaryTag = parent::createTag( self::TAG_BILL_ITEM_BUILD_UP_RATE_SUMMARY );
				
				foreach($brSummaries as $k => $summary)
				{
					parent::addChildTag( $brSummaryTag, self::TAG_ITEM , $summary );
					
					unset($summary);
				}
				
				unset($brSummaries);
			}
			
			unset($billItems, $uoms, $typeRefs, $buildUpQtys, $ifcs);
		}
		
		if($write)
            parent::write();
	}

	public function processUOM(Array $uoms)
	{
		$uomTag = parent::createTag( self::TAG_UOM );
		$dimensionArray = array();

		foreach($uoms as $uomId => $uom)
		{
			if(array_key_exists('UnitOfMeasurementDimensions', $uom) && count($uom['UnitOfMeasurementDimensions']))
			{
				$dimensionsXrefs = $uom['UnitOfMeasurementDimensions'];
				
				unset($uom['UnitOfMeasurementDimensions']);
			}
			
			$itemTag 		= parent::addChildTag($uomTag, self::TAG_ITEM, $uom );
			$dimensionTag 	= parent::addChildTag($itemTag, self::TAG_DIMENSIONS );

			if(isset($dimensionsXrefs) && count($dimensionsXrefs))
			{
				foreach($dimensionsXrefs as $k => $xref)
				{
					if(array_key_exists('Dimension', $xref))
					{
						$dimensionToAdd = $xref['Dimension'];
						
						$dimensionToAdd['priority'] = $xref['priority'];
						
						parent::addChildTag($dimensionTag, self::TAG_ITEM, $dimensionToAdd);
						
						if(!array_key_exists($xref['Dimension']['id'], $dimensionArray))
						{
							$dimensionArray[$xref['Dimension']['id']] = $xref['Dimension'];
						}
					}
					
					unset($xref);
				}
			}
		}
		
		if(count($dimensionArray))
		{
			$dimensionTag = parent::createTag( self::TAG_DIMENSION );
			
			foreach($dimensionArray as $dimensionId => $dimension)
			{
				$itemTag = parent::addChildTag($dimensionTag, self::TAG_ITEM, $dimension );
				
				unset($dimension);
			}
			
			unset($dimensionArray);
		}
		
		unset($uoms);
	}

	public function processLSItems(Array $lsItems)
	{
		$lsItemTag = parent::createTag( self::TAG_BILL_ITEM_LS_PERCENT );
		
		foreach($lsItems as $k => $lsItem)
		{
			parent::addChildTag( $lsItemTag, self::TAG_ITEM , $lsItem );
			
			unset($lsItem);
		}
		
		unset($lsItems);
	}

	public function processPCItems(Array $pcItems)
	{
		$lsItemTag = parent::createTag( self::TAG_BILL_ITEM_PC_RATE );
		
		foreach($pcItems as $k => $lsItem)
		{
			parent::addChildTag( $lsItemTag, self::TAG_ITEM , $lsItem );
			
			unset($lsItem);
		}
		
		unset($pcItems);
	}
	
	public function processTypeRef(Array $typeRefs)
	{
		$typeRefTag = parent::createTag( self::TAG_BILL_ITEM_TYPE_REF );
		$formulatedColumns = array();

        $pushedTypeRefId = new SplFixedArray(0);

		foreach($typeRefs as $typeRef)
		{
			if(array_key_exists('FormulatedColumns', $typeRef))
			{
				if(is_array($typeRef['FormulatedColumns']) && count($typeRef['FormulatedColumns']))
				{
					foreach($typeRef['FormulatedColumns'] as $fc)
					{
						array_push($formulatedColumns, $fc);
					}
				}
				
				unset($typeRef['FormulatedColumns']);
			}

            if(!in_array($typeRef['id'], $pushedTypeRefId->toArray()))
            {
                $pushedTypeRefId->setSize($pushedTypeRefId->getSize() + 1);
                $pushedTypeRefId[$pushedTypeRefId->getSize() -1] = $typeRef['id'];

                parent::addChildTag( $typeRefTag, self::TAG_ITEM , $typeRef );
            }

			unset($typeRef);
		}

        unset($pushedTypeRefId, $typeRefs);

		if(count($formulatedColumns))
		{
			$typeRefFCTag = parent::createTag( self::TAG_BILL_ITEM_TYPE_REF_FC );
			
			foreach($formulatedColumns as $fc)
			{
				parent::addChildTag( $typeRefFCTag, self::TAG_ITEM , $fc);
			}
		}
		
		unset($formulatedColumns);
	}
	
	public function processBuildUpQty(Array $buildUpQtys)
	{
		$qtyTag = parent::createTag( self::TAG_BILL_ITEM_BUILD_UP_QTY );
		$formulatedColumns = array();
		
		foreach($buildUpQtys as $k => $quantity)
		{
			if(array_key_exists('FormulatedColumns', $quantity))
			{
				if(is_array($quantity['FormulatedColumns']) && count($quantity['FormulatedColumns']))
				{
					foreach($quantity['FormulatedColumns'] as $k => $fc)
					{
						array_push($formulatedColumns, $fc);
					}
				}
				
				unset($quantity['FormulatedColumns']);
			}
			
			parent::addChildTag( $qtyTag, self::TAG_ITEM , $quantity );
			
			unset($quantity);
		}
		
		if(count($formulatedColumns))
		{
			$qtyFCTag = parent::createTag( self::TAG_BILL_ITEM_BUILD_UP_QTY_FC );
			
			foreach($formulatedColumns as $k => $fc)
			{
				parent::addChildTag( $qtyFCTag, self::TAG_ITEM , $fc );
			}
		}
		
		unset($buildUpQtys, $formulatedColumns);
	}
	
	public function processBuildUpRate(Array $buildUpRates)
	{
		$brTag = parent::createTag( self::TAG_BILL_ITEM_BUILD_UP_RATE );
		$brFormulatedColumns = array();
		$brResources = array();
		$brTrades = array();
		
		foreach($buildUpRates as $rate)
		{
			if(array_key_exists('FormulatedColumns', $rate))
			{
				if(is_array($rate['FormulatedColumns']) && count($rate['FormulatedColumns']))
				{
					foreach($rate['FormulatedColumns'] as $k => $fc)
					{
						array_push($brFormulatedColumns, $fc);
					}
				}
				
				unset($rate['FormulatedColumns']);
			}

			if(array_key_exists('Resource', $rate))
			{
				if(is_array($rate['Resource']) && !array_key_exists($rate['Resource']['id'], $brResources))
				{
					$brResources[$rate['Resource']['id']] = $rate['Resource'];
				}
				
				unset($rate['Resource']);
			}
			
			if(array_key_exists('Trade', $rate))
			{
				
				if(is_array($rate['Trade']) && !array_key_exists($rate['Trade']['id'], $brTrades))
				{
					$brTrades[$rate['Trade']['id']] = $rate['Trade'];
				}
				
				unset($rate['Trade']);
			}
			
			parent::addChildTag( $brTag, self::TAG_ITEM , $rate );
		}
		
		if(count($brFormulatedColumns))
		{
			$brFCTag = parent::createTag( self::TAG_BILL_ITEM_BUILD_UP_RATE_FC );
			
			foreach($brFormulatedColumns as $k => $fc)
			{
				parent::addChildTag( $brFCTag, self::TAG_ITEM , $fc );
			}
		}
		
		if(count($brResources))
		{
			$brResourceTag = parent::createTag( self::TAG_BILL_ITEM_BUILD_UP_RATE_RESOURCE );
			
			foreach($brResources as $k => $resource)
			{
				parent::addChildTag( $brResourceTag, self::TAG_ITEM , $resource );
			}
		}
		
		if(count($brTrades))
		{
			$brTradeTag = parent::createTag( self::TAG_BILL_ITEM_BUILD_UP_RATE_TRADE );
			
			foreach($brTrades as $k => $trade)
			{
				parent::addChildTag( $brTradeTag, self::TAG_ITEM , $trade );
			}
		}
	}
	
	public function getBillStructure($billId = false)
	{
		$billId = ($billId) ? $billId : $this->bill->id;

		$bill = array();

		$bill = $this->getProjectStructure($billId);

		$bill['BillSetting'] 		= $this->getBillSetting($billId);

		$bill['BillMarkupSetting'] 	= $this->getBillMarkupSetting($billId);

		$bill['BillColumnSettings']	= $this->getBillColumnSettings($billId);

		$bill['BillType']			= $this->getbillType($billId);

		$bill['BillLayoutSetting']	= $this->getBillLayoutSetting($billId);
			
		return $bill;
	}

	public function getProjectStructure($billId)
	{
		$stmt = $this->pdo->prepare("SELECT p.id, p.title, p.type 
			FROM ".ProjectStructureTable::getInstance()->getTableName()." p 
			WHERE p.id = ".$billId." AND p.deleted_at IS NULL");

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getBillSetting($billId)
	{
		$stmt = $this->pdo->prepare("SELECT s.id, s.title, s.description, s.build_up_quantity_rounding_type, s.build_up_rate_rounding_type, s.unit_type
			FROM ".BillSettingTable::getInstance()->getTableName()." s WHERE s.project_structure_id = ".$billId." AND s.deleted_at IS NULL");

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getBillMarkupSetting($billId)
	{
		$stmt = $this->pdo->prepare("SELECT m.id, m.bill_markup_enabled, m.bill_markup_percentage, m.bill_markup_amount, m.element_markup_enabled, m.item_markup_enabled, m.rounding_type 
			FROM ".BillMarkupSettingTable::getInstance()->getTableName()." m WHERE m.project_structure_id = ".$billId." AND m.deleted_at IS NULL");

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getBillColumnSettings($billId)
	{
		$stmt = $this->pdo->prepare("SELECT bcs.id, bcs.name, bcs.quantity, bcs.remeasurement_quantity_enabled, bcs.use_original_quantity, bcs.total_floor_area_m2, 
            bcs.total_floor_area_ft2, bcs.floor_area_has_build_up, bcs.floor_area_use_metric, bcs.floor_area_display_metric,
            bcs.show_estimated_total_cost, bcs.is_hidden FROM ".BillColumnSettingTable::getInstance()->getTableName()." bcs 
            WHERE bcs.project_structure_id = ".$billId." AND bcs.deleted_at IS NULL ORDER BY bcs.id ASC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getBuildUpFloorArea($billId = false)
	{
		$floorAreaItems = array();

		$billId = ($billId) ? $billId : $this->bill->id;

		$stmt = $this->pdo->prepare("SELECT f.id, f.bill_column_setting_id, f.description, f.total, 
			f.sign, f.priority, ffc.id AS ffc_id, ffc.relation_id AS ffc_relation_id, ffc.column_name AS ffc_column_name, 
			ffc.value AS ffc_value, ffc.final_value AS ffc_final_value
			FROM ".BillBuildUpFloorAreaItemTable::getInstance()->getTableName()." f 
			LEFT JOIN ".BillBuildUpFloorAreaFormulatedColumnTable::getInstance()->getTableName()." ffc ON ffc.relation_id = f.id
			JOIN ".BillColumnSettingTable::getInstance()->getTableName()." bcs ON bcs.id = f.bill_column_setting_id
            WHERE ffc.deleted_at IS NULL AND bcs.project_structure_id = ".$billId." AND bcs.deleted_at IS NULL");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($result))
        {
        	foreach($result as $k => $fitems)
        	{
        		if(!(array_key_exists($fitems['id'], $floorAreaItems)))
        		{
        			$floorAreaItems[$fitems['id']] = array(
        				'id' => $fitems['id'],
        				'bill_column_setting_id' => $fitems['bill_column_setting_id'],
        				'description' => $fitems['description'],
        				'total' => $fitems['total'],
        				'sign' => $fitems['sign'],
        				'priority' => $fitems['priority'],
        				'FormulatedColumns' => array()
        			);
        		}

        		if($fitems['ffc_id'])
        		{
        			array_push($floorAreaItems[$fitems['id']]['FormulatedColumns'], array(
        				'id'		 	=> $fitems['ffc_id'],
        				'relation_id' 	=> $fitems['ffc_relation_id'],
        				'column_name' 	=> $fitems['ffc_column_name'],
        				'value' 		=> $fitems['ffc_value'],
        				'final_value' 	=> $fitems['ffc_final_value']
        			));
        		}
        	}
        }

        return $floorAreaItems;
	}

	public function getFloorAreaSummary($billId = false)
	{
		$billId = ($billId) ? $billId : $this->bill->id;

		$stmt = $this->pdo->prepare("SELECT fsummary.id, fsummary.bill_column_setting_id, fsummary.total_floor_area, 
			fsummary.final_floor_area, fsummary.apply_conversion_factor, fsummary.conversion_factor_amount, 
			fsummary.conversion_factor_operator, fsummary.rounding_type
			FROM ".BillBuildUpFloorAreaSummaryTable::getInstance()->getTableName()." fsummary 
			JOIN ".BillColumnSettingTable::getInstance()->getTableName()." bcs ON bcs.id = fsummary.bill_column_setting_id
            WHERE fsummary.deleted_at IS NULL AND bcs.project_structure_id = ".$billId." AND bcs.deleted_at IS NULL");

		$stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return (count($result)) ? $result : false;
	}

	public function getbillType($billId)
	{
		$stmt = $this->pdo->prepare("SELECT bt.id, bt.type, bt.status FROM ".BillTypeTable::getInstance()->getTableName()." bt 
            WHERE bt.project_structure_id = ".$billId." AND bt.deleted_at IS NULL");

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getBillLayoutSetting($billId)
	{
		$stmt = $this->pdo->prepare("SELECT l.id, l.font, l.rounding_type, l.size, l.comma_total, l.comma_rate, l.comma_qty, 
			l.priceFormat, l.print_amt_col_only, l.print_without_price, l.print_full_decimal, l.add_psum_pcsum, 
			l.print_dollar_cent, l.print_without_cent, l.switch_qty_unit_rate, l.indent_item, l.includeIAndOForBillRef, 
			l.apply_binding_alignment, l.add_cont, l.contd, l.print_element_header, l.print_element_grid, l.print_element_grid_once, 
			l.page_numbering_option, l.page_no_prefix, l.print_date_of_printing, l.print_grand_total_quantity, l.align_element_to_left, 
			l.close_grid FROM ".BillLayoutSettingTable::getInstance()->getTableName()." l 
			WHERE l.bill_id = ".$billId." AND l.deleted_at IS NULL");

        $stmt->execute();

        $billLayoutSetting = $stmt->fetch(PDO::FETCH_ASSOC);

        $billLayoutSetting['BillHeadSettings'] = $this->getBillHeadSetting($billLayoutSetting['id']);

        $billLayoutSetting['BillPhrase'] = $this->getBillPhrase($billLayoutSetting['id']);

        return $billLayoutSetting;
	}

	public function getBillPhrase($layoutSettingId)
	{
		$stmt = $this->pdo->prepare("SELECT lp.id, lp.to_collection, lp.table_header_description, lp.table_header_unit, lp.table_header_qty,
            lp.table_header_rate, lp.table_header_amt, lp.currency, lp.cents, lp.collection_in_grid, lp.summary, lp.summary_in_grid, lp.totalPerUnitPrefix,
            lp.totalUnitPrefix, lp.totalPerTypePrefix, lp.summary_page_no, lp.summary_tender, lp.summary_page_one, lp.summary_page_two, lp.summary_page_three,
            lp.summary_page_four, lp.summary_page_five, lp.summary_page_six, lp.summary_page_seven, lp.summary_page_eight, lp.summary_page_nine, lp.element_header_bold,
            lp.element_header_underline, lp.element_header_italic, lp.element_footer_bold, lp.element_footer_underline, lp.element_footer_italic, lp.element_note_top_left_row1,
            lp.element_note_top_left_row2, lp.element_note_top_right_row1, lp.element_note_bot_left_row1, lp.element_note_bot_left_row2, lp.element_note_bot_right_row1,
            lp.element_note_bot_right_row2 FROM ".BillLayoutPhraseTable::getInstance()->getTableName()." lp 
            WHERE lp.bill_layout_setting_id = ".$layoutSettingId." AND lp.deleted_at IS NULL");

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getBillHeadSetting($layoutSettingId)
	{
		$stmt = $this->pdo->prepare("SELECT lh.id, lh.head, lh.bold, lh.underline, lh.italic FROM ".BillLayoutHeadSettingTable::getInstance()->getTableName()." lh
            WHERE lh.bill_layout_setting_id = ".$layoutSettingId." AND lh.deleted_at IS NULL");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getElements($billId = false)
	{
		$billId = ($billId) ? $billId : $this->bill->id;

		$elements = array();
		
		$stmt = $this->pdo->prepare("SELECT e.id, e.id, e.description, e.priority, e.note, 
			efc.id AS efc_id, efc.relation_id AS efc_relation_id, efc.column_name AS efc_column_name, 
			efc.final_value AS efc_final_value, efc.value AS efc_value 
			FROM ".BillElementTable::getInstance()->getTableName()." e
			LEFT JOIN ".BillElementFormulatedColumnTable::getInstance()->getTableName()." efc ON efc.relation_id = e.id
        	WHERE e.project_structure_id = ".$billId." AND efc.deleted_at IS NULL AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($result))
        {
        	foreach($result as $efc)
        	{
        		if(!array_key_exists($efc['id'], $elements))
        		{
        			$elements[$efc['id']] = array(
        				'id' => $efc['id'],
        				'description' => $efc['description'],
        				'priority' => $efc['priority'],
        				'note' => $efc['note'],
        				'FormulatedColumns' => array()
        			);
        		}

        		if($efc['efc_id'])
        		{
        			array_push($elements[$efc['id']]['FormulatedColumns'], array(
	        			'id'		 	=> $efc['efc_id'],
	    				'relation_id' 	=> $efc['efc_relation_id'],
	    				'column_name' 	=> $efc['efc_column_name'],
	    				'value' 		=> $efc['efc_value'],
	    				'final_value' 	=> $efc['efc_final_value']
	        		));
        		}

        		unset($efc);
        	}

        	unset($result);
        }

        return $elements;
	}
	
	public function getItemList($billId = false, $withBuildUp = true)
	{
        $billId = ($billId) ? $billId : $this->bill->id;

        $billItems       = array();
        $billItemIds     = new SplFixedArray(0);
        $unitIds         = new SplFixedArray(0);
        $unitIdToItemIds = array();

        $stmt = $this->pdo->prepare("SELECT  c.id, c.description, c.type, c.uom_id, c.element_id, c.note,
            c.grand_total, c.grand_total_after_markup, c.grand_total_quantity, c.bill_ref_element_no,
            c.bill_ref_page_no, c.bill_ref_char, c.priority, c.root_id, c.lft, c.rgt, c.level, uom.name, uom.symbol, uom.type AS uom_type,
            ifc.id AS ifc_id, ifc.relation_id, ifc.schedule_of_rate_item_formulated_column_id, ifc.column_name, ifc.linked, ifc.has_build_up, ifc.final_value, ifc.value,
            br_summary.id AS brs_id, br_summary.bill_item_id AS brs_bill_item_id, br_summary.total_cost AS brs_total_cost,
            br_summary.markup AS brs_markup, br_summary.final_cost AS brs_final_cost, br_summary.apply_conversion_factor AS brs_apply_conversion_factor,
            br_summary.conversion_factor_amount AS brs_conversion_factor_amount, br_summary.conversion_factor_uom_id AS brs_conversion_factor_uom_id,
            br_summary.conversion_factor_operator AS brs_conversion_factor_operator, br_summary.rounding_type AS brs_rounding_type,
            ls.id AS ls_id, ls.bill_item_id AS ls_bill_item_id, ls.rate AS ls_rate, ls.percentage AS ls_percentage, ls.amount AS ls_amount,
            pc.id AS pc_id, pc.bill_item_id AS pc_bill_item_id, pc.supply_rate AS pc_supply_rate, pc.wastage_percentage AS pc_wastage_percentage, pc.wastage_amount AS pc_wastage_amount,
            pc.labour_for_installation AS pc_labour_for_installation, pc.other_cost AS pc_other_cost, pc.profit_percentage AS pc_profit_percentage,
            pc.profit_amount AS pc_profit_amount, pc.total AS pc_total
            FROM ".BillItemTable::getInstance()->getTableName()." c
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.id = c.element_id
            JOIN ".ProjectStructureTable::getInstance()->getTableName()." p ON p.id = e.project_structure_id
            LEFT JOIN ".BillItemLumpSumPercentageTable::getInstance()->getTableName()." ls ON ls.bill_item_id = c.id AND ls.deleted_at IS NULL
            LEFT JOIN ".BillItemPrimeCostRateTable::getInstance()->getTableName()." pc ON pc.bill_item_id = c.id AND pc.deleted_at IS NULL
            LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
            LEFT JOIN ".BillItemFormulatedColumnTable::getInstance()->getTableName()." ifc ON ifc.relation_id = c.id AND ifc.deleted_at IS NULL
            LEFT JOIN ".BillBuildUpRateSummaryTable::getInstance()->getTableName()." br_summary ON br_summary.bill_item_id = c.id AND br_summary.deleted_at IS NULL
            WHERE p.id = ".$billId." AND p.deleted_at IS NULL AND c.deleted_at IS NULL AND c.deleted_at_project_revision_id IS NULL ORDER BY c.priority, c.lft, c.level");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($items))
        {
            foreach($items as $k => $item)
            {
                if(!array_key_exists($item['id'], $billItems))
                {
                    $billItems[$item['id']] = array(
                        'id' => $item['id'],
                        'description' => $item['description'],
                        'type' => $item['type'],
                        'uom_id' => $item['uom_id'],
                        'element_id' => $item['element_id'],
                        'note' => $item['note'],
                        'grand_total' => $item['grand_total'],
                        'grand_total_after_markup' => $item['grand_total_after_markup'],
                        'grand_total_quantity' => $item['grand_total_quantity'],
                        'bill_ref_element_no' => $item['bill_ref_element_no'],
                        'bill_ref_page_no' => $item['bill_ref_page_no'],
                        'bill_ref_char' => $item['bill_ref_char'],
                        'priority' => $item['priority'],
                        'root_id' => $item['root_id'],
                        'lft' => $item['lft'],
                        'rgt' => $item['rgt'],
                        'level' => $item['level'],
                        'UnitOfMeasurement' => null,
                        'LumpSumPercentage' => null,
                        'PrimeCostRate' => null,
                        'BillItemTypeReferences' => array(),
                        'FormulatedColumns' => array(),
                        'BuildUpQuantities' => array(),
                        'BuildUpQuantitySummaries' => array(),
                        'BuildUpRateItems' => array(),
                        'BuildUpRateSummary' => null
                    );

                    if($item['uom_id'])
                    {
                        $billItems[$item['id']]['UnitOfMeasurement'] = array(
                            'id' => $item['uom_id'],
                            'name' => $item['name'],
                            'symbol' => $item['symbol'],
                            'type' => $item['uom_type']
                        );

                        if(!array_key_exists($item['uom_id'], $unitIdToItemIds))
                        {
                            $unitIds->setSize($unitIds->getSize() + 1);

                            $unitIds[$unitIds->getSize() - 1] = $item['uom_id'];
                        }

                        $unitIdToItemIds[$item['uom_id']] = $item['id'];
                    }

                    if($item['ls_id'])
                    {
                        $billItems[$item['id']]['LumpSumPercentage'] = array(
                            'id' => $item['ls_id'],
                            'bill_item_id' => $item['ls_bill_item_id'],
                            'rate' => $item['ls_rate'],
                            'percentage' => $item['ls_percentage'],
                            'amount' => $item['ls_amount']
                        );
                    }

                    if($item['pc_id'])
                    {
                        $billItems[$item['id']]['PrimeCostRate'] = array(
                            'id' => $item['pc_id'],
                            'bill_item_id' => $item['pc_bill_item_id'],
                            'supply_rate' => $item['pc_supply_rate'],
                            'wastage_percentage' => $item['pc_wastage_percentage'],
                            'wastage_amount' => $item['pc_wastage_amount'],
                            'labour_for_installation' => $item['pc_labour_for_installation'],
                            'other_cost' => $item['pc_other_cost'],
                            'profit_percentage' => $item['pc_profit_percentage'],
                            'profit_amount' => $item['pc_profit_amount'],
                            'total' => $item['pc_total'],
                        );
                    }

                    $billItemIds->setSize($billItemIds->getSize() + 1);

                    $billItemIds[$billItemIds->getSize() - 1] = $item['id'];
                }

                if($item['ifc_id'])
                {
                    array_push($billItems[$item['id']]['FormulatedColumns'], array(
                        'id'		 	=> $item['ifc_id'],
                        'relation_id' 	=> $item['relation_id'],
                        'schedule_of_rate_item_formulated_column_id' => $item['schedule_of_rate_item_formulated_column_id'],
                        'column_name' 	=> $item['column_name'],
                        'linked' 		=> $item['linked'],
                        'has_build_up' 	=> $item['has_build_up'],
                        'value' 		=> $item['value'],
                        'final_value' 	=> $item['final_value']
                    ));
                }

                if($item['brs_id'])
                {
                    $billItems[$item['id']]['BuildUpRateSummary'] = array(
                        'id' => $item['brs_id'],
                        'bill_item_id' => $item['brs_bill_item_id'],
                        'total_cost' => $item['brs_total_cost'],
                        'markup' => $item['brs_markup'],
                        'final_cost' => $item['brs_final_cost'],
                        'apply_conversion_factor' => $item['brs_apply_conversion_factor'],
                        'conversion_factor_amount' => $item['brs_conversion_factor_amount'],
                        'conversion_factor_uom_id' => $item['brs_conversion_factor_uom_id'],
                        'conversion_factor_operator' => $item['brs_conversion_factor_operator'],
                        'rounding_type' => $item['brs_rounding_type']
                    );
                }

                unset($item);
            }

            unset($items);

            if($itemIdToTypeRefs = $this->getBillItemTypeRef($billItemIds))
            {
                foreach($itemIdToTypeRefs as $itemId => $typeRefs)
                {
                    if(array_key_exists($itemId, $billItems))
                    {
                        $billItems[$itemId]['BillItemTypeReferences'] = $typeRefs;
                    }
                    else
                    {
                        $billItems[$itemId]['BillItemTypeReferences'] = array();
                    }

                    unset($typeRefs);
                }
            }

            unset($itemIdToTypeRefs);

            if($unitIds->getSize() > 0)
            {
                if($unitToDimensions = $this->getUnitDimensions($unitIds))
                {
                    foreach($billItems as $itemId => $billItem)
                    {
                        if(is_array($billItem['UnitOfMeasurement']) && array_key_exists($billItem['UnitOfMeasurement']['id'], $unitToDimensions))
                        {
                            $billItems[$itemId]['UnitOfMeasurement']['UnitOfMeasurementDimensions'] = $unitToDimensions[$billItem['UnitOfMeasurement']['id']];
                        }
                    }
                }

                unset($unitToDimensions, $unitIds, $unitIdToItemIds);
            }

            if($itemIdToBillOfQuantities = $this->getBillOfQuantities($billItemIds))
            {
                foreach($itemIdToBillOfQuantities as $itemId => $bqs)
                {
                    if(array_key_exists($itemId, $billItems))
                    {
                        $billItems[$itemId]['BuildUpQuantities'] = $bqs;
                    }

                    unset($bqs);
                }
            }

            unset($itemIdToBillOfQuantities);

            if($itemIdToBQSummary = $this->getBQSummary($billItemIds))
            {
                foreach($itemIdToBQSummary as $itemId => $summary)
                {
                    if(array_key_exists($itemId, $billItems))
                    {
                        $billItems[$itemId]['BuildUpQuantitySummaries'] = $summary;
                    }

                    unset($summary);
                }
            }

            unset($itemIdToBQSummary);

            if($brItems = $this->getBRItems($billItemIds))
            {
                foreach($brItems as $itemId => $brItem)
                {
                    if(array_key_exists($itemId, $billItems))
                    {
                        $billItems[$itemId]['BuildUpRateItems'] = $brItem;
                    }

                    unset($brItem);
                }
            }

            unset($brItems);
        }

        return $billItems;
    }

	public function getDimensions($unitId)
	{
		$stmt = $this->pdo->prepare("SELECT uomXref.id, uomXref.priority, dimension.id AS dimension_id, dimension.name 
			FROM ".UnitOfMeasurementDimensionsTable::getInstance()->getTableName()." uomXref
            LEFT JOIN ".DimensionTable::getInstance()->getTableName()." dimension ON dimension.id = uomXref.dimension_id AND dimension.deleted_at IS NULL
        	WHERE uomXref.unit_of_measurement_id = ".$unitId);

		$stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($result))
        {
        	foreach($result as $k => $dimension)
        	{
        		$result[$k]['Dimension'] = array(
        			'id' => $dimension['dimension_id'],
        			'name' => $dimension['name']
        		);

        		unset($dimension, $result[$k]['dimension_id'], $result[$k]['name']);
        	}
        }

        return $result;
	}

    public function getUnitDimensions(SplFixedArray $unitIds)
    {
        if($unitIds->getSize() == 0)
            return false;

        $stmt = $this->pdo->prepare("SELECT uomXref.unit_of_measurement_id, uomXref.id, uomXref.priority, dimension.id AS dimension_id, dimension.name
            FROM ".UnitOfMeasurementDimensionsTable::getInstance()->getTableName()." uomXref
            LEFT JOIN ".DimensionTable::getInstance()->getTableName()." dimension ON dimension.id = uomXref.dimension_id AND dimension.deleted_at IS NULL
            WHERE uomXref.unit_of_measurement_id IN (".implode(',', $unitIds->toArray()).")");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        unset($unitIds);

        if(count($result))
        {
            foreach($result as $unitId => $dimensions)
            {
                foreach($dimensions as $k => $dimension)
                {
                    $result[$unitId][$k]['Dimension'] = array(
                        'id' => $dimension['dimension_id'],
                        'name' => $dimension['name']
                    );

                    unset($dimension, $result[$unitId][$k]['dimension_id'], $result[$unitId][$k]['name']);
                }

                unset($dimensions);
            }

            return $result;
        }

        return false;
    }

	public function getBillItemTypeRef(SplFixedArray $itemIds)
	{
        if($itemIds->getSize() == 0)
            return false;

		$stmt = $this->pdo->prepare("SELECT  type.bill_item_id AS item_id, type.bill_item_id, type.id, type.bill_column_setting_id, type.include, 
			type.total_quantity, type.grand_total, type.quantity_per_unit_difference, type.grand_total_after_markup,
			type_fc.id AS type_fc_id, type_fc.relation_id, type_fc.linked, type_fc.has_build_up, type_fc.column_name, 
			type_fc.value, type_fc.final_value FROM ".BillItemTypeReferenceTable::getInstance()->getTableName()." type
            LEFT JOIN ".BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName()." type_fc ON type_fc.relation_id = type.id AND type_fc.deleted_at IS NULL
        	JOIN ".BillColumnSettingTable::getInstance()->getTableName()." bcs ON bcs.id = type.bill_column_setting_id
        	WHERE type.bill_item_id IN (".implode(',', $itemIds->toArray()).") AND bcs.deleted_at IS NULL AND type.deleted_at IS NULL");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        unset($itemIds);

        if(count($result))
        {
        	foreach($result as $itemId => $typeRefs)
	        {
	        	foreach($typeRefs as $k => $type)
	        	{
	        		$formulatedColumns = null;

	        		if($type['type_fc_id'])
	        		{
	        			$formulatedColumns = array(
		        			'id' => $type['type_fc_id'],
		        			'relation_id' => $type['relation_id'],
		        			'linked' => $type['linked'],
		        			'has_build_up' => $type['has_build_up'],
		        			'column_name' => $type['column_name'],
		        			'value' => $type['value'],
		        			'final_value' => $type['final_value']
		        		);

		        		$result[$itemId][$k]['FormulatedColumns'][0] = $formulatedColumns;
	        		}

	        		unset($formulatedColumns, $result[$itemId][$k]['type_fc_id'], $result[$itemId][$k]['linked'], $result[$itemId][$k]['has_build_up'], 
	        			$result[$itemId][$k]['column_name'], $result[$itemId][$k]['value'], $result[$itemId][$k]['final_value'] );
	        	}
	        }

	        return $result;
        }

        return false;
	}

	public function getBillOfQuantities(SplFixedArray $itemIds)
	{
        if($itemIds->getSize() == 0)
            return false;

		$boq = array();

		$stmt = $this->pdo->prepare("SELECT bq.id, bq.bill_item_id, bq.bill_column_setting_id, bq.description, 
			bq.total, bq.sign, bq.priority, bq.type, bq_fc.id AS bq_fc_id, bq_fc.relation_id, bq_fc.column_name, 
			bq_fc.value, bq_fc.final_value FROM ".BillBuildUpQuantityItemTable::getInstance()->getTableName()." bq
            LEFT JOIN ".BillBuildUpQuantityFormulatedColumnTable::getInstance()->getTableName()." bq_fc ON bq_fc.relation_id = bq.id AND bq_fc.deleted_at IS NULL
            JOIN ".BillColumnSettingTable::getInstance()->getTableName()." bcs ON bcs.id = bq.bill_column_setting_id
        	WHERE bq.bill_item_id IN (".implode(',', $itemIds->toArray()).") AND bcs.deleted_at IS NULL AND bq.deleted_at IS NULL");

		$stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        unset($itemIds);

        if(count($result))
        {
        	foreach($result as $bqId => $bqFcs)
        	{
        		if(count($bqFcs))
        		{
        			$bqRef  = null;
        			$itemId = null;

        			foreach($bqFcs as $k => $bqFc)
	        		{
	        			if(!is_array($bqRef))
	        			{
	        				$itemId = $bqFc['bill_item_id'];

	        				$bqRef = array(
	        					'id' 					  => $bqId,
	        					'bill_item_id' 			  => $bqFc['bill_item_id'],
	        					'bill_column_setting_id'  => $bqFc['bill_column_setting_id'],
	        					'description' 			  => $bqFc['description'],
	        					'total' 				  => $bqFc['total'],
	        					'sign' 					  => $bqFc['sign'],
	        					'priority' 				  => $bqFc['priority'],
	        					'type' 					  => $bqFc['type'],
	        					'FormulatedColumns'		  => array()
	        				);
	        			}

	        			if($bqFc['bq_fc_id'])
	        			{
	        				array_push($bqRef['FormulatedColumns'], array(
		        				'id' 		  => $bqFc['bq_fc_id'],
		        				'relation_id' => $bqFc['relation_id'],
		        				'column_name' => $bqFc['column_name'],
		        				'value'	 	  => $bqFc['value'],
		        				'final_value' => $bqFc['final_value']
		        			));
	        			}

	        			unset($bqFc);
	        		}

	        		if(!array_key_exists($itemId, $boq))
	        		{
	        			$boq[$itemId] = array();
	        		}

	        		array_push($boq[$itemId], $bqRef);
        		}

        		unset($bqFcs);
        	}

        	return $boq;
        }

    	return false;
	}

    public function getBQSummary(SplFixedArray $itemIds)
    {
        if($itemIds->getSize() == 0)
            return false;

        $stmt = $this->pdo->prepare("SELECT bq_summary.bill_item_id  AS item_id, bq_summary.id, bq_summary.bill_item_id, bq_summary.bill_column_setting_id,
            bq_summary.linked_total_quantity, bq_summary.total_quantity, bq_summary.final_quantity, bq_summary.apply_conversion_factor,
            bq_summary.conversion_factor_amount, bq_summary.conversion_factor_operator, bq_summary.rounding_type,
            bq_summary.type FROM ".BillBuildUpQuantitySummaryTable::getInstance()->getTableName()." bq_summary
            JOIN ".BillColumnSettingTable::getInstance()->getTableName()." bcs ON bcs.id = bq_summary.bill_column_setting_id
            WHERE bq_summary.bill_item_id IN (".implode(',', $itemIds->toArray()).") AND bcs.deleted_at IS NULL AND bq_summary.deleted_at IS NULL");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        return (count($result)) ? $result : false;
    }

    public function getBRItems(SplFixedArray $itemIds)
    {
        if($itemIds->getSize() == 0)
            return false;

        $itemIdToBR = array();

        $stmt = $this->pdo->prepare("SELECT br_item.id, br_item.bill_item_id, br_item.build_up_rate_resource_id,
            br_item.build_up_rate_resource_trade_id, br_item.resource_item_library_id, br_item.description,
            br_item.total, br_item.line_total, br_item.uom_id, br_item.priority, br_item_uom.name AS uom_name, br_item_uom.symbol AS uom_symbol, br_item_uom.type AS uom_type, br_item_resource.id AS r_id, br_item_resource.name AS r_name,
            br_item_resource.bill_item_id AS r_bill_item_id, br_item_resource.resource_library_id AS r_resource_library_id,
            br_item_trade.id AS t_id, br_item_trade.description AS t_description, br_item_trade.bill_item_id AS t_bill_item_id,
            br_item_trade.build_up_rate_resource_id AS t_build_up_rate_resource_id, br_item_trade.resource_trade_library_id AS t_resource_trade_library_id,
            br_item_trade.priority AS t_priority, br_item_fc.id AS fc_id, br_item_fc.linked AS fc_linked, br_item_fc.relation_id AS fc_relation_id,
            br_item_fc.column_name AS fc_column_name, br_item_fc.value AS fc_value, br_item_fc.final_value AS fc_final_value
            FROM ".BillBuildUpRateItemTable::getInstance()->getTableName()." br_item
            LEFT JOIN ".UnitOfMeasurementTable::getInstance()->getTableName()." br_item_uom ON br_item.uom_id = br_item_uom.id AND br_item_uom.deleted_at IS NULL
            LEFT JOIN ".BillBuildUpRateResourceTable::getInstance()->getTableName()." br_item_resource ON br_item.build_up_rate_resource_id = br_item_resource.id AND br_item_resource.deleted_at IS NULL
            LEFT JOIN ".BillBuildUpRateResourceTradeTable::getInstance()->getTableName()." br_item_trade ON br_item.build_up_rate_resource_trade_id = br_item_trade.id AND br_item_trade.deleted_at IS NULL
            LEFT JOIN ".BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName()." br_item_fc ON br_item.id = br_item_fc.relation_id AND br_item_fc.deleted_at IS NULL
            WHERE br_item.bill_item_id IN (".implode(',', $itemIds->toArray()).") AND br_item.deleted_at IS NULL");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        unset($itemIds);

        if(count($result))
        {
            $unitToDimensions = array();

            foreach($result as $brItem)
            {
                if(!array_key_exists($brItem['bill_item_id'], $itemIdToBR))
                {
                    $itemIdToBR[$brItem['bill_item_id']] = array();
                }

                if(!array_key_exists($brItem['id'], $itemIdToBR[$brItem['bill_item_id']]))
                {
                    $trade = null;
                    $uom   = null;

                    if($brItem['t_id'])
                    {
                        $trade = array(
                            'id' => $brItem['t_id'],
                            'description' => $brItem['t_description'],
                            'bill_item_id' => $brItem['t_bill_item_id'],
                            'build_up_rate_resource_id' => $brItem['t_build_up_rate_resource_id'],
                            'resource_trade_library_id' => $brItem['t_resource_trade_library_id'],
                            'priority' => $brItem['t_priority']
                        );
                    }

                    if($brItem['uom_id'])
                    {
                        $dimensions = null;

                        $uom = array(
                            'id' => $brItem['uom_id'],
                            'name' => $brItem['uom_name'],
                            'symbol' => $brItem['uom_symbol'],
                            'type' => $brItem['uom_type']
                        );

                        if(array_key_exists($brItem['uom_id'], $unitToDimensions))
                        {
                            $dimensions = $unitToDimensions[$brItem['uom_id']];
                        }
                        else
                        {
                            $dimensions = $this->getDimensions($brItem['uom_id']);

                            $unitToDimensions[$brItem['uom_id']] = $dimensions;
                        }

                        $uom['UnitOfMeasurementDimensions'] = $dimensions;

                        unset($dimensions);
                    }

                    $itemIdToBR[$brItem['bill_item_id']][$brItem['id']] = array(
                        'id' => $brItem['id'],
                        'bill_item_id' => $brItem['bill_item_id'],
                        'build_up_rate_resource_id' => $brItem['build_up_rate_resource_id'],
                        'build_up_rate_resource_trade_id' => $brItem['build_up_rate_resource_trade_id'],
                        'resource_item_library_id' => $brItem['resource_item_library_id'],
                        'description' => $brItem['description'],
                        'total' => $brItem['total'],
                        'line_total' => $brItem['line_total'],
                        'uom_id' => $brItem['uom_id'],
                        'priority' => $brItem['priority'],
                        'UnitOfMeasurement' => $uom,
                        'FormulatedColumns' => array(),
                        'Resource' => array(
                            'id' => $brItem['r_id'],
                            'name' => $brItem['r_name'],
                            'bill_item_id' => $brItem['r_bill_item_id'],
                            'resource_library_id' => $brItem['r_resource_library_id']
                        ),
                        'Trade' => (is_array($trade)) ? $trade : null
                    );

                    unset($uom);
                }

                if($brItem['fc_id'])
                {
                    array_push($itemIdToBR[$brItem['bill_item_id']][$brItem['id']]['FormulatedColumns'], array(
                        'id' => $brItem['fc_id'],
                        'linked' => $brItem['fc_linked'],
                        'relation_id' => $brItem['fc_relation_id'],
                        'column_name' => $brItem['fc_column_name'],
                        'value' => $brItem['fc_value'],
                        'final_value' => $brItem['fc_final_value']
                    ));
                }

                unset($brItem);
            }

            unset($result);

            return $itemIdToBR;
        }

        return false;
    }
}
