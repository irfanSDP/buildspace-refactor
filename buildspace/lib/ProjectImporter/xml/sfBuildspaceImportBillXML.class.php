<?php
class sfBuildspaceImportBillXML extends sfBuildspaceXMLParser
{
    public $rootProject;
    public $projectUniqueId;
    public $projectId;
    public $originalProjectId;
    public $buildspaceId;
    public $breakdownIds = array();
    public $unitIds = array();
    public $versionIds = array();
    public $billItemIds = array();
    public $columnSettingIds = array();
    public $elementIds = array();
    public $typeRefIds = array();
    public $billId;
    public $userId;

    protected $billSetting;
    protected $elements;
    protected $items;
    protected $billColumnSettings;
    protected $billType;
    protected $layoutSetting;
    protected $units;
    protected $collectionPages;
    protected $billPages;
    protected $billPagesPool = array();
    protected $billCollectionPool = array();

    function __construct( $userId, $filename = null, $uploadPath = null, $project = false, $breakdownIds = array(), $unitIds = array(), $versionIds = array(), $extension = null, $deleteFile = null )
    {
        if(!$project)
            throw new Exception(ExportedFile::ERROR_MSG_BILL_IMPORT_ERROR);

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        
        $this->userId = $userId;

        parent::__construct( $filename, $uploadPath, $extension, $deleteFile );

        $this->breakdownIds = $breakdownIds;

        $this->unitIds = $unitIds;

        $this->versionIds = $versionIds;

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

        $this->billSetting = ($xmlData->{sfBuildspaceExportBillXML::TAG_BILLSETTING}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_BILLSETTING}->children() : false;

        $this->billColumnSettings = ($xmlData->{sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTING}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTING}->children() : false;

        $this->billType = ($xmlData->{sfBuildspaceExportBillXML::TAG_BILLTYPE}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_BILLTYPE}->children() : false;

        $this->layoutSetting = ($xmlData->{sfBuildspaceExportBillXML::TAG_LAYOUTSETTING}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_LAYOUTSETTING}->children() : false;

        $this->units = ($xmlData->{sfBuildspaceExportBillXML::TAG_UNITOFMEASUREMENT}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_UNITOFMEASUREMENT}->children() : false;

        $this->elements = ($xmlData->{sfBuildspaceExportBillXML::TAG_ELEMENTS}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_ELEMENTS}->children() : false;

        $this->items = ($xmlData->{sfBuildspaceExportBillXML::TAG_ITEMS}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_ITEMS}->children() : false;

        $originalBillId = (int) $xmlData->attributes()->billId;

        if(!array_key_exists($originalBillId, $this->breakdownIds))
        {
            throw new Exception('Bill Id '.$originalBillId.' does not exist in the system');
        }

        $this->billId = $this->breakdownIds[$originalBillId];
    }

    public function process()
    {
        if($this->billSetting)
            $this->processBillSetting();

        if($this->billColumnSettings)
            $this->processBillColumnSettings();

        if($this->billType)
            $this->processBillType();

        if($this->layoutSetting)
        {
            $this->processLayoutSetting();
        }

        if($this->elements)
            $this->processElements();

        if($this->units)
            $this->processUnits();

        if($this->items)
            $this->processItems();

        if(count($this->billPagesPool))
            $this->processBillPages();

        $this->endReader();

        return true;
    }

    public function processBillSetting()
    {
        if($this->billSetting->id)
        {
            unset($this->billSetting->id);
        }

        $this->billSetting->project_structure_id = $this->breakdownIds[(int) $this->billSetting->project_structure_id ];
        $this->billSetting->created_at = 'NOW()';
        $this->billSetting->updated_at = 'NOW()';
        $this->billSetting->created_by = $this->userId;
        $this->billSetting->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $this->billSetting, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillSettingTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset($this->billSetting, $stmt);
    }

    public function processBillType()
    {
        if($this->billType->id)
        {
            unset($this->billType->id);
        }

        $this->billType->project_structure_id = $this->breakdownIds[(int) $this->billType->project_structure_id ];
        $this->billType->created_at = 'NOW()';
        $this->billType->updated_at = 'NOW()';
        $this->billType->created_by = $this->userId;
        $this->billType->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $this->billType, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillTypeTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset($this->billType, $stmt);
    }

    public function processLayoutSetting()
    {
        if($this->layoutSetting->id)
        {
            $originalId = (int) $this->layoutSetting->id;

            unset($this->layoutSetting->id);
        }

        $billPhrase = (array_key_exists(sfBuildspaceExportBillXML::TAG_PHRASE, $this->layoutSetting)) ? true : false;

        if($billPhrase)
        {
            $billPhrase = $this->layoutSetting->{sfBuildspaceExportBillXML::TAG_PHRASE}->children();
        }
        else
        {
            $billPhrase = false;
        }

        $headSetting = (array_key_exists(sfBuildspaceExportBillXML::TAG_HEADSETTING, $this->layoutSetting)) ? true : false;

        if($headSetting)
        {
            $headSetting = $this->layoutSetting->{sfBuildspaceExportBillXML::TAG_HEADSETTING}->children();
        }
        else
        {
            $headSetting = false;
        }

        $this->layoutSetting->bill_id = $this->breakdownIds[(int) $this->layoutSetting->bill_id ];
        $this->layoutSetting->add_cont = (int) $this->layoutSetting->add_cont;
        $this->layoutSetting->print_element_header = (int) $this->layoutSetting->print_element_header;
        $this->layoutSetting->print_element_grid = (int) $this->layoutSetting->print_element_grid;
        $this->layoutSetting->print_element_grid_once = (int) $this->layoutSetting->print_element_grid_once;
        $this->layoutSetting->created_at = 'NOW()';
        $this->layoutSetting->updated_at = 'NOW()';
        $this->layoutSetting->created_by = $this->userId;
        $this->layoutSetting->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $this->layoutSetting, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillLayoutSettingTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        $returningId = $stmt->returningIds[0];

        if($billPhrase)
        {
            $billPhrase->bill_layout_setting_id = $returningId;
            $billPhrase->created_at = 'NOW()';
            $billPhrase->updated_at = 'NOW()';
            $billPhrase->created_by = $this->userId;
            $billPhrase->updated_by = $this->userId;

            $dataAndStructure = parent::generateArrayOfSingleData( $billPhrase, true );

            $stmt->createInsert(BillLayoutPhraseTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            unset($billPhrase);
        }

        if($headSetting)
        {
            foreach($headSetting as $head)
            {
                $item = $head->children();

                $item->bill_layout_setting_id = $returningId;
                $item->created_at = 'NOW()';
                $item->updated_at = 'NOW()';
                $item->created_by = $this->userId;
                $item->updated_by = $this->userId;

                $dataAndStructure = parent::generateArrayOfSingleData( $item, true );

                $stmt->createInsert(BillLayoutHeadSettingTable::getInstance()->getTableName(), $dataAndStructure['structure']);

                $stmt->addRecord($dataAndStructure['data']);

                $stmt->save();

                unset($head, $item);
            }

            unset($headSetting);
        }

        unset($this->layoutSetting, $stmt);
    }

    public function processBillColumnSettings()
    {
        foreach($this->billColumnSettings as $column)
        {
            if($column->id)
            {
                $originalId = (int) $column->id;

                unset($column->id);
            }

            $column->project_structure_id = $this->breakdownIds[(int) $column->project_structure_id ];
            $column->created_at = 'NOW()';
            $column->updated_at = 'NOW()';
            $column->created_by = $this->userId;
            $column->updated_by = $this->userId;
            $column->use_original_quantity = 1;
            $column->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $originalId, $this->originalProjectId);
            $column->remeasurement_quantity_enabled = false;

            $dataAndStructure = parent::generateArrayOfSingleData( $column, true );

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

        unset($this->billColumnSettings);
    }

    public function processElements()
    {
        foreach($this->elements as $element)
        {
            if($element->id)
            {
                $originalId = (int) $element->id;

                unset($element->id);
            }

            $billPages = (array_key_exists(sfBuildspaceExportBillXML::TAG_BILLPAGES, $element)) ? true : false;

            if($billPages)
            {
                array_push($this->billPagesPool, $element->{sfBuildspaceExportBillXML::TAG_BILLPAGES}->children());
            }

            $collectionPages = (array_key_exists(sfBuildspaceExportBillXML::TAG_COLLECTIONPAGES, $element)) ? true : false;

            if($collectionPages)
            {
                $this->collectionPages = $element->{sfBuildspaceExportBillXML::TAG_COLLECTIONPAGES}->children();
            }
            else
            {
                $this->collectionPages = false;
            }

            if(!$element->project_structure_id)
            {
                $element->project_structure_id = $this->billId;
            }
            else
            {
                $element->project_structure_id = $this->breakdownIds[(int) $element->project_structure_id ];
            }
            
            $element->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $originalId, $this->originalProjectId);
            $element->created_at = 'NOW()';
            $element->updated_at = 'NOW()';
            $element->created_by = $this->userId;
            $element->updated_by = $this->userId;

            $dataAndStructure = parent::generateArrayOfSingleData( $element, true );

            $stmt = new sfImportStatementGenerator();

            $stmt->createInsert(BillElementTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $this->elementIds[$originalId] = $stmt->returningIds[0];

            if($this->collectionPages)
                $this->processCollectionPages();

            unset($element);
        }

        unset($this->elements);
    }

    public function processCollectionPages()
    {
        foreach($this->collectionPages as $pages)
        {
            $page = $pages->children();

            if(array_key_exists((int) $page->revision_id, $this->versionIds))
            {
                $stmt = $this->pdo->prepare("SELECT * FROM ".BillCollectionPageTable::getInstance()->getTableName()." c
                WHERE c.element_id = :element_id AND c.revision_id = :revision_id AND c.page_no = :page_no");

                $stmt->execute(array(
                    'element_id' => $this->elementIds[(int) $page->element_id],
                    'revision_id' => $this->versionIds[(int) $page->revision_id],
                    'page_no' => (string) $page->page_no
                ));

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if(!$result)
                {
                    $page->element_id = $this->elementIds[(int) $page->element_id];
                    $page->revision_id = $this->versionIds[(int) $page->revision_id];
                    $page->created_at = 'NOW()';
                    $page->updated_at = 'NOW()';

                    $dataAndStructure = parent::generateArrayOfSingleData( $page, true );

                    $stmt = new sfImportStatementGenerator();

                    $stmt->createInsert(BillCollectionPageTable::getInstance()->getTableName(), $dataAndStructure['structure']);

                    $stmt->addRecord($dataAndStructure['data']);

                    $stmt->save();
                }
            }

            unset($page);
            unset($pages);
        }
    }

    public function processBillPages()
    {
        foreach($this->billPagesPool as $billPagesCollectionItem)
        {
            foreach($billPagesCollectionItem as $billPages)
            {
                $pages = $billPages->children();

                if(array_key_exists((int) $pages->revision_id, $this->versionIds))
                {
                    if($pages->id)
                    {
                        $pagesOriginalId = (int) $pages->id;

                        unset($pages->id);
                    }

                    $pageItems = (array_key_exists(sfBuildspaceExportBillXML::TAG_BILLPAGE, $pages)) ? true : false;

                    if($pageItems)
                    {
                        $pageItems = $pages->{sfBuildspaceExportBillXML::TAG_BILLPAGE};
                    }
                    else
                    {
                        $pageItems = false;
                    }

                    $pages->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $pagesOriginalId, $this->originalProjectId);
                    $pages->element_id = $this->elementIds[(int) $pages->element_id];
                    $pages->revision_id = $this->versionIds[(int) $pages->revision_id];

                    if((int) $pages->new_revision_id != null && (array_key_exists((int) $pages->new_revision_id, $this->versionIds)))
                    {
                        $pages->new_revision_id = $this->versionIds[(int) $pages->new_revision_id];
                    }
                    else
                    {
                        unset($pages->new_revision_id);
                    }

                    $pages->created_at = 'NOW()';
                    $pages->updated_at = 'NOW()';

                    $dataAndStructure = parent::generateArrayOfSingleData( $pages, true );

                    $stmt = new sfImportStatementGenerator();

                    $stmt->createInsert(BillPageTable::getInstance()->getTableName(), $dataAndStructure['structure']);

                    $stmt->addRecord($dataAndStructure['data']);

                    $stmt->save();

                    $returningId = $stmt->returningIds[0];

                    if($pageItems)
                    {
                        foreach($pageItems as $pageItem)
                        {
                            $items = $pageItem->children();

                            foreach($items as $item)
                            {
                                if(array_key_exists((int) $item->bill_item_id, $this->billItemIds))
                                {
                                    if($item->id)
                                    {
                                        $pageItemOriginalId = (int) $item->id;

                                        unset($item->id);
                                    }

                                    $item->bill_page_id = $returningId;
                                    $item->bill_item_id = $this->billItemIds[(int) $item->bill_item_id];
                                    $item->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $pageItemOriginalId, $this->originalProjectId);
                                    $item->created_at = 'NOW()';
                                    $item->updated_at = 'NOW()';

                                    $dataAndStructure = parent::generateArrayOfSingleData( $item, true );

                                    $stmt->createInsert(BillPageItemTable::getInstance()->getTableName(), $dataAndStructure['structure']);

                                    $stmt->addRecord($dataAndStructure['data']);

                                    $stmt->save();
                                }
                            }

                            unset($item);
                            unset($pageItem);
                        }

                        unset($pageItem);
                    }
                }
            }
        }
    }

    public function processItems()
    {
        $rateLogData = array();

        foreach($this->items as $item)
        {
            if($item->id)
            {
                $originalId = (int) $item->id;

                $item->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $originalId, $this->originalProjectId);

                unset($item->id);
            }

            $item->project_revision_id = $this->versionIds[(int) $item->project_revision_id];

            $root = false;

            if($originalId != (int) $item->root_id)
            {
                $item->root_id = $this->billItemIds[(int) $item->root_id];
            }
            else
            {
                $root = true;
            }

            $typeRef = (array_key_exists(sfBuildspaceExportBillXML::TAG_TYPEREFERENCES, $item)) ? true : false;

            if($typeRef)
            {
                $typeRef = $item->{sfBuildspaceExportBillXML::TAG_TYPEREFERENCES}->children();
            }
            else
            {
                $typeRef = false;
            }

            //Process Item LumpSumpPercent
            $lumpSumpPercent = (array_key_exists(sfBuildspaceExportBillXML::TAG_ITEM_LS_PERCENT, $item)) ? true : false;

            if($lumpSumpPercent && ($item->type == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT))
            {
                $lumpSumpPercent = $item->{sfBuildspaceExportBillXML::TAG_ITEM_LS_PERCENT}->children();

                unset($lumpSumpPercent->percentage);
                unset($lumpSumpPercent->amount);
            }
            else
            {
                $lumpSumpPercent = false;
            }

            //Process Item PrimeCost
            $primeCostRate = (array_key_exists(sfBuildspaceExportBillXML::TAG_ITEM_PC_RATE, $item)) ? true : false;

            if($primeCostRate && ($item->type == BillItem::TYPE_ITEM_PC_RATE))
            {
                $primeCostRate = $item->{sfBuildspaceExportBillXML::TAG_ITEM_PC_RATE}->children();
                $rates = $item->{sfBuildspaceExportBillXML::TAG_RATES}->children();
                
                foreach($typeRef as $type)
                {
                    $type->grand_total = $type->grand_total_after_markup = number_format((float) $rates->final_value * (float) $type->total_quantity, 2,'.','');
                }

            }
            else
            {
                $primeCostRate = false;
            }

            //Process Item LumpSumpPercent
            $rate = (array_key_exists(sfBuildspaceExportBillXML::TAG_RATES, $item)) ? true : false;

            if($rate)
            {
                $rate = $item->{sfBuildspaceExportBillXML::TAG_RATES}->children();
            }
            else
            {
                $rate = false;
            }

            $uomId = (int) $item->uom_id;

            if($uomId)
            {
                $item->uom_id = $this->unitIds[$uomId];
            }

            $item->element_id = $this->elementIds[(int) $item->element_id];
            $item->created_at = 'NOW()';
            $item->updated_at = 'NOW()';
            $item->created_by = $this->userId;
            $item->updated_by = $this->userId;

            if(array_key_exists((int) $item->deleted_at_project_revision_id, $this->versionIds))
            {
                $item->deleted_at_project_revision_id = $this->versionIds[(int) $item->deleted_at_project_revision_id];
            }
			else 
			{
				unset($item->deleted_at_project_revision_id);
                unset($item->project_revision_deleted_at);
			}

            $dataAndStructure = parent::generateArrayOfSingleData( $item, true );

            $stmt = new sfImportStatementGenerator();

            $stmt->createInsert(BillItemTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $newItemId = $stmt->returningIds[0];

            if($root)
            {
                //Update Root Id
                $stmt->updateRecord(BillItemTable::getInstance()->getTableName(), 
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

            if($typeRef)
            {
                $this->processTypeRef($typeRef);
            }

            if($lumpSumpPercent)
            {
                $this->processLumpSumpPercent($lumpSumpPercent);
            }

            if($primeCostRate)
            {
                $this->processPrimeCostRate($primeCostRate);
            }

            if($rate)
            {
                $rateLogData[$this->billItemIds[(int) $rate->relation_id]] = (double)$rate->final_value;

                $this->processRate($rate);
            }

            unset($item);
        }

        BillItemRateLogTable::insertBatchLogByBillId($this->billId, $rateLogData);

        unset($this->items);
    }

    public function processRate( $rate )
    {
        if(!array_key_exists((int)$rate->relation_id, $this->billItemIds))
        {
            return false;
        }

        $rate->relation_id = $this->billItemIds[(int) $rate->relation_id];
        $rate->created_at = 'NOW()';
        $rate->updated_at = 'NOW()';
        $rate->created_by = $this->userId;
        $rate->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $rate, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillItemFormulatedColumnTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset($rate, $stmt);

        return true;
    }

    public function processLumpSumpPercent( $lumpSumpPercent )
    {
        if(!array_key_exists((int)$lumpSumpPercent->bill_item_id, $this->billItemIds))
        {
            return false;
        }

        $lumpSumpPercent->bill_item_id = $this->billItemIds[(int) $lumpSumpPercent->bill_item_id];
        $lumpSumpPercent->created_at = 'NOW()';
        $lumpSumpPercent->updated_at = 'NOW()';
        $lumpSumpPercent->created_by = $this->userId;
        $lumpSumpPercent->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $lumpSumpPercent, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillItemLumpSumPercentageTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset($lumpSumpPercent, $stmt);

        return true;
    }

    public function processPrimeCostRate( $primeCostRate )
    {
        if(!array_key_exists((int)$primeCostRate->bill_item_id, $this->billItemIds))
        {
            return false;
        }
        
        $primeCostRate->bill_item_id = $this->billItemIds[(int) $primeCostRate->bill_item_id];
        $primeCostRate->created_at = 'NOW()';
        $primeCostRate->updated_at = 'NOW()';
        $primeCostRate->created_by = $this->userId;
        $primeCostRate->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData( $primeCostRate, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillItemPrimeCostRateTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset($primeCostRate, $stmt);

        return true;
    }

    public function processTypeRef( $typeRef )
    {
        foreach($typeRef as $type)
        {
            if($type->id)
            {
                $originalId = (int) $type->id;

                unset($type->id);
            }

            $type->include = ((int) $type->include) ? 1 : 0;

            $qty = (array_key_exists(sfBuildspaceExportBillXML::TAG_QTY, $type)) ? true : false;

            if($qty)
            {
                $qty = $type->{sfBuildspaceExportBillXML::TAG_QTY}->children();
            }
            else
            {
                $qty = false;
            }

            $type->bill_item_id = $this->billItemIds[(int) $type->bill_item_id];
            $type->bill_column_setting_id = $this->columnSettingIds[(int) $type->bill_column_setting_id];
            $type->created_at = 'NOW()';
            $type->updated_at = 'NOW()';
            $type->created_by = $this->userId;
            $type->updated_by = $this->userId;

            $dataAndStructure = parent::generateArrayOfSingleData( $type, true );

            $stmt = new sfImportStatementGenerator();

            $stmt->createInsert(BillItemTypeReferenceTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $this->typeRefIds[$originalId] = $stmt->returningIds[0];

            unset($stmt);

            if($qty)
            {
                $this->processQty($qty);
            }

            unset($type);
        }

        unset($typeRef);

        return true;
    }

    public function processQty( $qty )
    {
        if($qty->id)
        {
            $originalId = (int) $qty->id;

            unset($qty->id);
        }

        $qty->relation_id = $this->typeRefIds[(int) $qty->relation_id];
        $qty->created_at = 'NOW()';
        $qty->updated_at = 'NOW()';
        $qty->created_by = $this->userId;
        $qty->updated_by = $this->userId;
        $qty->column_name = BillItemTypeReference::FORMULATED_COLUMN_QTY_PER_UNIT;
        $qty->value = $qty->final_value;
        $qty->has_build_up = false;
        $qty->linked = false;

        $dataAndStructure = parent::generateArrayOfSingleData( $qty, true );

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(BillItemTypeReferenceFormulatedColumnTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset($qty);
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
                    $unit->display = true;
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

    protected function storeUnitInformation(SimpleXMLElement $column, int $billColumnSettingId)
    {
        $unitsXml = $column->{sfBuildspaceExportBillXML::TAG_BILLCOLUMNSETTINGUNITS}->{sfBuildspaceExportBillXML::TAG_UNIT} ?? array();

        foreach($unitsXml as $unit)
        {
            SubPackageUnitInformationTable::getOrNew($billColumnSettingId, (string)$unit);
        }
    }

}
