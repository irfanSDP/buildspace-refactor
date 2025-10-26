<?php
class sfBuildspaceImportProjectAddendumXML extends sfBuildspaceXMLParser
{
    public $structure;
    public $information;
    public $breakdown;
    public $rootProject;
    public $projectUniqueId;
    public $buildspaceId;
    public $currentRevision;
    public $revision;
    public $projectId;
    public $originalProjectId;
    public $breakdownIds = array();
    public $billIds = array();
    public $versionIds = array();

    public $userId;

    function __construct( $userId, $filename = null, $uploadPath = null, $extension = null, $deleteFile = null ) 
    {
        $this->userId = $userId; 

        parent::__construct( $filename, $uploadPath, $extension, $deleteFile );
    }

    public function process()
    {
        parent::read();

        $xmlData = parent::getProcessedData();

        $this->validateImportedFile((int) $xmlData->attributes()->exportType);

        $this->rootProject = $xmlData->{sfBuildspaceExportProjectXML::TAG_ROOT}->children();

        $this->buildspaceId = $xmlData->attributes()->buildspaceId; 

        $this->getOriginalProjectInformation();

        $this->validateProjectInfo((string) $xmlData->attributes()->uniqueId, $xmlData->{sfBuildspaceExportProjectXML::TAG_REVISIONS}->children());

        $this->revision = $xmlData->{sfBuildspaceExportProjectXML::TAG_REVISIONS}->children(); 

        $this->processRevision();

        $this->processTenderAlternative();
    }

    public function getProjectInformation()
    {
        if(!$this->xml)
            parent::read();

        $this->information = $this->xml->{sfBuildspaceExportProjectXML::TAG_INFORMATION}->children();

        $this->validateImportedFile((int) $this->xml->attributes()->exportType);

        if($this->information->{sfBuildspaceExportProjectXML::TAG_REGION}->count() > 0)
        {
            $region = $this->information->{sfBuildspaceExportProjectXML::TAG_REGION}->children(); 

            $regionName = (string) $region->country;
        }

        if($this->information->{sfBuildspaceExportProjectXML::TAG_SUBREGION}->count() > 0)
        {
            $subregion = $this->information->{sfBuildspaceExportProjectXML::TAG_SUBREGION}->children(); 

            $subRegionName = (string) $subregion->name;
        } 

        if($this->information->{sfBuildspaceExportProjectXML::TAG_WORKCAT}->count() > 0)
        {
            $workCategory = $this->information->{sfBuildspaceExportProjectXML::TAG_WORKCAT}->children(); 

            $workCategoryName = (string) $workCategory->name;
        } 

        if($this->information->{sfBuildspaceExportProjectXML::TAG_CURRENCY}->count() > 0)
        {
            $currency = $this->information->{sfBuildspaceExportProjectXML::TAG_CURRENCY}->children(); 

            $currencyName = (string) $currency->currency_name;   
        } 

        return array(
            'projectTitle' => (string) $this->information->title,
            'description' => (string) $this->information->description,
            'client' => (string) $this->information->client,
            'unique_id' => (string) $this->projectUniqueId,
            'country' => $regionName,
            'state' => $subRegionName,
            'work_category'=> $workCategoryName
        );
    }

    public function getProjectBreakdown()
    {
        if(!$this->xml)
            parent::read();

        $this->breakdown = $this->xml->{sfBuildspaceExportProjectXML::TAG_BREAKDOWN}->children(); 

        $records = array();

        $count = 0;

        foreach($this->breakdown as $structure)
        {
            $count = (int) $structure->type == ProjectStructure::TYPE_BILL ? $count + 1 : $count;

            array_push($records, array(
                'count' => $count,
                'id' => (int) $structure->id,
                'level' => (int) $structure->level,
                'title' => (string) $structure->title,
                'type' => (int) $structure->type
            ));
        }

        return array(
            'identifier' => 'id',
            'items' => $records
        );
    }

    public function processRevision()
    {
        $stmt = new sfImportStatementGenerator();

        //Update Root Id
        $stmt->updateRecord(ProjectRevisionTable::getInstance()->getTableName(), 
            array(
                'project_structure_id' => $this->projectId
            ), 
            array(
                'current_selected_revision' => 0,
                'locked_status' => true
            )
        );

        foreach($this->revision as $version)
        {
            if($version->id)
            {
                $originalId = (int) $version->id;

                unset($version->id);
            }

            $version->created_at = 'NOW()';
            $version->updated_at = 'NOW()';
            $version->created_by = $this->userId;
            $version->locked_status = true;
            $version->current_selected_revision = true;
            $version->updated_by = $this->userId;
            $version->project_structure_id = $this->projectId;
            $version->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $originalId, $this->originalProjectId);

            $dataAndStructure = parent::generateArrayOfSingleData( $version, true );

            $stmt->createInsert(ProjectRevisionTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $this->versionIds[$originalId] = $stmt->returningIds[0];

            unset($version);
        }

        unset($this->revision);
    }

    public function processTenderAlternative()
    {
        if(empty($this->xml->{sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVES}))
        {
            return false;
        }
        
        $project = ProjectStructureTable::getInstance()->find($this->projectId);

        if(!$project or $project->type != ProjectStructure::TYPE_ROOT)
        {
            throw new Exception('Invalid Project Id: '.$this->projectId);
        }

        $importedTenderAlternatives = $project->getTenderAlternatives();

        $savedTenderAlternativeIds = [];
        $importedTenderAlternativeTenderOriginIds = [];
        foreach($importedTenderAlternatives as $importedTenderAlternative)
        {
            $importedTenderAlternativeTenderOriginIds[] = $importedTenderAlternative->tender_origin_id;
        }

        $tenderAlternatives = $this->xml->{sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVES}->children();

        $stmt = new sfImportStatementGenerator();

        foreach($tenderAlternatives as $tenderAlternative)
        {
            $tenderOriginId = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, (int) $tenderAlternative->id, $this->originalProjectId);

            if(!in_array($tenderOriginId, $importedTenderAlternativeTenderOriginIds))
            {
                if($tenderAlternative->id)
                {
                    $originalId = (int) $tenderAlternative->id;

                    unset($tenderAlternative->id);
                }

                if($tenderAlternative->tender_amount)
                {
                    /* we unset this values because there is not column related to this values in buildspace tenderAlternative table.
                    * This values is used for Eproject when importing TenderAlternative in Eproject.
                    */
                    unset($tenderAlternative->tender_amount, $tenderAlternative->tender_amount_except_prime_cost_provisional, $tenderAlternative->tender_som_amount);
                }

                $tenderAlternative->project_structure_id = $project->id;
                $tenderAlternative->project_revision_id = $this->versionIds[(int)$tenderAlternative->project_revision_id];
                $tenderAlternative->created_at = 'NOW()';
                $tenderAlternative->updated_at = 'NOW()';
                $tenderAlternative->created_by = $this->userId;
                $tenderAlternative->updated_by = $this->userId;
                $tenderAlternative->tender_origin_id = $tenderOriginId;

                $dataAndStructure = parent::generateArrayOfSingleData( $tenderAlternative, true );

                $stmt->createInsert(TenderAlternativeTable::getInstance()->getTableName(), $dataAndStructure['structure']);

                $stmt->addRecord($dataAndStructure['data']);

                $stmt->save();

                $savedTenderAlternativeIds[$originalId] = $stmt->returningIds[0];
            }
            elseif(!empty((string)$tenderAlternative->project_revision_deleted_at))
            {
                $stmt->updateRecord(TenderAlternativeTable::getInstance()->getTableName(), 
                    [
                        'tender_origin_id' => $tenderOriginId
                    ], 
                    [
                        'deleted_at_project_revision_id' => $this->versionIds[(int) $tenderAlternative->deleted_at_project_revision_id],
                        'project_revision_deleted_at' => (string) $tenderAlternative->project_revision_deleted_at
                    ]
                );
            }
        }

        unset($importedTenderAlternatives);

        $tenderAlternativeBills = $this->xml->{sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVES_BILLS}->children();

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $stmt = $pdo->prepare("SELECT DISTINCT b.id, b.tender_origin_id
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " p
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON b.root_id = p.id
            WHERE p.id = " . $project->id . "
            AND b.type NOT IN (".ProjectStructure::TYPE_ROOT.", ".ProjectStructure::TYPE_LEVEL.")
            AND p.deleted_at IS NULL AND b.deleted_at IS NULL");
        
        $stmt->execute();
        $billTenderOriginIds = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $savedBillIds = [];
        foreach($billTenderOriginIds as $billId => $billTenderOriginId)
        {
            $extractedTenderOriginIds = ProjectStructureTable::extractOriginId($billTenderOriginId);
            $savedBillIds[$extractedTenderOriginIds['origin_id']] = $billId;
        }

        $stmt = new sfImportStatementGenerator();

        foreach($tenderAlternativeBills as $xref)
        {
            if(array_key_exists((int)$xref->tender_alternative_id, $savedTenderAlternativeIds) && array_key_exists((int)$xref->project_structure_id, $savedBillIds))
            {
                $xref->tender_alternative_id = $savedTenderAlternativeIds[(int)$xref->tender_alternative_id];
                $xref->project_structure_id  = $savedBillIds[(int)$xref->project_structure_id];
                $xref->created_at            = 'NOW()';
                $xref->updated_at            = 'NOW()';
                $xref->created_by            = (int)$this->userId;
                $xref->updated_by            = (int)$this->userId;

                $dataAndStructure = parent::generateArrayOfSingleData($xref, true);

                $stmt->createInsert(TenderAlternativeBillTable::getInstance()->getTableName(), $dataAndStructure['structure']);
    
                $stmt->addRecord($dataAndStructure['data']);
    
                $stmt->save();
            }
        }

        unset($tenderAlternatives, $tenderAlternativeBills);
    }

    public function getOriginalProjectInformation()
    {
        if(!$this->rootProject)
            return false;

        $originalProjectInfo = DoctrineQuery::create()->select('p.id, p.title, p.tender_origin_id, m.*, r.country, sr.name, p.created_at')
            ->from('ProjectStructure p')
            ->leftJoin('p.MainInformation m')
            ->where('p.tender_origin_id = ?', ProjectStructureTable::generateTenderOriginId($this->buildspaceId, (int) $this->rootProject->id, (int) $this->rootProject->id))
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        //Get Revision Info
        $revisionInfo = DoctrineQuery::create()
            ->select('r.id, r.version')
            ->from('ProjectRevision r')
            ->where('r.project_structure_id = ?', $originalProjectInfo['id'])
            ->orderBy('r.id DESC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        $this->currentRevision = $revisionInfo; 

        $this->projectUniqueId = $originalProjectInfo['MainInformation']['unique_id'];

        $this->projectId = $originalProjectInfo['id'];

        $arrayOfIds = ProjectStructureTable::extractOriginId($originalProjectInfo['tender_origin_id']);

        $this->originalProjectId = $arrayOfIds['origin_id'];

        return true;
    }

    public function validate()
    {
        parent::read();

        $xmlData = parent::getProcessedData();

        $this->validateImportedFile((int) $xmlData->attributes()->exportType);

        $this->rootProject = $xmlData->{sfBuildspaceExportProjectXML::TAG_ROOT}->children();

        $this->buildspaceId = $xmlData->attributes()->buildspaceId; 

        $this->getOriginalProjectInformation();

        $this->validateProjectInfo((string) $xmlData->attributes()->uniqueId, $xmlData->{sfBuildspaceExportProjectXML::TAG_REVISIONS}->children());
    }

    public function validateImportedFile($exportType)
    {
        if($exportType != ExportedFile::EXPORT_TYPE_ADDENDUM)
            throw new Exception('Please Choose a correct Addendum File');
    }

    protected function validateProjectInfo($projectUniqueId, $revisions)
    {
    	if($projectUniqueId != $this->projectUniqueId)
        {
            throw new Exception('Please Choose a correct project file');   
        }
            

        foreach($revisions as $revision)
        {
            if(((int) $revision->version - $this->currentRevision['version']) != 1)
                throw new Exception('Please Choose a correct addendum version');
        }
    }

}
