<?php

class sfBuildspaceImportScheduleOfRateBillXML extends sfBuildspaceXMLParser
{
    public $rootProject;
    public $projectUniqueId;
    public $projectId;
    public $originalProjectId;
    public $buildspaceId;
    public $breakdownIds = array();
    public $unitIds = array();
    public $billItemIds = array();
    public $columnSettingIds = array();
    public $elementIds = array();
    public $typeRefIds = array();
    public $billId;
    public $userId;

    protected $scheduleOfRateBill;
    protected $elements;
    protected $items;
    protected $layoutSetting;
    protected $units;

    public function __construct(
        $userId,
        $filename = null,
        $uploadPath = null,
        $project = false,
        $breakdownIds = array(),
        $unitIds = array(),
        $extension = null,
        $deleteFile = null
    ) {
        if (!$project)
        {
            throw new Exception(ExportedFile::ERROR_MSG_BILL_IMPORT_ERROR);
        }

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->userId = $userId;

        parent::__construct($filename, $uploadPath, $extension, $deleteFile);

        $this->breakdownIds = $breakdownIds;

        $this->unitIds = $unitIds;

        $this->extractData();

        $extractedOriginId = ProjectStructureTable::extractOriginId($project['tender_origin_id']);

        $this->projectId = $project['id'];

        $this->projectUniqueId = $project['MainInformation']['unique_id'];

        $this->originalProjectId = $extractedOriginId['project_id'];
    }

    public function extractData()
    {
        parent::read();

        $xmlData = parent::getProcessedData();

        $this->buildspaceId = $xmlData->attributes()->buildspaceId;

        $this->scheduleOfRateBill = ( $xmlData->{sfBuildspaceExportBillXML::TAG_BILLSETTING}->count() > 0 ) ? $xmlData->{sfBuildspaceExportBillXML::TAG_BILLSETTING}->children() : false;

        $this->layoutSetting = ( $xmlData->{sfBuildspaceExportScheduleOfRateBillXML::TAG_LAYOUTSETTING}->count() > 0 ) ? $xmlData->{sfBuildspaceExportScheduleOfRateBillXML::TAG_LAYOUTSETTING}->children() : false;

        $this->units = ( $xmlData->{sfBuildspaceExportScheduleOfRateBillXML::TAG_UNITOFMEASUREMENT}->count() > 0 ) ? $xmlData->{sfBuildspaceExportScheduleOfRateBillXML::TAG_UNITOFMEASUREMENT}->children() : false;

        $this->elements = ( $xmlData->{sfBuildspaceExportScheduleOfRateBillXML::TAG_ELEMENTS}->count() > 0 ) ? $xmlData->{sfBuildspaceExportScheduleOfRateBillXML::TAG_ELEMENTS}->children() : false;

        $this->items = ( $xmlData->{sfBuildspaceExportScheduleOfRateBillXML::TAG_ITEMS}->count() > 0 ) ? $xmlData->{sfBuildspaceExportScheduleOfRateBillXML::TAG_ITEMS}->children() : false;

        $originalBillId = (int) $xmlData->attributes()->billId;

        if(!array_key_exists($originalBillId, $this->breakdownIds))
        {
            throw new Exception('Bill Id '.$originalBillId.' does not exist in the system');
        }

        $this->billId = $this->breakdownIds[$originalBillId];
    }

    public function process()
    {
        $this->processScheduleOfMaterialBillSetting();

        if ($this->layoutSetting)
        {
            $this->processLayoutSetting();
        }

        if ($this->elements)
        {
            $this->processElements();
        }

        if ($this->units)
        {
            $this->processUnits();
        }

        if ($this->items)
        {
            $this->processItems();
        }

        $this->endReader();

        return true;
    }

    public function processScheduleOfMaterialBillSetting()
    {
        if ($this->scheduleOfRateBill->id)
        {
            unset( $this->scheduleOfRateBill->id );
        }

        $this->scheduleOfRateBill->project_structure_id = $this->breakdownIds[(int) $this->scheduleOfRateBill->project_structure_id];
        $this->scheduleOfRateBill->description          = (string) $this->scheduleOfRateBill->description;
        $this->scheduleOfRateBill->created_at           = 'NOW()';
        $this->scheduleOfRateBill->updated_at           = 'NOW()';
        $this->scheduleOfRateBill->created_by           = $this->userId;
        $this->scheduleOfRateBill->updated_by           = $this->userId;

        unset( $this->scheduleOfRateBill->deleted_at );

        $dataAndStructure = parent::generateArrayOfSingleData($this->scheduleOfRateBill, true);

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(ScheduleOfRateBillTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset( $this->scheduleOfRateBill, $stmt );
    }

    public function processLayoutSetting()
    {
        $billPhrase = ( array_key_exists(sfBuildspaceExportScheduleOfRateBillXML::TAG_PHRASE,
            $this->layoutSetting) ) ? true : false;

        if ($billPhrase)
        {
            $billPhrase = $this->layoutSetting->{sfBuildspaceExportScheduleOfRateBillXML::TAG_PHRASE}->children();
        }
        else
        {
            $billPhrase = false;
        }

        $headSettings = ( array_key_exists(sfBuildspaceExportScheduleOfRateBillXML::TAG_HEADSETTING,
            $this->layoutSetting) ) ? true : false;

        if ($headSettings)
        {
            $headSettings = $this->layoutSetting->{sfBuildspaceExportScheduleOfRateBillXML::TAG_HEADSETTING}->children();
        }
        else
        {
            $headSettings = false;
        }

        $this->layoutSetting->project_structure_id    = $this->breakdownIds[(int) $this->layoutSetting->project_structure_id];
        $this->layoutSetting->comma_total             = (string) $this->layoutSetting->comma_total;
        $this->layoutSetting->comma_rate              = (string) $this->layoutSetting->comma_rate;
        $this->layoutSetting->print_element_grid      = (int) $this->layoutSetting->print_element_grid;
        $this->layoutSetting->includeIAndOForBillRef  = (string) $this->layoutSetting->includeIAndOForBillRef;
        $this->layoutSetting->print_element_grid_once = (string) $this->layoutSetting->print_element_grid_once;
        $this->layoutSetting->page_no_prefix          = (string) $this->layoutSetting->page_no_prefix;
        $this->layoutSetting->align_element_to_left   = (string) $this->layoutSetting->align_element_to_left;
        $this->layoutSetting->created_at              = 'NOW()';
        $this->layoutSetting->updated_at              = 'NOW()';
        $this->layoutSetting->created_by              = $this->userId;
        $this->layoutSetting->updated_by              = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData($this->layoutSetting, true);

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(
            ScheduleOfRateBillLayoutSettingTable::getInstance()->getTableName(),
            $dataAndStructure['structure']
        );

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        $returningId = $stmt->returningIds[0];

        if ($billPhrase)
        {
            $billPhrase->schedule_of_rate_bill_layout_setting_id = $returningId;
            $billPhrase->created_at            = 'NOW()';
            $billPhrase->updated_at            = 'NOW()';
            $billPhrase->created_by            = $this->userId;
            $billPhrase->updated_by            = $this->userId;

            $dataAndStructure = parent::generateArrayOfSingleData($billPhrase, true);

            $stmt->createInsert(
                ScheduleOfRateBillLayoutPhraseSettingTable::getInstance()->getTableName(),
                $dataAndStructure['structure']
            );

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            unset( $billPhrase );
        }

        if ($headSettings and is_object($headSettings))
        {
            foreach ($headSettings as $headSetting)
            {
                $item = $headSetting->children();

                $item->schedule_of_rate_bill_layout_setting_id = $returningId;
                $item->created_at            = 'NOW()';
                $item->updated_at            = 'NOW()';
                $item->created_by            = $this->userId;
                $item->updated_by            = $this->userId;

                $dataAndStructure = parent::generateArrayOfSingleData($item, true);

                $stmt->createInsert(
                    ScheduleOfRateBillLayoutHeadSettingTable::getInstance()->getTableName(),
                    $dataAndStructure['structure']
                );

                $stmt->addRecord($dataAndStructure['data']);

                $stmt->save();

                unset( $head, $item );
            }

            unset( $headSettings );
        }

        unset( $this->layoutSetting, $stmt );
    }

    public function processElements()
    {
        $originalId = null;

        foreach ($this->elements as $element)
        {
            if ($element->id)
            {
                $originalId = (int) $element->id;

                unset( $element->id );
            }

            if (!$element->project_structure_id)
            {
                $element->project_structure_id = $this->billId;
            }
            else
            {
                $element->project_structure_id = $this->breakdownIds[(int) $element->project_structure_id];
            }

            $element->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $originalId,
                $this->originalProjectId);
            $element->created_at       = 'NOW()';
            $element->updated_at       = 'NOW()';
            $element->created_by       = $this->userId;
            $element->updated_by       = $this->userId;

            $dataAndStructure = parent::generateArrayOfSingleData($element, true);

            $stmt = new sfImportStatementGenerator();

            $stmt->createInsert(
                ScheduleOfRateBillElementTable::getInstance()->getTableName(),
                $dataAndStructure['structure']
            );

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $this->elementIds[$originalId] = $stmt->returningIds[0];

            unset( $element );
        }

        unset( $this->elements );
    }

    public function processItems()
    {
        $originalId = null;

        foreach ($this->items as $item)
        {
            if ($item->id)
            {
                $originalId = (int) $item->id;

                $item->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId,
                    $originalId, $this->originalProjectId);

                unset( $item->id );
            }

            $root = false;

            if ($originalId != (int) $item->root_id)
            {
                $item->root_id = $this->billItemIds[(int) $item->root_id];
            }
            else
            {
                $root = true;
            }

            $uomId = (int) $item->uom_id;

            if ($uomId)
            {
                $item->uom_id = $this->unitIds[$uomId];
            }

            $item->element_id  = $this->elementIds[(int) $item->element_id];
            $item->created_at  = 'NOW()';
            $item->updated_at  = 'NOW()';
            $item->created_by  = $this->userId;
            $item->updated_by  = $this->userId;

            $dataAndStructure = parent::generateArrayOfSingleData($item, true);

            $stmt = new sfImportStatementGenerator();

            $stmt->createInsert(
                ScheduleOfRateBillItemTable::getInstance()->getTableName(),
                $dataAndStructure['structure']
            );

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $newItemId = $stmt->returningIds[0];

            if ($root)
            {
                //Update Root Id
                $stmt->updateRecord(ScheduleOfRateBillItemTable::getInstance()->getTableName(),
                    array(
                        'id' => $newItemId
                    ),
                    array(
                        'root_id' => $newItemId
                    )
                );
            }

            unset( $stmt );

            $this->billItemIds[$originalId] = $newItemId;

            unset( $item );
        }

        unset( $this->items );
    }

    public function getUnitBySymbolAndType($symbol, $type)
    {
        $stmt = $this->pdo->prepare("SELECT uom.id, uom.name, uom.symbol FROM " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom
        WHERE LOWER(uom.symbol) = :uom_symbol AND uom.type = :type");

        $stmt->execute(array(
            'uom_symbol' => strtolower($symbol),
            'type'       => $type
        ));

        $uom = $stmt->fetch(PDO::FETCH_ASSOC);

        return ( $uom ) ? $uom : false;
    }

    public function processUnits()
    {
        $originalId = null;

        foreach ($this->units as $unit)
        {
            if ($unit->id)
            {
                $originalId = (int) $unit->id;

                unset( $unit->id );
            }

            if (!array_key_exists($originalId, $this->unitIds))
            {
                if ($uom = $this->getUnitBySymbolAndType((string) $unit->symbol, (int) $unit->type))
                {
                    $this->unitIds[$originalId] = $uom['id'];
                }
                else
                {
                    $unit->display    = true;
                    $unit->created_at = 'NOW()';
                    $unit->updated_at = 'NOW()';
                    $unit->created_by = $this->userId;
                    $unit->updated_by = $this->userId;

                    $dataAndStructure = parent::generateArrayOfSingleData($unit, true);

                    $stmt = new sfImportStatementGenerator();

                    $stmt->createInsert(
                        UnitOfMeasurementTable::getInstance()->getTableName(),
                        $dataAndStructure['structure']
                    );

                    $stmt->addRecord($dataAndStructure['data']);

                    $stmt->save();

                    $this->unitIds[$originalId] = $stmt->returningIds[0];
                }
            }
            else
            {
                unset( $unit );
            }

        }

        unset( $this->units );
    }

}