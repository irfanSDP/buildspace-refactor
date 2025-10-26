<?php
class sfBuildspaceImportProjectBuilderBillXML
{
    protected $con;
    protected $pdo;
    protected $unitIds = array();
    protected $xmlParser;

    protected $buildspaceId;
    protected $originalProjectStructureId;
    protected $userId;
    protected $breakdownIds;
    protected $existingUnitOfMeasurements;
    protected $columnSettingIds;
    protected $collectionPagesXML;
    protected $billPagesXML;
    protected $quantitiesXML;
    protected $billPagesPool = array();
    protected $billCollectionPool = array();
    protected $elementIds = array();
    protected $billItemIds = array();
    protected $newTypeReferenceIds = array();
    protected $withQty;
    protected $withRate;
    protected $projectRevision;

    function __construct(ProjectMainInformation $projectMainInformation, $userId, $breakdownIds = array(), $withQty=true, $withRate=true, Doctrine_Connection $con=null)
    {
        $this->con = $con ? $con : ProjectStructureTable::getInstance()->getConnection();

        $this->pdo = $this->con->getDbh();

        $this->userId = $userId;

        $this->breakdownIds = $breakdownIds;

        $stmt = $this->pdo->prepare("SELECT uom.id, uom.symbol, uom.type FROM ".UnitOfMeasurementTable::getInstance()->getTableName()." uom
        WHERE uom.deleted_at IS NULL ORDER BY uom.type");

        $stmt->execute();

        $uomRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($uomRecords as $uomRecord)
        {
            $this->existingUnitOfMeasurements[$uomRecord['type'].'-'.strtolower(trim($uomRecord['symbol']))] = $uomRecord['id'];
        }

        $this->withQty  = $withQty;
        $this->withRate = $withRate;

        $this->projectRevision = $projectMainInformation->ProjectStructure->getLatestProjectRevision();
    }

    public function processXMLFile($filename, $path)
    {
        $this->xmlParser = new sfBuildspaceXMLParser((string) $filename, $path, 'xml', false);
        $this->xmlParser->read();

        $xmlData = $this->xmlParser->getProcessedData();

        $this->buildspaceId = $xmlData->attributes()->buildspaceId;

        $billSettingXML = ($xmlData->{sfBuildspaceExportBillXML::TAG_BILLSETTING}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_BILLSETTING}->children() : false;

        if($xmlData->attributes()->isSupplyOfMaterialBill)
        {
            $billSettingType = ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL;
        }
        elseif ($xmlData->attributes()->isScheduleOfRateBill)
        {
            $billSettingType = ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL;
        }
        else
        {
            $billSettingType = ProjectStructure::TYPE_BILL;
        }

        if($billSettingXML)
            $this->processBillSetting($billSettingXML, $billSettingType);

        if($billSettingType == ProjectStructure::TYPE_BILL)
        {
            $billColumnSettingsXML = ($xmlData->{sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTING}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTING}->children() : false;

            if($billColumnSettingsXML)
                $this->processBillColumnSettings($billColumnSettingsXML);

            $billTypeXML = ($xmlData->{sfBuildspaceExportBillXML::TAG_BILLTYPE}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_BILLTYPE}->children() : false;

            if($billTypeXML)
                $this->processBillType($billTypeXML);
        }

        $layoutSettingXML = ($xmlData->{sfBuildspaceExportBillXML::TAG_LAYOUTSETTING}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_LAYOUTSETTING}->children() : false;

        if($layoutSettingXML)
            $this->processLayoutSetting($layoutSettingXML, $billSettingType);

        $elementsXML = ($xmlData->{sfBuildspaceExportBillXML::TAG_ELEMENTS}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_ELEMENTS}->children() : false;

        $originalBillId = (int) $xmlData->attributes()->billId;

        if($elementsXML && array_key_exists($originalBillId, $this->breakdownIds))
        {
            $billId = $this->breakdownIds[$originalBillId];
            $this->processElements($elementsXML, $billId, $billSettingType);
        }

        $unitsXML = ($xmlData->{sfBuildspaceExportBillXML::TAG_UNITOFMEASUREMENT}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_UNITOFMEASUREMENT}->children() : false;

        if($unitsXML)
            $this->processUnits($unitsXML);

        $itemsXML = ($xmlData->{sfBuildspaceExportBillXML::TAG_ITEMS}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_ITEMS}->children() : false;

        if($itemsXML)
        {
            $this->processItems($itemsXML, $billSettingType);
        }
    }

    protected function processBillSetting(SimpleXMLElement $billSettingXML, $billSettingType = ProjectStructure::TYPE_BILL)
    {
        if(array_key_exists((int) $billSettingXML->project_structure_id, $this->breakdownIds))
        {
            $projectStructureId = $this->breakdownIds[(int) $billSettingXML->project_structure_id ];

            switch($billSettingType)
            {
                case ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL:
                    $billSetting =  new SupplyOfMaterial();
                break;
                case ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL:
                    $billSetting = new ScheduleOfRateBill();
                    break;
                case ProjectStructure::TYPE_BILL:
                    $billSetting = new BillSetting();

                    $billSetting->build_up_quantity_rounding_type = (int)$billSettingXML->build_up_quantity_rounding_type;
                    $billSetting->build_up_rate_rounding_type     = (int)$billSettingXML->build_up_rate_rounding_type;
                    break;
                default:
                    throw new Exception('Invalid Bill Setting Type!');
            }

            $billSetting->project_structure_id = $projectStructureId;
            $billSetting->title                = (string)$billSettingXML->title;
            $billSetting->description          = (string)$billSettingXML->description;
            $billSetting->unit_type            = (int)$billSettingXML->unit_type;

            $billSetting->save($this->con);
        }
        else
        {
            throw new Exception("Cannot create bill settings record!");
        }
    }

    protected function processBillColumnSettings(SimpleXMLElement $billColumnSettingsXML)
    {
        foreach($billColumnSettingsXML as $column)
        {
            if($column->id)
            {
                $originalId = (int) $column->id;

                unset($column->id);

                $this->originalProjectStructureId = (int) $column->project_structure_id;
                $column->project_structure_id = $this->breakdownIds[(int) $column->project_structure_id ];
                $column->created_at = 'NOW()';
                $column->updated_at = 'NOW()';
                $column->created_by = $this->userId;
                $column->updated_by = $this->userId;
                $column->use_original_quantity = 1;
                $column->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $originalId, $this->originalProjectStructureId );
                $column->remeasurement_quantity_enabled = false;

                $dataAndStructure = $this->xmlParser->generateArrayOfSingleData( $column, true );

                // Remove unnecessary fields.
                if($unitIndex = array_search(sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTINGUNITS, $dataAndStructure['structure']))
                {
                    unset($dataAndStructure['data'][$unitIndex]);
                    unset($dataAndStructure['structure'][$unitIndex]);
                }

                $stmt = new sfImportStatementGenerator();

                $stmt->createInsert(BillColumnSettingTable::getInstance()->getTableName(), $dataAndStructure['structure']);

                $stmt->addRecord($dataAndStructure['data']);

                $stmt->save();

                $this->storeUnitInformation($column, $stmt->returningIds[0]);

                $this->columnSettingIds[$originalId] = $stmt->returningIds[0];

                unset($column, $stmt);
            }
        }
    }

    public function processBillType(SimpleXMLElement $billTypeXML)
    {
        $billType = new BillType();

        $billType->project_structure_id = $this->breakdownIds[(int) $billTypeXML->project_structure_id ];
        $billType->type                 = (int)$billTypeXML->type;
        $billType->status               = (int)$billTypeXML->status;

        $billType->save($this->con);

        $billType->free(true);
    }

    public function processLayoutSetting(SimpleXMLElement $layoutSettingXML, $billSettingType = ProjectStructure::TYPE_BILL)
    {
        switch($billSettingType)
        {
            case ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL:

                if(!array_key_exists((int)$layoutSettingXML->project_structure_id, $this->breakdownIds))
                    throw new Exception('Could not create Bill Layout Settings!');

                $layoutSetting = new SupplyOfMaterialLayoutSetting();
                $layoutSettingColumnNames = SupplyOfMaterialLayoutSettingTable::getInstance()->getColumnNames();

                $billPhrase = new SupplyOfMaterialLayoutPhraseSetting();
                $billPhraseColumnNames = SupplyOfMaterialLayoutPhraseSettingTable::getInstance()->getColumnNames();

                $headSettings = new Doctrine_Collection('SupplyOfMaterialLayoutHeadSetting');
                $headSettingColumnNames = SupplyOfMaterialLayoutHeadSettingTable::getInstance()->getColumnNames();

                $layoutSettingXML->project_structure_id = $this->breakdownIds[(int) $layoutSettingXML->project_structure_id];

                break;
            case ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL:
                if(!array_key_exists((int)$layoutSettingXML->project_structure_id, $this->breakdownIds))
                    throw new Exception('Could not create Bill Layout Settings!');

                $layoutSetting = new ScheduleOfRateBillLayoutSetting();
                $layoutSettingColumnNames = ScheduleOfRateBillLayoutSettingTable::getInstance()->getColumnNames();

                $billPhrase = new ScheduleOfRateBillLayoutPhraseSetting();
                $billPhraseColumnNames = ScheduleOfRateBillLayoutPhraseSettingTable::getInstance()->getColumnNames();

                $headSettings = new Doctrine_Collection('ScheduleOfRateBillLayoutHeadSetting');
                $headSettingColumnNames = ScheduleOfRateBillLayoutHeadSettingTable::getInstance()->getColumnNames();

                $layoutSettingXML->project_structure_id = $this->breakdownIds[(int) $layoutSettingXML->project_structure_id];

                break;
            case ProjectStructure::TYPE_BILL:
                if(!array_key_exists((int)$layoutSettingXML->bill_id, $this->breakdownIds))
                    throw new Exception('Could not create Bill Layout Settings!');

                $layoutSetting = new BillLayoutSetting();
                $layoutSettingColumnNames = BillLayoutSettingTable::getInstance()->getColumnNames();

                $billPhrase = new BillLayoutPhrase();
                $billPhraseColumnNames = BillLayoutPhraseTable::getInstance()->getColumnNames();

                $headSettings = new Doctrine_Collection('BillLayoutHeadSetting');
                $headSettingColumnNames = BillLayoutHeadSettingTable::getInstance()->getColumnNames();

                $layoutSettingXML->bill_id = $this->breakdownIds[(int) $layoutSettingXML->bill_id ];
                $layoutSettingXML->print_element_header = (int) $layoutSettingXML->print_element_header;

                break;
            default:
                throw new Exception('Invalid Bill Setting Type!');
        }

        $billPhraseXML = (array_key_exists(sfBuildspaceExportBillXML::TAG_PHRASE, $layoutSettingXML)) ? $layoutSettingXML->{sfBuildspaceExportBillXML::TAG_PHRASE}->children() : false;

        $headSettingXML = (array_key_exists(sfBuildspaceExportBillXML::TAG_HEADSETTING, $layoutSettingXML)) ? $layoutSettingXML->{sfBuildspaceExportBillXML::TAG_HEADSETTING}->children() : false;

        $layoutSettingXML->add_cont = (int) $layoutSettingXML->add_cont;
        $layoutSettingXML->print_element_grid = (int) $layoutSettingXML->print_element_grid;
        $layoutSettingXML->print_element_grid_once = (int) $layoutSettingXML->print_element_grid_once;

        unset($layoutSettingXML->created_at, $layoutSettingXML->updated_at, $layoutSettingXML->created_by, $layoutSettingXML->updated_by);

        foreach ($layoutSettingXML as $field => $value) {
            if (!in_array($field, $this->xmlParser->getExcludedFieldsList()) && in_array($field, $layoutSettingColumnNames) && $value != '' && $value != null) {
                $layoutSetting->{$field} = $value;
            }
        }

        $layoutSetting->save($this->con);

        unset($layoutSettingXML);

        if($billPhraseXML)
        {
            if($billSettingType == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL)
                $billPhraseXML->som_layout_setting_id = $layoutSetting->id;
            elseif($billSettingType == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL)
                $billPhraseXML->schedule_of_rate_bill_layout_setting_id = $layoutSetting->id;
            else
                $billPhraseXML->bill_layout_setting_id = $layoutSetting->id;

            unset($billPhraseXML->created_at, $billPhraseXML->updated_at, $billPhraseXML->created_by, $billPhraseXML->updated_by);

            foreach ($billPhraseXML as $field => $value) {
                if (!in_array($field, $this->xmlParser->getExcludedFieldsList()) && in_array($field, $billPhraseColumnNames) && $value != '' && $value != null) {
                    $billPhrase->{$field} = $value;
                }
            }

            $billPhrase->save($this->con);

            $billPhrase->free(true);

            unset($billPhraseXML, $billPhrase);
        }

        if($headSettingXML)
        {
            foreach($headSettingXML as $head)
            {
                $item = $head->children();

                if($billSettingType == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL)
                {
                    $item->som_layout_setting_id = $layoutSetting->id;

                    $headSetting = new SupplyOfMaterialLayoutHeadSetting();
                }
                elseif($billSettingType == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL)
                {
                    $item->schedule_of_rate_bill_layout_setting_id = $layoutSetting->id;

                    $headSetting = new ScheduleOfRateBillLayoutHeadSetting();
                }
                else
                {
                    $item->bill_layout_setting_id = $layoutSetting->id;

                    $headSetting = new BillLayoutHeadSetting();
                }

                unset($item->created_at, $item->updated_at, $item->created_by, $item->updated_by);

                foreach ($item as $field => $value) {
                    if (!in_array($field, $this->xmlParser->getExcludedFieldsList()) && in_array($field, $headSettingColumnNames) && $value != '' && $value != null) {
                        $headSetting->{$field} = $value;
                    }
                }

                $headSettings->add($headSetting);

                unset($item);
            }

            $headSettings->save($this->con);

            unset($headSettingXML, $headSettings);
        }

        $layoutSetting->free(true);

        unset($layoutSetting);
    }

    protected function processElements(SimpleXMLElement $elementsXML, $billId, $billSettingType = ProjectStructure::TYPE_BILL)
    {
        if($billSettingType == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL)
        {
            $tableName = SupplyOfMaterialElementTable::getInstance()->getTableName();
        }
        elseif($billSettingType == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL)
        {
            $tableName = ScheduleOfRateBillElementTable::getInstance()->getTableName();
        }
        else
        {
            $tableName = BillElementTable::getInstance()->getTableName();
        }

        foreach($elementsXML as $element)
        {
            if($element->id)
            {
                $originalId = (int) $element->id;

                unset($element->id);

                if(!$element->project_structure_id)
                {
                    $element->project_structure_id = $billId;
                }
                else
                {
                    $element->project_structure_id = $this->breakdownIds[(int) $element->project_structure_id ];
                }

                $element->created_at = 'NOW()';
                $element->updated_at = 'NOW()';
                $element->created_by = $this->userId;
                $element->updated_by = $this->userId;

                $dataAndStructure = $this->xmlParser->generateArrayOfSingleData( $element, true );

                $stmt = new sfImportStatementGenerator();

                $stmt->createInsert($tableName, $dataAndStructure['structure']);

                $stmt->addRecord($dataAndStructure['data']);

                $stmt->save();

                $this->elementIds[$originalId] = $stmt->returningIds[0];
            }

            unset($element);
        }
    }

    protected function processUnits(SimpleXMLElement $unitsXML)
    {
        foreach($unitsXML as $unit)
        {
            if($unit->id)
            {
                $originalId = (int) $unit->id;

                unset($unit->id);

                if(!array_key_exists($originalId, $this->unitIds))
                {
                    $key = (int) $unit->type.'-'.strtolower(trim((string) $unit->symbol));

                    if(array_key_exists( $key,$this->existingUnitOfMeasurements))
                    {
                        $this->unitIds[$originalId] = $this->existingUnitOfMeasurements[$key];
                    }
                    else
                    {
                        $unit->display = true;
                        $unit->created_at = 'NOW()';
                        $unit->updated_at = 'NOW()';
                        $unit->created_by = $this->userId;
                        $unit->updated_by = $this->userId;

                        $dataAndStructure = $this->xmlParser->generateArrayOfSingleData( $unit, true );

                        $stmt = new sfImportStatementGenerator();

                        $stmt->createInsert(UnitOfMeasurementTable::getInstance()->getTableName(), $dataAndStructure['structure']);

                        $stmt->addRecord($dataAndStructure['data']);

                        $stmt->save();

                        $this->unitIds[$originalId] = $stmt->returningIds[0];
                    }
                }
            }

            unset($unit);
        }

        unset($this->units);
    }

    protected function processItems(SimpleXMLElement $itemsXML, $billSettingType = ProjectStructure::TYPE_BILL)
    {
        $ratesXML           = array();
        $lumpSumPercentsXML = array();
        $primeCostRatesXML  = array();
        $quantitiesXML      = array();
        $typeReferenceIds   = array();

        foreach($itemsXML as $item)
        {
            if($item->id)
            {
                $originalId = (int) $item->id;

                unset($item->id);

                $root = false;

                if($originalId != (int) $item->root_id)
                {
                    $item->root_id = $this->billItemIds[(int) $item->root_id];
                }
                else
                {
                    $root = true;
                }

                if($uomId = (int) $item->uom_id)
                {
                    $item->uom_id = $this->unitIds[$uomId];
                }

                switch($billSettingType)
                {
                    case ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL:
                        $item->supply_rate = !$this->withRate ? 0 : floatval($item->supply_rate);
                        $item->difference = !$this->withRate ? 0 : floatval($item->supply_rate);
                        $tableName = SupplyOfMaterialItemTable::getInstance()->getTableName();
                        break;
                    case ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL:
                        $tableName = ScheduleOfRateBillItemTable::getInstance()->getTableName();
                        break;
                    default:
                        $tableName = BillItemTable::getInstance()->getTableName();
                }

                $item->element_id          = $this->elementIds[(int) $item->element_id];
                $item->project_revision_id = $this->projectRevision->id;
                $item->created_at          = 'NOW()';
                $item->updated_at          = 'NOW()';
                $item->created_by          = $this->userId;
                $item->updated_by          = $this->userId;

                if(!$this->withQty and $billSettingType == ProjectStructure::TYPE_BILL)
                {
                    $item->grand_total_quantity = 0;
                }

                if((!$this->withRate or !$this->withQty) and $billSettingType == ProjectStructure::TYPE_BILL)
                {
                    $item->grand_total = 0;
                    $item->grand_total_after_markup = 0;
                }

                unset($item->deleted_at_project_revision_id);
                unset($item->project_revision_deleted_at);

                $dataAndStructure = $this->xmlParser->generateArrayOfSingleData( $item, true );

                $stmt = new sfImportStatementGenerator();

                $dataAndStructure['structure'][] = 'tender_origin_id';
                $dataAndStructure['data'][]      = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $originalId, $this->originalProjectStructureId);

                $stmt->createInsert($tableName, $dataAndStructure['structure']);

                $stmt->addRecord($dataAndStructure['data']);

                $stmt->save();

                $newItemId = $stmt->returningIds[0];

                if($root)
                {
                    //Update Root Id
                    $stmt->updateRecord($tableName,
                        array(
                            'id' => $newItemId
                        ),
                        array(
                            'root_id' => $newItemId
                        )
                    );
                }

                unset($stmt);

                $this->billItemIds[$originalId] = $newItemId;

                if($billSettingType == ProjectStructure::TYPE_BILL && array_key_exists(sfBuildspaceExportBillXML::TAG_TYPEREFERENCES, $item))
                {
                    $typeReferences = $item->{sfBuildspaceExportBillXML::TAG_TYPEREFERENCES}->children();

                    if($this->withRate && array_key_exists(sfBuildspaceExportBillXML::TAG_ITEM_PC_RATE, $item) && ((int)$item->type == BillItem::TYPE_ITEM_PC_RATE))
                    {
                        $primeCostRate = $item->{sfBuildspaceExportBillXML::TAG_ITEM_PC_RATE}->children();
                        $rates         = $item->{sfBuildspaceExportBillXML::TAG_RATES}->children();

                        foreach($typeReferences as $type)
                        {
                            $type->grand_total = $type->grand_total_after_markup = number_format((float) $rates->final_value * (float) $type->total_quantity, 2,'.','');
                        }

                        $primeCostRatesXML[] = $primeCostRate;
                    }

                    list($quantities, $newTypeReferenceIds) = $this->processTypeReferences($typeReferences);

                    $quantitiesXML = array_merge($quantitiesXML, $quantities);
                    $typeReferenceIds += $newTypeReferenceIds;
                }

                if($billSettingType == ProjectStructure::TYPE_BILL && $this->withRate)
                {
                    //Process Item LumpSumpPercent
                    if(array_key_exists(sfBuildspaceExportBillXML::TAG_ITEM_LS_PERCENT, $item) && ((int)$item->type == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT))
                    {
                        $lumpSumPercent = $item->{sfBuildspaceExportBillXML::TAG_ITEM_LS_PERCENT}->children();
                        $lumpSumPercentsXML[] = $lumpSumPercent;
                    }

                    if(array_key_exists(sfBuildspaceExportBillXML::TAG_RATES, $item))
                    {
                        $ratesXML[] = $item->{sfBuildspaceExportBillXML::TAG_RATES}->children();
                    }
                }
            }

            unset($item);
        }

        if(!empty($quantitiesXML))
            $this->processQty($quantitiesXML, $typeReferenceIds);

        if(!empty($lumpSumPercentsXML))
            $this->processLumpSumPercent($lumpSumPercentsXML);

        if(!empty($primeCostRatesXML))
            $this->processPrimeCostRate($primeCostRatesXML);

        if(!empty($ratesXML))
            $this->processRate($ratesXML);
    }

    protected function processTypeReferences( SimpleXMLElement $typeReferences )
    {
        $quantities          = array();
        $newTypeReferenceIds = array();

        foreach($typeReferences as $typeReference)
        {
            if($typeReference->id)
            {
                $originalId = (int) $typeReference->id;

                unset($typeReference->id);

                if(!$this->withQty)
                {
                    $typeReference->quantity_per_unit_difference = 0;
                    $typeReference->total_quantity = 0;
                }

                if(!$this->withRate or !$this->withQty)
                {
                    $typeReference->grand_total = 0;
                    $typeReference->grand_total_after_markup = 0;
                }

                $typeReference->include                = ((int) $typeReference->include) ? 1 : 0;
                $typeReference->bill_item_id           = $this->billItemIds[(int) $typeReference->bill_item_id];
                $typeReference->bill_column_setting_id = $this->columnSettingIds[(int) $typeReference->bill_column_setting_id];
                $typeReference->created_at             = 'NOW()';
                $typeReference->updated_at             = 'NOW()';
                $typeReference->created_by             = $this->userId;
                $typeReference->updated_by             = $this->userId;

                $dataAndStructure = $this->xmlParser->generateArrayOfSingleData( $typeReference, true );

                $stmt = new sfImportStatementGenerator();

                $stmt->createInsert(BillItemTypeReferenceTable::getInstance()->getTableName(), $dataAndStructure['structure']);

                $stmt->addRecord($dataAndStructure['data']);

                $stmt->save();

                $newTypeReferenceIds[$originalId] = $stmt->returningIds[0];

                unset($stmt);

                if($this->withQty and array_key_exists(sfBuildspaceExportBillXML::TAG_QTY, $typeReference))
                {
                    $qty = $typeReference->{sfBuildspaceExportBillXML::TAG_QTY}->children();
                    $quantities[] = $qty;
                }
            }

            unset($type);
        }

        return array($quantities, $newTypeReferenceIds);
    }

    protected function processQty( Array $quantities, Array $typeReferenceIds )
    {
        $insertValues  = array();
        $questionMarks = array();

        foreach($quantities as $qty)
        {
            if($qty->id && array_key_exists((int)$qty->relation_id, $typeReferenceIds))
            {
                $relationId = $typeReferenceIds[(int)$qty->relation_id];

                $data = array(
                    intval(false),
                    intval(false),
                    $relationId,
                    BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT,
                    (float)$qty->final_value,
                    (float)$qty->final_value,
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s'),
                    $this->userId,
                    $this->userId
                );

                $insertValues = array_merge($insertValues, $data);

                $questionMarks[] = '('.implode(',', array_fill(0, count($data), '?')).')';
            }

            unset($qty);
        }

        if(!empty($insertValues))
        {
            $stmt = $this->pdo->prepare("INSERT INTO ".BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName()."
            (linked, has_build_up, relation_id, column_name, value, final_value, created_at, updated_at, created_by, updated_by)
            VALUES " . implode(',', $questionMarks));

            $stmt->execute($insertValues);
        }
    }

    protected function processLumpSumPercent( Array $lumpSumPercents )
    {
        $insertValues  = array();
        $questionMarks = array();

        foreach($lumpSumPercents as $lumpSumPercent)
        {
            if(array_key_exists((int)$lumpSumPercent->bill_item_id, $this->billItemIds))
            {
                $billItemId = $this->billItemIds[(int)$lumpSumPercent->bill_item_id];

                $data = array(
                    $billItemId,
                    (float)$lumpSumPercent->rate,
                    (float)$lumpSumPercent->percentage,
                    (float)$lumpSumPercent->amount,
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s'),
                    $this->userId,
                    $this->userId
                );

                $insertValues = array_merge($insertValues, $data);

                $questionMarks[] = '('.implode(',', array_fill(0, count($data), '?')).')';
            }

            unset($rate);
        }

        if(!empty($insertValues))
        {
            $stmt = $this->pdo->prepare("INSERT INTO ".BillItemLumpSumPercentageTable::getInstance()->getTableName()."
            (bill_item_id, rate, percentage, amount, created_at, updated_at, created_by, updated_by)
            VALUES " . implode(',', $questionMarks));

            $stmt->execute($insertValues);
        }
    }

    protected function processPrimeCostRate( Array $primeCostRates )
    {
        $insertValues  = array();
        $questionMarks = array();

        foreach($primeCostRates as $primeCostRate)
        {
            if(array_key_exists((int)$primeCostRate->bill_item_id, $this->billItemIds))
            {
                $billItemId = $this->billItemIds[(int)$primeCostRate->bill_item_id];

                $data = array(
                    $billItemId,
                    (float)$primeCostRate->supply_rate,
                    (float) ($primeCostRate->wastage_percentage ?? 0),
                    (float) ($primeCostRate->wastage_amount ?? 0),
                    (float) ($primeCostRate->labour_for_installation ?? 0),
                    (float) ($primeCostRate->other_cost ?? 0),
                    (float) ($primeCostRate->profit_percentage ?? 0),
                    (float) ($primeCostRate->profit_amount ?? 0),
                    (float) ($primeCostRate->total ?? 0),
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s'),
                    $this->userId,
                    $this->userId
                );

                $insertValues = array_merge($insertValues, $data);

                $questionMarks[] = '('.implode(',', array_fill(0, count($data), '?')).')';
            }

            unset($rate);
        }

        if(!empty($insertValues))
        {
            $stmt = $this->pdo->prepare("INSERT INTO ".BillItemPrimeCostRateTable::getInstance()->getTableName()."
            (bill_item_id, supply_rate, wastage_percentage, wastage_amount, labour_for_installation, other_cost, profit_percentage, profit_amount, total, created_at, updated_at, created_by, updated_by)
            VALUES " . implode(',', $questionMarks));

            $stmt->execute($insertValues);
        }
    }

    protected function processRate( Array $rates )
    {
        $insertValues  = array();
        $questionMarks = array();

        foreach($rates as $rate)
        {
            if(array_key_exists((int)$rate->relation_id, $this->billItemIds))
            {
                $relationId = $this->billItemIds[(int) $rate->relation_id];

                $data = array(
                    $relationId,
                    (string)$rate->value,
                    (float)$rate->final_value,
                    (string)$rate->column_name,
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s'),
                    $this->userId,
                    $this->userId
                );

                $insertValues = array_merge($insertValues, $data);

                $questionMarks[] = '('.implode(',', array_fill(0, count($data), '?')).')';
            }

            unset($rate);
        }

        if(!empty($insertValues))
        {
            $stmt = $this->pdo->prepare("INSERT INTO ".BillItemFormulatedColumnTable::getInstance()->getTableName()."
            (relation_id, value, final_value, column_name, created_at, updated_at, created_by, updated_by)
            VALUES " . implode(',', $questionMarks));

            $stmt->execute($insertValues);
        }
    }

    protected function storeUnitInformation(SimpleXMLElement $column, $billColumnSettingId)
    {
        $unitsXml = $column->{sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTINGUNITS}->{sfBuildspaceExportBillXML::TAG_UNIT} ? $column->{sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTINGUNITS}->{sfBuildspaceExportBillXML::TAG_UNIT} : array();

        foreach($unitsXml as $unit)
        {
            SubPackageUnitInformationTable::getOrNew($billColumnSettingId, (string)$unit);
        }
    }
}
