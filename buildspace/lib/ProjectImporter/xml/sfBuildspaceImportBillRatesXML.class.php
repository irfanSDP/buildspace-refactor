<?php
class sfBuildspaceImportBillRatesXML extends sfBuildspaceXMLParser
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
    protected $items;
    protected $billColumnSettingUnits;
    protected $units;
    protected $tenderCompanyInfo;

    function __construct( $userId, $project, $company, $tenderCompanyInfo = array(), $filename = null, $uploadPath = null, $extension = null, $deleteFile = null )
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        
        $this->userId = $userId;

        $this->project = $project;

        $this->company = $company;

        parent::__construct( $filename, $uploadPath, $extension, $deleteFile );

        $this->extractData();

        if($project)
        {
            $this->projectId = $project['id'];

            $project = Doctrine_Core::getTable('ProjectStructure')->find($this->projectId);

            $this->projectUniqueId = $project->MainInformation->unique_id;
        }

        if($company)
        {
            $this->companyId = $company['id'];
        }

        if(!$tenderCompanyInfo)
        {
            $this->tenderCompanyInfo = ($this->getTenderCompany()) ? $this->getTenderCompany() : false;
        }
        else
        {
            $this->tenderCompanyInfo = $tenderCompanyInfo;
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

        $this->billColumnSettingUnits = $this->getBillColumnSettingUnits($xmlData);
    }

    public function process()
    {
        if(!$this->tenderCompanyInfo)
            return;

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
        if(!$this->tenderCompanyInfo)
            return;

        foreach($this->elements as $element)
        {
            if($element->id)
            {
                $elementId = (int) $element->id;
            }
            else
            {
                return;
            }

            $data = new stdClass();

            $data->tender_company_id = $this->tenderCompanyInfo['id'];
            $data->bill_element_id = $elementId;
            $data->grand_total = (float) $element->total_amount;
            $data->created_at = 'NOW()';
            $data->updated_at = 'NOW()';
            $data->created_by = $this->userId;
            $data->updated_by = $this->userId;

            $dataAndStructure = parent::generateArrayOfSingleData( $data, true );

            $stmt = new sfImportStatementGenerator();

            $stmt->createInsert(TenderBillElementGrandTotalTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            unset($data);
            unset($element);
        }

        unset($this->elements);
    }

    public function processItems()
    {
        if(!$this->tenderCompanyInfo)
            return;

        $insertedData = array();

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
                $data->tender_bill_item_not_listed_id = $itemNotListedId;
            }

            $data->tender_company_id = $this->tenderCompanyInfo['id'];
            $data->bill_item_id = $itemId;
            $data->grand_total = $grandTotal;
            $data->rate = $rate;
            $data->created_at = 'NOW()';
            $data->updated_at = 'NOW()';
            $data->created_by = $this->userId;
            $data->updated_by = $this->userId;

            $dataAndStructure = parent::generateArrayOfSingleData( $data, true );

            $stmt = new sfImportStatementGenerator();

            $stmt->createInsert(TenderBillItemRateTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $insertedData[] = array(
                'tender_company_id' => $data->tender_company_id,
                'bill_item_id' => $data->bill_item_id,
                'rate' => $data->rate,
                'grand_total' => $data->grand_total
            );

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
            unset($item);
        }

        if($insertedData)
            TenderBillItemRateLogTable::insertBatchLog($insertedData, "IMPORT");
    }

    public function processLumpSumpPercent( $lumpSumpPercent, $tenderBillItemRateId = false )
    {
        if($tenderBillItemRateId)
        {
            $lumpSumpPercent->tender_bill_item_rate_id = $tenderBillItemRateId;
        }

        $lumpSumpPercent->created_at = 'NOW()';
        $lumpSumpPercent->updated_at = 'NOW()';
        $lumpSumpPercent->created_by = $this->userId;
        $lumpSumpPercent->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $lumpSumpPercent, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(TenderBillItemLumpSumPercentageTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset($lumpSumpPercent);

        return true;
    }

    public function processPrimeCostRate( $primeCostRate, $tenderBillItemRateId = false )
    {
        if($tenderBillItemRateId)
        {
            $primeCostRate->tender_bill_item_rate_id = $tenderBillItemRateId;
        }

        $primeCostRate->created_at = 'NOW()';
        $primeCostRate->updated_at = 'NOW()';
        $primeCostRate->created_by = $this->userId;
        $primeCostRate->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $primeCostRate, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(TenderBillItemPrimeCostRateTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset($primeCostRate);

        return true;
    }

    public function processItemTypeNotListed( $item )
    {
        if($item->id)
        {
            $billItemId = (int) $item->id;
            unset($item->id);
        }
        else
        {
            return;
        }

        if($item->markup_percentage)
        {
            unset($item->markup_percentage);
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
        $item->tender_company_id = $this->tenderCompanyInfo['id'];
        $item->created_at = 'NOW()';
        $item->updated_at = 'NOW()';
        $item->created_by = $this->userId;
        $item->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $item, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(TenderBillItemNotListedTable::getInstance()->getTableName(), $dataAndStructure['structure']);

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

                $data->tender_bill_item_not_listed_id = $itemNotListedId;
                $data->bill_column_setting_id = (int) $type->bill_column_setting_id;
                $data->final_value = ($qty) ? (float) $qty->final_value : 0;
                $data->created_at = 'NOW()';
                $data->updated_at = 'NOW()';
                $data->created_by = $this->userId;
                $data->updated_by = $this->userId;

                $dataAndStructure = parent::generateArrayOfSingleData( $data, true );

                $stmt = new sfImportStatementGenerator();

                $stmt->createInsert(TenderBillItemNotListedQuantityTable::getInstance()->getTableName(), $dataAndStructure['structure']);

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

    public function getTenderCompany()
    {
        if(!$this->companyId && !$this->projectId)
            return;

        $query = DoctrineQuery::create()->select('tc.id, tc.project_structure_id, tc.company_id, tc.total_amount')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $this->projectId)
            ->andWhere('tc.company_id = ?', $this->companyId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

        return $tenderCompanyInfo = ($query->count() > 0) ? $query->fetchOne() : false;
    }

    protected function getBillColumnSettingUnits($xmlData)
    {
        $billColumnSettingUnits = array();

        $columns = $xmlData->{sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTING}->{sfBuildspaceExportBillXML::TAG_COLUMN} ?? array();

        foreach($columns as $column)
        {
            // Only include bill column settings with tender_origin_id.
            if( ! $column->tender_origin_id ) continue;

            $origin_id = explode('-', (string)$column->tender_origin_id)[2];

            $units = $column->{sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTINGUNITS}->{sfBuildspaceExportBillXML::TAG_UNIT} ?? array();

            foreach($units as $unit)
            {
                $billColumnSettingUnits[$origin_id][] = (int) $unit;
            }
        }

        return $billColumnSettingUnits;
    }

}
