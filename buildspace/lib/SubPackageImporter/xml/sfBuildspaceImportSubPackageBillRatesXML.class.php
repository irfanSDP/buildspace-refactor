<?php
class sfBuildspaceImportSubPackageBillRatesXML extends sfBuildspaceImportBillRatesXML
{
	public $subPackageId;
	public $subPackageCompanyInfo = false;
	
    function __construct( $userId, $project, $subPackage, $company, $subPackageCompanyInfo = array(), $tenderCompanyInfo = false, $filename = null, $uploadPath = null, $extension = null, $deleteFile = null )
    {
        parent::__construct($userId, $project, $company, $tenderCompanyInfo, $filename, $uploadPath, $extension, $deleteFile);

		$this->subPackageId = $subPackage['id'];
		
		$this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        if(!$subPackageCompanyInfo)
        {
            $this->subPackageCompanyInfo = ($this->getSubPackageCompany()) ? $this->getSubPackageCompany() : false;
        }
        else
        {
            $this->subPackageCompanyInfo = $subPackageCompanyInfo;
        }
    }
	
	public function process()
    {
        if(!$this->subPackageCompanyInfo)
            return;

        if($this->units)
            $this->processUnits();

        if($this->items)
            $this->processItems();
    }

    protected function getMatchingBillColumnSettingUnits()
    {
        $matchingBillColumnSettingUnits = array();

        foreach(SubPackageTypeReferenceTable::getIndividualUnits($this->subPackageId, $this->billId) as $billColumnSettingId => $subPackageUnitCounters)
        {
            $matchingBillColumnSettingUnits[$billColumnSettingId] = array();

            foreach($subPackageUnitCounters as $unitCounter)
            {
                $unitCountersFromImportFile = $this->billColumnSettingUnits[$billColumnSettingId] ?? array();

                if(in_array($unitCounter, $unitCountersFromImportFile))
                {
                    $matchingBillColumnSettingUnits[$billColumnSettingId][] = $unitCounter;
                }
            }
        }

        return $matchingBillColumnSettingUnits;
    }

	public function processItems()
    {
        if(!$this->subPackageCompanyInfo)
            return;
		
		$itemQuantities = $this->getItemQuantities();

        $matchingBillColumnSettingUnits = $this->getMatchingBillColumnSettingUnits();

		if(count($itemQuantities))
		{
			foreach($this->items as $item)
	        {
	            if($item->id)
	            {
	                $itemId = (int) $item->id;
	            }
	            else
	            {
	                return;
	            }
				
				if(array_key_exists($itemId, $itemQuantities) && count($itemQuantities[$itemId]))
				{
					$grandTotalPerUnit = 0;
		            $rate = (float) $item->rate;

		            unset($item->grand_total, $item->rate);

					foreach($itemQuantities[$itemId] as $k => $type)
					{
                        $billColumnSettingUnits = $matchingBillColumnSettingUnits[$type['bill_column_setting_id']] ?? array();

                        $grandTotalPerUnit += count($billColumnSettingUnits) * $type['final_value'];
					}

		            $data = new stdClass();

                    $grandTotal = (float) ($grandTotalPerUnit * $rate);

		            $data->sub_package_company_id = $this->subPackageCompanyInfo['id'];
		            $data->bill_item_id = $itemId;
		            $data->grand_total = $grandTotal;
					$data->single_unit_grand_total = $grandTotalPerUnit;
		            $data->rate = $rate;
		            $data->created_at = 'NOW()';
		            $data->updated_at = 'NOW()';
		            $data->created_by = $this->userId;
		            $data->updated_by = $this->userId;

		            $dataAndStructure = parent::generateArrayOfSingleData( $data, true );
		
		            $stmt = new sfImportStatementGenerator();
		
		            $stmt->createInsert(SubPackageBillItemRateTable::getInstance()->getTableName(), $dataAndStructure['structure']);
		
		            $stmt->addRecord($dataAndStructure['data']);
		
		            $stmt->save();
		
		            $returningId = $stmt->returningIds[0];

		            unset($data);
				}
	            unset($item);
	        }
	        $this->saveSubPackageUnitInformation($matchingBillColumnSettingUnits);
		}
    }

	public function getItemQuantities()
    {
    	$subPackageId = $this->subPackageId;

        $sqlFieldCond = '(
            CASE 
                WHEN spsori.sub_package_id is not null THEN spsori.sub_package_id
                WHEN spri.sub_package_id is not null THEN spri.sub_package_id
            ELSE
                spbi.sub_package_id
            END
            ) AS sub_package_id';

        $stmtItem = $this->pdo->prepare("SELECT DISTINCT i.id AS bill_item_id, rate.final_value AS final_value, $sqlFieldCond
        FROM ".SubPackageTable::getInstance()->getTableName()." sp
        LEFT JOIN ".ProjectStructureTable::getInstance()->getTableName()." bill ON bill.root_id = sp.project_structure_id
        LEFT JOIN ".SubPackageResourceItemTable::getInstance()->getTableName()." AS spri ON spri.sub_package_id = sp.id
        LEFT JOIN ".SubPackageScheduleOfRateItemTable::getInstance()->getTableName()." AS spsori ON spsori.sub_package_id = sp.id
        JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = bill.id
        JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id
        LEFT JOIN ".BillBuildUpRateItemTable::getInstance()->getTableName()." bur ON bur.bill_item_id = i.id AND bur.resource_item_library_id = spri.resource_item_id AND bur.deleted_at IS NULL
        LEFT JOIN ".ScheduleOfRateItemFormulatedColumnTable::getInstance()->getTableName()." AS sifc ON sifc.relation_id = spsori.schedule_of_rate_item_id AND sifc.deleted_at IS NULL
        LEFT JOIN ".BillItemFormulatedColumnTable::getInstance()->getTableName()." AS rate ON rate.relation_id = i.id
        LEFT JOIN " . SubPackageBillItemTable::getInstance()->getTableName() . " spbi ON spbi.bill_item_id = i.id AND spbi.sub_package_id = sp.id
        WHERE sp.id =".$subPackageId." AND sp.deleted_at IS NULL
        AND NOT (spri.sub_package_id IS NULL AND spsori.sub_package_id IS NULL and spbi.sub_package_id IS NULL)
        AND (rate.relation_id = bur.bill_item_id OR rate.schedule_of_rate_item_formulated_column_id = sifc.id OR rate.relation_id = spbi.bill_item_id)
        AND rate.column_name = '".BillItem::FORMULATED_COLUMN_RATE."' AND rate.final_value <> 0 AND rate.deleted_at IS NULL
        AND i.type <> ".BillItem::TYPE_ITEM_NOT_LISTED." AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL ORDER BY i.id");

        $stmtItem->execute();
		
        $records = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

        $billItems = array();
		
        $form = new BaseForm();
        
        $quantities = array();

        if(count($records) > 0)
        {
            $billItemIds           = Utilities::arrayValueRecursive('bill_item_id', $records);
            $billRefSelector       = 'p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char';
            
            $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.description
                FROM ".BillItemTable::getInstance()->getTableName()." c
                JOIN ".BillItemTable::getInstance()->getTableName()." p ON c.lft BETWEEN p.lft AND p.rgt
                WHERE c.root_id = p.root_id AND c.type != ".BillItem::TYPE_ITEM_NOT_LISTED."
                AND c.id IN (".implode(',', $billItemIds).") AND c.project_revision_deleted_at IS NULL
                AND c.deleted_at IS NULL AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL");

            $stmt->execute();
            
            $finalItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $billItemIds = Utilities::arrayValueRecursive('id', $finalItems);
			
			/* Get Quantities */
			$stmt = $this->pdo->prepare("SELECT type.bill_item_id, type.bill_column_setting_id, type_fc.final_value, type_fc.column_name
                FROM ".BillItemTypeReferenceTable::getInstance()->getTableName()." type
                JOIN ".BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName()." type_fc ON type_fc.relation_id = type.id AND type_fc.column_name = '".BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT."'
                WHERE type.bill_item_id IN (".implode(',', $billItemIds).") AND type.include = TRUE AND type.deleted_at IS NULL 
                AND type_fc.deleted_at IS NULL GROUP BY type.bill_item_id, type.bill_column_setting_id, type_fc.final_value, type_fc.column_name");

            $stmt->execute();
            
            $quantities = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

            unset($records);
        }

		return $quantities;
    }
	
	public function getSubPackageCompany()
    {
        if(!$this->companyId && !$this->subPackageId)
            return;

        $query = DoctrineQuery::create()->select('sc.id, sc.sub_package_id, sc.company_id')
            ->from('SubPackageCompany sc')
            ->where('sc.sub_package_id = ?', $this->subPackageId)
            ->andWhere('sc.company_id = ?', $this->companyId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

        return $subPackageCompanyInfo = ($query->count() > 0) ? $query->fetchOne() : false;
    }

    protected function saveSubPackageUnitInformation(array $matchingBillColumnSettingUnits)
    {
        foreach($matchingBillColumnSettingUnits as $billColumnSettingId => $counters)
        {
            SubPackageUnitInformationTable::flushUnits($billColumnSettingId);
            foreach($counters as $counter)
            {
                SubPackageUnitInformationTable::getOrNew($billColumnSettingId, $counter);
            }
        }
    }
}
