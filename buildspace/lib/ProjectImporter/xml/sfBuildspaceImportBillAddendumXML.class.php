<?php
class sfBuildspaceImportBillAddendumXML extends sfBuildspaceXMLParser
{
    public $rootProject;
    public $projectUniqueId;
    public $buildspaceId;
    public $projectId;
    public $originalProjectId;
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
    protected $priorityToUpdateItems;
    protected $billColumnSettings;
    protected $billType;
    protected $layoutSetting;
    protected $units;
    protected $collectionPages;
    protected $billPages;
    protected $billPagesPool = array();
    protected $billCollectionPool = array();

    function __construct( $userId, $filename = null, $uploadPath = null, $project = false, $unitIds = array(), $versionIds = array(), $extension = null, $deleteFile = null ) 
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->unitIds = $unitIds;

        $this->versionIds = $versionIds;

        $extractedOriginId = ProjectStructureTable::extractOriginId($project['tender_origin_id']);

        $this->projectId = $project['id'];

        $this->projectUniqueId = $project['MainInformation']['unique_id'];

        $this->originalProjectId = $extractedOriginId['origin_id'];

        $this->userId = $userId;

        parent::__construct( $filename, $uploadPath, $extension, $deleteFile );

        $this->extractData();
    }

    public function extractData()
    {
        if(!$this->projectId)
            return false;

        $this->getBreakdownIds();

        parent::read();

        $xmlData = parent::getProcessedData();

        $this->buildspaceId = $xmlData->attributes()->buildspaceId;

        $this->layoutSetting = ($xmlData->{sfBuildspaceExportBillXML::TAG_LAYOUTSETTING}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_LAYOUTSETTING}->children() : false;

        $this->units = ($xmlData->{sfBuildspaceExportBillAddendumXML::TAG_UNITOFMEASUREMENT}->count() > 0) ? $xmlData->{sfBuildspaceExportBillAddendumXML::TAG_UNITOFMEASUREMENT}->children() : false;

        $this->deletedItems = ($xmlData->{sfBuildspaceExportBillAddendumXML::TAG_DELETEDITEM}->count() > 0) ? $xmlData->{sfBuildspaceExportBillAddendumXML::TAG_DELETEDITEM}->children() : false;

        $this->newItems = ($xmlData->{sfBuildspaceExportBillAddendumXML::TAG_NEWITEM}->count() > 0) ? $xmlData->{sfBuildspaceExportBillAddendumXML::TAG_NEWITEM}->children() : false;

        $this->affectedItems = ($xmlData->{sfBuildspaceExportBillAddendumXML::TAG_AFFECTEDITEM}->count() > 0) ? $xmlData->{sfBuildspaceExportBillAddendumXML::TAG_AFFECTEDITEM}->children() : false;

        $this->priorityToUpdateItems = ($xmlData->{sfBuildspaceExportBillAddendumXML::TAG_PRIORITYTOUPDATE}->count() > 0) ? $xmlData->{sfBuildspaceExportBillAddendumXML::TAG_PRIORITYTOUPDATE}->children() : false;

        $this->elements = ($xmlData->{sfBuildspaceExportBillXML::TAG_ELEMENTS}->count() > 0) ? $xmlData->{sfBuildspaceExportBillXML::TAG_ELEMENTS}->children() : false;

        $originalBillId = (int) $xmlData->attributes()->billId;

        if(!array_key_exists($originalBillId, $this->breakdownIds))
        {
            throw new Exception('Bill Id '.$originalBillId.' does not exist in the system');
        }
        
        $this->billId = $this->breakdownIds[$originalBillId];

        return true;
    }

    public function getBreakdownIds()
    {
        $stmt = $this->pdo->prepare("SELECT p.tender_origin_id, p.id FROM ".ProjectStructureTable::getInstance()->getTableName()." p
        WHERE p.root_id = :project_structure_id");

        $stmt->execute(array(
            'project_structure_id' => $this->projectId
        ));

        $breakdowns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($breakdowns as $breakdown)
        {
            $arrayOfIds = ProjectStructureTable::extractOriginId($breakdown['tender_origin_id']);

            $this->breakdownIds[$arrayOfIds['origin_id']] = $breakdown['id'];
        }
    }

    public function getElements()
    {
        $stmt = $this->pdo->prepare("SELECT e.tender_origin_id, e.id FROM ".BillElementTable::getInstance()->getTableName()." e
        WHERE e.project_structure_id = :project_structure_id");

        $stmt->execute(array(
            'project_structure_id' => $this->billId
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($elements as $element)
        {
            $arrayOfIds = ProjectStructureTable::extractOriginId($element['tender_origin_id']);

            $this->elementIds[$arrayOfIds['origin_id']] = $element['id'];
        }
    }

    public function getRootIds()
    {
        $stmt = $this->pdo->prepare("SELECT i.id, i.tender_origin_id FROM ".BillItemTable::getInstance()->getTableName()." i
        WHERE i.element_id IN (SELECT e.id FROM ".BillElementTable::getInstance()->getTableName()." e
        WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL
        ORDER BY e.priority) AND i.deleted_at IS NULL ORDER BY i.priority");

        $stmt->execute(array(
            'project_structure_id' => $this->billId
        ));

        $roots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($roots as $root)
        {
            $arrayOfIds = ProjectStructureTable::extractOriginId($root['tender_origin_id']);

            $this->billItemIds[$arrayOfIds['origin_id']] = $root['id'];
        }
    }

    public function getColumnSettingIds()
    {
        $stmt = $this->pdo->prepare("SELECT c.tender_origin_id, c.id FROM ".BillColumnSettingTable::getInstance()->getTableName()." c
        WHERE c.project_structure_id = :project_structure_id");

        $stmt->execute(array(
            'project_structure_id' => $this->billId
        ));

        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($columns as $column)
        {
            $arrayOfIds = ProjectStructureTable::extractOriginId($column['tender_origin_id']);

            $this->columnSettingIds[$arrayOfIds['origin_id']] = $column['id'];
        }
    }

    public function getVersionIds()
    {
        $stmt = $this->pdo->prepare("SELECT r.tender_origin_id, r.id FROM ".ProjectRevisionTable::getInstance()->getTableName()." r
        WHERE r.project_structure_id = :project_structure_id");

        $stmt->execute(array(
            'project_structure_id' => $this->projectId
        ));

        $revisions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $revisionIds = array();

        foreach($revisions as $revision)
        {
            $arrayOfIds = ProjectStructureTable::extractOriginId($revision['tender_origin_id']);

            $revisionIds[$arrayOfIds['origin_id']] = $revision['id'];
        }

        return $revisionIds;
    }

    public function process()
    {
        $this->getColumnSettingIds();

        $this->getElements();

        $this->getRootIds();

        if($this->layoutSetting)
        {
            $this->processLayoutSetting();
        }

        if($this->units)
            $this->processUnits();

        if($this->affectedItems)
            $this->processAffectedItems();

        if($this->deletedItems)
            $this->processDeletedItems();

        if($this->newItems)
            $this->processItems();

        if($this->priorityToUpdateItems)
            $this->processPriorityToUpdateItems();

        if($this->elements)
            $this->extractBillPages();

        if(count($this->billCollectionPool))
            $this->processCollectionPages();

        if(count($this->billPagesPool))
            $this->processBillPages();

        return true;
    }

    public function processDeletedItems()
    {
        foreach($this->deletedItems as $item)
        {
            $stmt = new sfImportStatementGenerator();

            $stmt->updateRecord(BillItemTable::getInstance()->getTableName(), 
                array(
                    'tender_origin_id' => ProjectStructureTable::generateTenderOriginId($this->buildspaceId, (int) $item->id, $this->originalProjectId)
                ), 
                array(
                    'deleted_at_project_revision_id' => $this->versionIds[(int) $item->deleted_at_project_revision_id],
                    'project_revision_deleted_at' => (string) $item->project_revision_deleted_at,
                    'grand_total_quantity' => 0,
                    'bill_ref_element_no' => null,
                    'bill_ref_char' => null,
                    'bill_ref_page_no' => null,
                    'grand_total' => 0,
                    'grand_total_after_markup' => 0
                )
            );

            $stmt->updateRecord(BillItemTypeReferenceTable::getInstance()->getTableName(), 
                array(
                    'bill_item_id' => $this->billItemIds[(int) $item->id]
                ), 
                array(
                    'deleted_at' => 'NOW()'
                )
            );

            $stmt->updateRecord(BillItemFormulatedColumnTable::getInstance()->getTableName(), 
                array(
                    'relation_id' => $this->billItemIds[(int) $item->id]
                ), 
                array(
                    'deleted_at' => 'NOW()'
                )
            );

            unset($item);
        }

        unset($this->items);
    }


    public function processAffectedItems()
    {
        foreach($this->affectedItems as $item)
        {
            $stmt = new sfImportStatementGenerator();

            $stmt->updateRecord(BillItemTable::getInstance()->getTableName(), 
                array(
                    'tender_origin_id' => ProjectStructureTable::generateTenderOriginId($this->buildspaceId, (int) $item->id, $this->originalProjectId)
                ), 
                array(
                    'priority' => (int) $item->priority,
                    'lft' => (int) $item->lft,
                    'rgt' => (int) $item->rgt,
                    'level' => (int) $item->level
                )
            );

            unset($item);
        }

        unset($this->affectedItems);
    }

    public function processPriorityToUpdateItems()
    {
        $stmt = $this->pdo->prepare("SELECT c.tender_origin_id, c.id FROM ".BillItemTable::getInstance()->getTableName()." c
            LEFT JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.id = c.element_id
            WHERE c.id = c.root_id AND e.project_structure_id = ".$this->billId." AND c.deleted_at IS NULL ORDER BY c.priority");

        $stmt->execute();

        $originIdToId = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        $priorityCaseStatement = '';

        $itemIds = array();

        foreach($this->priorityToUpdateItems as $item)
        {
            $priority = (int) $item->priority;
            
            $originId = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, (int) $item->id, $this->originalProjectId);
            
            $itemId = $originIdToId[$originId][0];

            $priorityCaseStatement.=" WHEN ".$itemId." THEN (".$priority.")";

            $itemIds[] = $itemId;
        }

        if($priorityCaseStatement)
        {
            //Update Root Priority
            $stmt = $this->pdo->prepare("UPDATE ".BillItemTable::getInstance()->getTableName()."
                SET priority = (CASE root_id".$priorityCaseStatement." END)
                WHERE root_id IN (".implode(',', $itemIds).")");

            $stmt->execute();
        }

        unset($this->priorityToUpdateItems, $originIdToId);
    }

    public function processBillColumnSettings()
    {
        $originalId = null;

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
            $column->remeasurement_quantity_enabled = false;

            $dataAndStructure = parent::generateArrayOfSingleData( $column, true );

            $stmt = new sfImportStatementGenerator();

            $stmt->createInsert(BillColumnSettingTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $this->columnSettingIds[$originalId] = $stmt->returningIds[0];

            unset($column);
        }

        unset($this->billColumnSettings);
    }

    public function extractBillPages()
    {
        foreach($this->elements as $element)
        {
            $billPages = (array_key_exists(sfBuildspaceExportBillAddendumXML::TAG_BILLPAGES, $element)) ? true : false;
            
            if($billPages)
            {
                array_push($this->billPagesPool, $element->{sfBuildspaceExportBillXML::TAG_BILLPAGES}->children());
            }

            $collectionPages = (array_key_exists(sfBuildspaceExportBillAddendumXML::TAG_COLLECTIONPAGES, $element)) ? true : false;

            if($collectionPages)
            {
                array_push($this->billCollectionPool, $element->{sfBuildspaceExportBillAddendumXML::TAG_COLLECTIONPAGES}->children());
            }

            unset($element);
        }

        unset($this->elements);
    }

    public function processCollectionPages()
    {
        $versionIds = $this->getVersionIds();

        foreach($this->billCollectionPool as $billCollectionPagePool)
        {
            foreach($billCollectionPagePool as $pages)
            {
                $page = $pages->children();

                if(array_key_exists((int) $page->revision_id, $versionIds))
                {
                    $stmt = $this->pdo->prepare("SELECT * FROM ".BillCollectionPageTable::getInstance()->getTableName()." c
                    WHERE c.element_id = :element_id AND c.revision_id = :revision_id AND c.page_no = :page_no");

                    $stmt->execute(array(
                        'element_id' => $this->elementIds[(int) $page->element_id],
                        'revision_id' => $versionIds[(int) $page->revision_id],
                        'page_no' => (string) $page->page_no
                    ));

                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if(!$result)
                    {
                        $page->element_id = $this->elementIds[(int) $page->element_id];
                        $page->revision_id = $versionIds[(int) $page->revision_id];
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
    }

    public function processBillPages()
    {
        foreach($this->billPagesPool as $billPagesPool)
        {
            foreach($billPagesPool as $billPages)
            {
                $pages = $billPages->children();

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

                if(array_key_exists((int) $pages->new_revision_id, $this->versionIds))
                {
                    $exist = false;
                    $stmt = $this->pdo->prepare("SELECT * FROM ".BillPageTable::getInstance()->getTableName()." p WHERE p.tender_origin_id = :tender_origin_id");
                    $stmt->execute(array(
                        'tender_origin_id' => ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $pagesOriginalId, $this->originalProjectId)
                    ));
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    $stmt = new sfImportStatementGenerator();

                    if($result)
                    {
                        $exist = true;

                        $returningId = $result['id'];

                        $stmt->updateRecord(BillPageTable::getInstance()->getTableName(), 
                            array(
                                'tender_origin_id' => ProjectStructureTable::generateTenderOriginId($this->buildspaceId, (int) $pagesOriginalId, $this->originalProjectId)
                            ), 
                            array(
                                'new_revision_id' => $this->versionIds[(int) $pages->new_revision_id]
                            )
                        );
                    }
                    else
                    {
                        $stmt = $this->pdo->prepare("SELECT id FROM ".ProjectRevisionTable::getInstance()->getTableName()." r WHERE r.tender_origin_id = :tender_origin_id");

                        $stmt->execute(array(
                            'tender_origin_id' => ProjectStructureTable::generateTenderOriginId($this->buildspaceId, (int) $pages->revision_id, $this->originalProjectId)
                        ));

                        $result = $stmt->fetch(PDO::FETCH_ASSOC);

                        $pages->element_id = $this->elementIds[(int) $pages->element_id];

                        $pages->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $pagesOriginalId, $this->originalProjectId);

                        $pages->revision_id = $result['id'];

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
                    }

                    if($pageItems)
                    {
                        foreach($pageItems as $pageItem)
                        {
                            $items = $pageItem->children();

                            foreach($items as $item)
                            {
                                if($exist)
                                {
                                    if(array_key_exists((int) $item->bill_item_id, $this->billItemIds) && (int) $item->new_item_from_new_revision)
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
                                else
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
                            }

                            unset($item);
                            unset($pageItem);
                        }

                        unset($pageItem);
                    }
                }
                else
                {
                    $stmt = $this->pdo->prepare("SELECT id FROM ".ProjectRevisionTable::getInstance()->getTableName()." r WHERE r.tender_origin_id = :tender_origin_id");

                    $stmt->execute(array(
                        'tender_origin_id' => ProjectStructureTable::generateTenderOriginId($this->buildspaceId, (int) $pages->revision_id, $this->originalProjectId)
                    ));

                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    $pages->element_id = $this->elementIds[(int) $pages->element_id];

                    $pages->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $pagesOriginalId, $this->originalProjectId);

                    $pages->revision_id = $result['id'];

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
        $originalId = null;

        foreach($this->newItems as $item)
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

            $typeRef = (array_key_exists(sfBuildspaceExportBillAddendumXML::TAG_TYPEREFERENCES, $item)) ? true : false;

            if($typeRef)
            {
                $typeRef = $item->{sfBuildspaceExportBillAddendumXML::TAG_TYPEREFERENCES}->children();
            }
            else
            {
                $typeRef = false;
            }

            //Process Item LumpSumpPercent
            $lumpSumpPercent = (array_key_exists(sfBuildspaceExportBillAddendumXML::TAG_ITEM_LS_PERCENT, $item)) ? true : false;

            if($lumpSumpPercent && ($item->type == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT))
            {
                $lumpSumpPercent = $item->{sfBuildspaceExportBillAddendumXML::TAG_ITEM_LS_PERCENT}->children();

                unset($lumpSumpPercent->percentage);
                unset($lumpSumpPercent->amount);
            }
            else
            {
                $lumpSumpPercent = false;
            }

            //Process Item PRimeCosst
            $primeCostRate = (array_key_exists(sfBuildspaceExportBillXML::TAG_ITEM_PC_RATE, $item)) ? true : false;

            if($primeCostRate && ($item->type == BillItem::TYPE_ITEM_PC_RATE))
            {
                $primeCostRate = $item->{sfBuildspaceExportBillXML::TAG_ITEM_PC_RATE}->children();
                
                foreach($typeRef as $type)
                {
                    $type->grand_total = $type->grand_total_after_markup = number_format((float) $primeCostRate->supply_rate * (float) $type->total_quantity, 2,'.','');
                }
            }
            else
            {
                $primeCostRate = false;
            }

            //Process Item LumpSumpPercent
            $rate = (array_key_exists(sfBuildspaceExportBillAddendumXML::TAG_RATES, $item)) ? true : false;

            if($rate)
            {
                $rate = $item->{sfBuildspaceExportBillAddendumXML::TAG_RATES}->children();
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

            if($root)
            {
                $priority = (int) $item->priority;

                $stmt = $this->pdo->prepare("UPDATE ".BillItemTable::getInstance()->getTableName()." SET priority = priority + 1
                    WHERE element_id = :element_id AND priority = :priority");

                $stmt->execute(array(
                    'priority' => $priority,
                    'element_id' => $this->elementIds[(int) $item->element_id]
                ));
            }

            $item->element_id = $this->elementIds[(int) $item->element_id];
            $item->created_at = 'NOW()';
            $item->updated_at = 'NOW()';
            $item->created_by = $this->userId;
            $item->updated_by = $this->userId;

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
                $this->processRate($rate);
            }

            unset($item);
        }

        unset($this->items);
    }

    public function processRate( $rate )
    {
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

        unset($rate);

        return true;
    }

    public function processLumpSumpPercent( $lumpSumpPercent )
    {
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

        unset($lumpSumpPercent);

        return true;
    }

    public function processPrimeCostRate( $primeCostRate )
    {
        $primeCostRate->bill_item_id = $this->billItemIds[(int) $primeCostRate->bill_item_id];
        $primeCostRate->total       = $primeCostRate->supply_rate;
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

            $qty = (array_key_exists(sfBuildspaceExportBillAddendumXML::TAG_QTY, $type)) ? true : false;

            if($qty)
            {
                $qty = $type->{sfBuildspaceExportBillAddendumXML::TAG_QTY}->children();
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

    public function processLayoutSetting()
    {
        $stmt = $this->pdo->prepare("DELETE FROM ".BillLayoutSettingTable::getInstance()->getTableName()." WHERE bill_id = :bill_id");
        $stmt->execute(array(
            'bill_id' => $this->billId
        ));

        if($this->layoutSetting->id)
        {
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

        if($headSetting && is_object($headSetting))
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

}
