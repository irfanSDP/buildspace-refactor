<?php

class sfBuildspaceImportClaimsXML extends sfBuildspaceXMLParser
{
    protected $pdo;

    public $exporterProjectOriginInformation;
    public $exporterOriginalProjectId;
    public $exporterBuildspaceId;
    public $targetProject;
    public $targetIsOriginProject  = false;
    public $targetClaimRevision;
    public $exporterClaimVersion;
    public $userId;
    public $attachmentPaths;
    public $exportedStandardClaims;
    public $exportedPreliminaryClaims;
    public $exportedVariationOrders;
    public $exportedVariationOrderItems;
    public $exportedVariationOrderClaimItems;
    public $exportedMaterialsOnSite;
    public $exportedMaterialOnSiteItems;
    public $billItemsMap           = array();
    public $billColumnSettingsMap  = array();
    public $claimTypeReferences    = array();
    public $variationOrdersMap     = array();
    public $variationOrderItemsMap = array();
    public $materialsOnSiteMap     = array();
    public $materialOnSiteItemsMap = array();
    public $exportedAttachments    = array();

    function __construct($filename = null, $uploadPath = null, $extension = null, $deleteFile = null)
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        parent::__construct($filename, $uploadPath, $extension, $deleteFile);
    }

    public function setParameters($targetProject, $targetClaimRevision, $exporterBuildspaceId, $exporterProjectId, $exporterProjectOriginInformation, $userId, $attachmentPaths)
    {
        $this->targetProject             = $targetProject;
        $this->exporterBuildspaceId      = $exporterBuildspaceId;
        $this->exporterOriginalProjectId = $exporterProjectId;
        $this->userId                    = $userId;
        $this->attachmentPaths           = $attachmentPaths;
        $this->targetClaimRevision       = $targetClaimRevision;

        $this->exporterProjectOriginInformation = $exporterProjectOriginInformation;

        if( $this->exporterProjectOriginInformation ) $this->targetIsOriginProject = true;

        if( $this->targetIsOriginProject )
        {
            $this->exporterOriginalProjectId = $this->exporterProjectOriginInformation['origin_id'];
        }
    }

    public function extractData()
    {
        parent::read();

        $xmlData = parent::getProcessedData();

        $this->exporterClaimVersion = (int)$xmlData->attributes()->claimVersion;

        $this->exportedStandardClaims    = $xmlData->{sfBuildspaceExportClaimsXML::TAG_STANDARD_CLAIMS}->children();
        $this->exportedPreliminaryClaims = $xmlData->{sfBuildspaceExportClaimsXML::TAG_PRELIMINARY_CLAIMS}->children();

        $this->exportedVariationOrders          = $xmlData->{sfBuildspaceExportClaimsXML::TAG_VARIATION_ORDERS}->children();
        $this->exportedVariationOrderItems      = $xmlData->{sfBuildspaceExportClaimsXML::TAG_VARIATION_ORDER_ITEMS}->children();
        $this->exportedVariationOrderClaimItems = $xmlData->{sfBuildspaceExportClaimsXML::TAG_VARIATION_ORDER_CLAIM_ITEMS}->children();

        $this->exportedMaterialsOnSite     = $xmlData->{sfBuildspaceExportClaimsXML::TAG_MATERIALS_ON_SITE}->children();
        $this->exportedMaterialOnSiteItems = $xmlData->{sfBuildspaceExportClaimsXML::TAG_MATERIAL_ON_SITE_ITEMS}->children();
        $this->exportedAttachments         = $xmlData->{sfBuildspaceExportClaimsXML::TAG_ATTACHMENTS}->children();

        return true;
    }

    protected function mapStandardClaimInfo()
    {
        $this->billItemsMap = $this->mapper($this->exportedStandardClaims, 'bill_item_id', 'bill_item_origin_id', BillItemTable::getInstance()->getTableName(), $this->targetIsOriginProject);

        $this->billColumnSettingsMap = $this->mapper($this->exportedStandardClaims, 'bill_column_setting_id', 'bill_column_setting_origin_id', BillColumnSettingTable::getInstance()->getTableName(), $this->targetIsOriginProject);

        $preliminaryClaimBillItems = $this->mapper($this->exportedPreliminaryClaims, 'bill_item_id', 'bill_item_origin_id', BillItemTable::getInstance()->getTableName(), $this->targetIsOriginProject);

        $this->billItemsMap = $this->billItemsMap + $preliminaryClaimBillItems;
    }

    protected function mapper($exportedRecordsXml, $exporterIdAttribute, $exporterOriginAttribute, $tableName, $targetIsOriginRecord = false)
    {
        $map = array();

        if( $exportedRecordsXml->count() < 1 ) return $map;

        foreach($exportedRecordsXml as $exportedRecord)
        {
            $originInformation = ProjectStructureTable::extractOriginId((string)$exportedRecord->{$exporterOriginAttribute});

            $map[ (int)$exportedRecord->{$exporterIdAttribute} ] = (int)$originInformation['origin_id'];
        }

        if( $targetIsOriginRecord ) return $map;

        $identifiers   = array();
        $questionMarks = array();

        foreach($map as $exporterId => $targetId)
        {
            $identifiers[ $exporterId ] = ProjectStructureTable::generateTenderOriginId($this->exporterBuildspaceId, $exporterId, $this->exporterOriginalProjectId);
            $questionMarks[]            = "?";
        }

        // Query and match back to importer BuildSpace records.
        $stmt = $this->pdo->prepare("SELECT t.tender_origin_id, t.id 
            FROM {$tableName} t
            WHERE t.tender_origin_id in (" . implode(',', $questionMarks) . ")");

        $stmt->execute(array_values($identifiers));

        $records = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach($identifiers as $exporterId => $targetId)
        {
            $map[ $exporterId ] = $records[ $targetId ];
        }

        return $map;
    }

    protected function validateClaimRevision()
    {
        $stmt = $this->pdo->prepare("SELECT r.id, r.version, r.claim_submission_locked
            FROM " . PostContractClaimRevisionTable::getInstance()->getTableName() . " r
            WHERE r.post_contract_id = :postContractId
            AND r.version = :claimVersion");

        $stmt->execute(array( 'postContractId' => $this->targetProject->PostContract->id, 'claimVersion' => $this->exporterClaimVersion ));

        if( ! $claimRevision = $stmt->fetch(PDO::FETCH_ASSOC) ) throw new Exception("Claim Version non-existent.");

        if( $claimRevision['id'] != $this->targetClaimRevision->id ) throw new Exception("Claim Version mismatch. Please choose the correct file.");

        if( $claimRevision['claim_submission_locked'] ) throw new Exception("Claim submission for this Claim Certificate is no longer allowed.");
    }

    public function process()
    {
        $this->extractData();

        $this->validateClaimRevision();

        $this->mapStandardClaimInfo();

        $this->flushAttachments();

        $this->insertClaimTypeReferences();
        $this->flushStandardClaims();
        $this->insertStandardClaims();

        $this->flushPreliminaryClaims();
        $this->insertPreliminaryClaims();

        $this->insertVariationOrders();
        $this->insertVariationOrderItems();
        $this->flushVariationOrderClaimItems();
        $this->insertVariationOrderClaimItems();

        $this->flushMaterialsOnSite();
        $this->insertMaterialsOnSite();
        $this->insertMaterialOnSiteItems();

        $this->insertAttachments();

        return true;
    }

    protected function flushStandardClaims()
    {
        $stmt = $this->pdo->prepare("DELETE FROM " . PostContractImportedStandardClaimTable::getInstance()->getTableName() . "
            WHERE revision_id = :revisionId;");

        $stmt->execute(array( 'revisionId' => $this->targetClaimRevision->id ));
    }

    protected function insertStandardClaims()
    {
        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(PostContractImportedStandardClaimTable::getInstance()->getTableName(), array(
            'revision_id', 'claim_type_ref_id', 'bill_item_id', 'current_percentage', 'current_amount', 'up_to_date_percentage', 'up_to_date_amount', 'up_to_date_qty',
        ));

        foreach($this->exportedStandardClaims as $claim)
        {
            $billColumnSettingId = $this->billColumnSettingsMap[ (int)$claim->bill_column_setting_id ];
            $claimTypeRefId      = $this->claimTypeReferences[ $billColumnSettingId ][ (int)$claim->counter ];
            $billItemId          = $this->billItemsMap[ (int)$claim->bill_item_id ];
            $currentPercentage   = (float)$claim->current_percentage;
            $currentAmount       = (float)$claim->current_amount;
            $upToDatePercentage  = (float)$claim->up_to_date_percentage;
            $upToDateAmount      = (float)$claim->up_to_date_amount;
            $upToDateQty         = (float)$claim->up_to_date_qty;

            $stmt->addRecord(array(
                $this->targetClaimRevision->id, $claimTypeRefId, $billItemId, $currentPercentage, $currentAmount, $upToDatePercentage, $upToDateAmount, $upToDateQty,
            ));
        }

        if( count($stmt->records) ) $stmt->save();
    }

    protected function insertClaimTypeReferences()
    {
        foreach($this->exportedStandardClaims as $claim)
        {
            $billColumnSettingOriginId = (int)$claim->bill_column_setting_id;
            $targetBillColumnSettingId = $this->billColumnSettingsMap[ $billColumnSettingOriginId ];
            if( ! array_key_exists($targetBillColumnSettingId, $this->claimTypeReferences) ) $this->claimTypeReferences[ $targetBillColumnSettingId ] = array();

            $this->claimTypeReferences[ $targetBillColumnSettingId ][ (int)$claim->counter ] = (int)$claim->counter;
        }

        foreach($this->claimTypeReferences as $targetBillColumnSettingId => $unitCounters)
        {
            $stmt = $this->pdo->prepare("SELECT t.id, t.counter
                FROM " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " t
                WHERE t.post_contract_id = " . $this->targetProject->PostContract->id . "
                AND t.bill_column_setting_id = " . $targetBillColumnSettingId . "
                AND t.counter IN (" . implode(',', $unitCounters) . ")
                ORDER BY t.counter ASC");

            $stmt->execute();

            $existingTypeItems = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $nonExistentReferences = array_diff($unitCounters, $existingTypeItems);

            if( ! empty( $nonExistentReferences ) )
            {
                $stmt = new sfImportStatementGenerator();

                $stmt->createInsert(PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName(), array(
                    'post_contract_id', 'bill_column_setting_id', 'counter',
                ));

                foreach($nonExistentReferences as $unit)
                {
                    $stmt->addRecord(array(
                        intval($this->targetProject->PostContract->id), $targetBillColumnSettingId, intval($unit),
                    ));
                }

                if( count($stmt->records) ) $stmt->save();
            }
        }

        $stmt = $this->pdo->prepare("SELECT t.bill_column_setting_id, t.id, t.counter
                FROM " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " t
                WHERE t.post_contract_id = " . $this->targetProject->PostContract->id . "
                ORDER BY t.counter ASC");

        $stmt->execute();

        $this->claimTypeReferences = array();

        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $record)
        {
            $this->claimTypeReferences[ $record['bill_column_setting_id'] ][ $record['counter'] ] = $record['id'];
        }
    }

    protected function flushPreliminaryClaims()
    {
        $stmt = $this->pdo->prepare("DELETE FROM " . PostContractImportedPreliminaryClaimTable::getInstance()->getTableName() . "
            WHERE revision_id = :revisionId;");

        $stmt->execute(array( 'revisionId' => $this->targetClaimRevision->id ));
    }

    protected function insertPreliminaryClaims()
    {
        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(PostContractImportedPreliminaryClaimTable::getInstance()->getTableName(), array(
            'revision_id', 'bill_item_id', 'up_to_date_amount',
        ));

        foreach($this->exportedPreliminaryClaims as $claim)
        {
            $billItemId     = $this->billItemsMap[ (int)$claim->bill_item_id ];
            $upToDateAmount = (float)$claim->up_to_date_amount;

            $stmt->addRecord(array(
                $this->targetClaimRevision->id, $billItemId, $upToDateAmount,
            ));
        }

        if( count($stmt->records) ) $stmt->save();
    }

    protected function mapVariationOrders()
    {
        $stmt = $this->pdo->prepare("SELECT vo.id, vo.tender_origin_id
            FROM " . ImportedVariationOrderTable::getInstance()->getTableName() . " vo
            WHERE vo.project_structure_id = {$this->targetProject->id}");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach($records as $targetId => $originId)
        {
            $originInfo                                           = ProjectStructureTable::extractOriginId((string)$originId);
            $this->variationOrdersMap[ $originInfo['origin_id'] ] = $targetId;
        }
    }

    protected function mapVariationOrderItems()
    {
        $stmt = $this->pdo->prepare("SELECT voi.id, voi.tender_origin_id
            FROM " . ImportedVariationOrderItemTable::getInstance()->getTableName() . " voi
            JOIN " . ImportedVariationOrderTable::getInstance()->getTableName() . " vo on vo.id = voi.imported_variation_order_id
            WHERE vo.project_structure_id = {$this->targetProject->id}");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach($records as $targetId => $originId)
        {
            $originInfo                                               = ProjectStructureTable::extractOriginId((string)$originId);
            $this->variationOrderItemsMap[ $originInfo['origin_id'] ] = $targetId;
        }
    }

    protected function insertVariationOrders()
    {
        $stmt = $this->pdo->prepare("SELECT vo.id, vo.tender_origin_id
            FROM " . ImportedVariationOrderTable::getInstance()->getTableName() . " vo
            WHERE vo.project_structure_id = {$this->targetProject->id}");

        $stmt->execute();

        $existingRecords = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach($existingRecords as $targetId => $originId)
        {
            $originInfo                   = ProjectStructureTable::extractOriginId((string)$originId);
            $existingRecords[ $targetId ] = $originInfo['origin_id'];
        }

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(ImportedVariationOrderTable::getInstance()->getTableName(), array(
            'revision_id', 'project_structure_id', 'tender_origin_id', 'description', 'priority', 'created_at', 'updated_at',
        ));

        foreach($this->exportedVariationOrders as $variationOrder)
        {
            $exporterId = (int)$variationOrder->id;

            if( in_array($exporterId, $existingRecords) ) continue;

            $targetOriginId = ProjectStructureTable::generateTenderOriginId($this->exporterBuildspaceId, $exporterId, $this->exporterOriginalProjectId);

            $stmt->addRecord(array(
                $this->targetClaimRevision->id, $this->targetProject->id, $targetOriginId, (string)$variationOrder->description, (int)$variationOrder->priority, 'NOW()', 'NOW()',
            ));
        }

        if( count($stmt->records) ) $stmt->save();

        $this->mapVariationOrders();
    }

    protected function insertVariationOrderItems()
    {
        $stmt = $this->pdo->prepare("SELECT voi.id, voi.tender_origin_id
            FROM " . ImportedVariationOrderItemTable::getInstance()->getTableName() . " voi
            JOIN " . ImportedVariationOrderTable::getInstance()->getTableName() . " vo on vo.id = voi.imported_variation_order_id
            WHERE vo.project_structure_id = {$this->targetProject->id}");

        $stmt->execute();

        $existingRecords = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach($existingRecords as $targetId => $originId)
        {
            $originInfo = ProjectStructureTable::extractOriginId((string)$originId);

            $existingRecords[ $targetId ] = $originInfo['origin_id'];
        }

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(ImportedVariationOrderItemTable::getInstance()->getTableName(), array(
            'imported_variation_order_id', 'tender_origin_id', 'description', 'priority', 'type', 'total_amount', 'total_unit', 'uom_symbol', 'rate', 'quantity', 'created_at', 'updated_at', 'root_id', 'lft', 'rgt', 'level',
        ));

        $newRecordsExporterIds = array();

        foreach($this->exportedVariationOrderItems as $item)
        {
            $exporterId = (int)$item->id;

            if( in_array($exporterId, $existingRecords) ) continue;

            $newRecordsExporterIds[] = $exporterId;

            $targetOriginId = ProjectStructureTable::generateTenderOriginId($this->exporterBuildspaceId, $exporterId, $this->exporterOriginalProjectId);

            $stmt->addRecord(array(
                $this->variationOrdersMap[ (int)$item->variation_order_id ], $targetOriginId, (string)$item->description, (int)$item->priority, (int)$item->type, (float)$item->total_amount, (float)$item->total_unit, (string)$item->uom_symbol, (float)$item->rate, (float)$item->quantity, 'NOW()', 'NOW()', 0, (int)$item->lft, (int)$item->rgt, (int)$item->level,
            ));
        }

        if( count($stmt->records) ) $stmt->save();

        $this->mapVariationOrderItems();

        $this->reRootRecords($this->exportedVariationOrderItems, $this->variationOrderItemsMap, ImportedVariationOrderItemTable::getInstance()->getTableName(), $newRecordsExporterIds);
    }

    protected function flushVariationOrderClaimItems()
    {
        $stmt = $this->pdo->prepare("DELETE FROM " . ImportedVariationOrderClaimItemTable::getInstance()->getTableName() . "
            WHERE revision_id = :revisionId;");

        $stmt->execute(array( 'revisionId' => $this->targetClaimRevision->id ));
    }

    protected function insertVariationOrderClaimItems()
    {
        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(ImportedVariationOrderClaimItemTable::getInstance()->getTableName(), array(
            'revision_id', 'imported_variation_order_item_id', 'up_to_date_percentage', 'up_to_date_amount', 'up_to_date_quantity',
        ));

        foreach($this->exportedVariationOrderClaimItems as $claim)
        {
            $variationOrderItemId = $this->variationOrderItemsMap[ (int)$claim->variation_order_item_id ];
            $upToDatePercentage   = (float)$claim->up_to_date_percentage;
            $upToDateAmount       = (float)$claim->up_to_date_amount;
            $upToDateQuantity     = (float)$claim->up_to_date_quantity;

            $stmt->addRecord(array(
                $this->targetClaimRevision->id, $variationOrderItemId, $upToDatePercentage, $upToDateAmount, $upToDateQuantity,
            ));
        }

        if( count($stmt->records) ) $stmt->save();
    }

    protected function flushMaterialsOnSite()
    {
        $this->flushMaterialOnSiteItems();

        $stmt = $this->pdo->prepare("DELETE FROM " . ImportedMaterialOnSiteTable::getInstance()->getTableName() . "
            WHERE revision_id = :revisionId;");

        $stmt->execute(array( 'revisionId' => $this->targetClaimRevision->id ));
    }

    protected function flushMaterialOnSiteItems()
    {
        $stmt = $this->pdo->prepare("SELECT mos.id
            FROM " . ImportedMaterialOnSiteTable::getInstance()->getTableName() . " mos
            WHERE mos.revision_id = " . $this->targetClaimRevision->id);

        $stmt->execute();

        $existingMaterialOnSiteIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if( ! empty( $existingMaterialOnSiteIds ) )
        {
            $stmt = $this->pdo->prepare("DELETE FROM " . ImportedMaterialOnSiteItemTable::getInstance()->getTableName() . "
            WHERE imported_material_on_site_id in (" . implode(',', $existingMaterialOnSiteIds) . ");");

            $stmt->execute();
        }
    }

    protected function insertMaterialsOnSite()
    {
        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(ImportedMaterialOnSiteTable::getInstance()->getTableName(), array(
            'revision_id', 'project_structure_id', 'tender_origin_id', 'description', 'sequence', 'created_at', 'updated_at',
        ));

        foreach($this->exportedMaterialsOnSite as $record)
        {
            $exporterId = (int)$record->id;

            $targetOriginId = ProjectStructureTable::generateTenderOriginId($this->exporterBuildspaceId, $exporterId, $this->exporterOriginalProjectId);

            $stmt->addRecord(array(
                $this->targetClaimRevision->id, $this->targetProject->id, $targetOriginId, (string)$record->description, (int)$record->sequence, 'NOW()', 'NOW()',
            ));
        }

        if( count($stmt->records) ) $stmt->save();

        $this->materialsOnSiteMap = $this->mapper($this->exportedMaterialsOnSite, 'id', 'tender_origin_id', ImportedMaterialOnSiteTable::getInstance()->getTableName());
    }

    protected function insertMaterialOnSiteItems()
    {
        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(ImportedMaterialOnSiteItemTable::getInstance()->getTableName(), array(
            'imported_material_on_site_id', 'tender_origin_id', 'description', 'sequence', 'uom_symbol', 'type', 'quantity', 'rate', 'final_amount', 'reduction_percentage', 'reduction_amount', 'created_at', 'updated_at', 'root_id', 'lft', 'rgt', 'level',
        ));

        foreach($this->exportedMaterialOnSiteItems as $record)
        {
            $exporterId = (int)$record->id;

            $targetOriginId = ProjectStructureTable::generateTenderOriginId($this->exporterBuildspaceId, $exporterId, $this->exporterOriginalProjectId);

            $stmt->addRecord(array(
                $this->materialsOnSiteMap[ (int)$record->material_on_site_id ], $targetOriginId, (string)$record->description, (int)$record->sequence, (string)$record->uom_symbol, (int)$record->type, (float)$record->quantity, (float)$record->rate, (float)$record->final_amount, (float)$record->reduction_percentage, (float)$record->reduction_amount, 'NOW()', 'NOW()', 0, (int)$record->lft, (int)$record->rgt, (int)$record->level,
            ));
        }

        if( count($stmt->records) ) $stmt->save();

        $this->materialOnSiteItemsMap = $this->mapper($this->exportedMaterialOnSiteItems, 'id', 'tender_origin_id', ImportedMaterialOnSiteItemTable::getInstance()->getTableName());

        $this->reRootRecords($this->exportedMaterialOnSiteItems, $this->materialOnSiteItemsMap, ImportedMaterialOnSiteItemTable::getInstance()->getTableName());
    }

    protected function reRootRecords($exportedRecordsXml, $map, $tableName, $excludedIds = array())
    {
        $targetIds = array();

        foreach($exportedRecordsXml as $item)
        {
            if( ! in_array((int)$item->id, $excludedIds) ) continue;

            $targetId     = $map[ (int)$item->id ];
            $targetRootId = $map[ (int)$item->root_id ];

            if( ! array_key_exists($targetRootId, $targetIds) ) $targetIds[ $targetRootId ] = array();
            $targetIds[ $targetRootId ][] = $targetId;
        }

        foreach($targetIds as $targetRootId => $targetIdsUnderRoot)
        {
            $stmt = $this->pdo->prepare("UPDATE {$tableName} SET root_id = {$targetRootId}
                WHERE id in (" . implode(',', $targetIdsUnderRoot) . ");");

            $stmt->execute();
        }
    }

    protected function flushAttachments()
    {
        $stmt = $this->pdo->prepare("SELECT id
            FROM " . ImportedVariationOrderTable::getInstance()->getTableName() . "
            WHERE project_structure_id = " . $this->targetProject->id);

        $stmt->execute();

        $itemIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach($itemIds as $itemId)
        {
            AttachmentsTable::deleteItemAttachments($itemId, ImportedVariationOrderTable::getInstance()->getClassnameToReturn());
        }

        $stmt = $this->pdo->prepare("SELECT i.id
            FROM " . ImportedVariationOrderItemTable::getInstance()->getTableName() . " i
            jOIN " . ImportedVariationOrderTable::getInstance()->getTableName() . " vo on vo.id = i.imported_variation_order_id
            WHERE vo.project_structure_id = " . $this->targetProject->id);

        $stmt->execute();

        $itemIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach($itemIds as $itemId)
        {
            AttachmentsTable::deleteItemAttachments($itemId, ImportedVariationOrderItemTable::getInstance()->getClassnameToReturn());
        }

        $stmt = $this->pdo->prepare("SELECT id
            FROM " . ImportedMaterialOnSiteTable::getInstance()->getTableName() . "
            WHERE revision_id = " . $this->targetClaimRevision->id);

        $stmt->execute();

        $itemIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach($itemIds as $itemId)
        {
            AttachmentsTable::deleteItemAttachments($itemId, ImportedMaterialOnSiteTable::getInstance()->getClassnameToReturn());
        }

        $stmt = $this->pdo->prepare("SELECT mosi.id
            FROM " . ImportedMaterialOnSiteItemTable::getInstance()->getTableName() . " mosi
            JOIN " . ImportedMaterialOnSiteTable::getInstance()->getTableName() . " mos ON mos.id = mosi.imported_material_on_site_id
            WHERE mos.revision_id = " . $this->targetClaimRevision->id);

        $stmt->execute();

        $itemIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach($itemIds as $itemId)
        {
            AttachmentsTable::deleteItemAttachments($itemId, ImportedMaterialOnSiteItemTable::getInstance()->getClassnameToReturn());
        }
    }

    protected function insertAttachments()
    {
        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(AttachmentsTable::getInstance()->getTableName(), array(
            'object_id', 'object_class', 'filepath', 'filename', 'extension', 'created_at', 'updated_at', 'created_by', 'updated_by',
        ));

        foreach($this->exportedAttachments as $attachmentInfo)
        {
            $itemClass = (string)$attachmentInfo->itemClass;
            $filename  = (string)$attachmentInfo->filename;
            $extension = (string)$attachmentInfo->extension;

            $itemMap = $this->getMap($itemClass);

            $targetItemId = $itemMap[ (int)$attachmentInfo->itemId ];

            $absoluteFilePath = AttachmentsTable::createAbsolutePath($itemClass, $targetItemId, $filename, $extension);
            $uploadFilePath   = AttachmentsTable::getUploadPath($absoluteFilePath);

            $stmt->addRecord(array(
                $targetItemId, $itemClass, $uploadFilePath, $filename, $extension, 'NOW()', 'NOW()', $this->userId, $this->userId,
            ));

            rename($this->attachmentPaths[ (string)$attachmentInfo->fileIdentifier ], $absoluteFilePath);
        }

        if( count($stmt->records) ) $stmt->save();
    }

    protected function getMap($exporterItemClass)
    {
        switch($exporterItemClass)
        {
            case ImportedVariationOrderTable::getInstance()->getClassnameToReturn():
                return $this->variationOrdersMap;
            case ImportedVariationOrderItemTable::getInstance()->getClassnameToReturn():
                return $this->variationOrderItemsMap;
            case ImportedMaterialOnSiteTable::getInstance()->getClassnameToReturn():
                return $this->materialsOnSiteMap;
            case ImportedMaterialOnSiteItemTable::getInstance()->getClassnameToReturn():
                return $this->materialOnSiteItemsMap;
            default:
                throw new Exception('Class not found');
        }
    }
}