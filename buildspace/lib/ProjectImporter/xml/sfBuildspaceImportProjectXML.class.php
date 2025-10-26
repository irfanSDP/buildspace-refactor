<?php

class sfBuildspaceImportProjectXML extends sfBuildspaceXMLParser
{
    public $structure;
    public $information;
    public $breakdown;
    public $rootProject;
    public $projectUniqueId;
    public $buildspaceId;
    public $exportType;
    public $revision;
    public $projectId;
    public $originalProjectId;
    public $breakdownIds = [];
    public $billIds = [];
    public $versionIds = [];

    protected $tenderAlternativeIds = [];
    protected $tenderAlternatives = [];
    protected $tenderAlternativesBills = [];

    public $userId;

    function __construct($userId, $filename = null, $uploadPath = null, $extension = null, $deleteFile = null)
    {
        $this->userId = $userId;

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        parent::__construct($filename, $uploadPath, $extension, $deleteFile);

        $this->extractData();
    }

    protected function extractData()
    {
        parent::read();

        $xmlData = parent::getProcessedData();

        $this->rootProject = ( $xmlData->{sfBuildspaceExportProjectXML::TAG_ROOT}->count() > 0 ) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_ROOT}->children() : false;

        $this->validateImportedFile((int) $xmlData->attributes()->exportType);

        $this->exportType = $xmlData->attributes()->exportType;

        $this->checkProjectExist((string) $xmlData->attributes()->buildspaceId, (int) $this->rootProject->id);

        $this->buildspaceId = ( (string) $xmlData->attributes()->buildspaceId ) ? (string) $xmlData->attributes()->buildspaceId : false;

        $this->projectUniqueId = $xmlData->attributes()->uniqueId;

        $this->information = ( $xmlData->{sfBuildspaceExportProjectXML::TAG_INFORMATION}->count() > 0 ) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_INFORMATION}->children() : false;

        $this->revision = ( $xmlData->{sfBuildspaceExportProjectXML::TAG_REVISIONS}->count() > 0 ) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_REVISIONS}->children() : [];

        $this->breakdown = ( $xmlData->{sfBuildspaceExportProjectXML::TAG_BREAKDOWN}->count() > 0 ) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_BREAKDOWN}->children() : [];

        $this->tenderAlternatives = ( $xmlData->{sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVES}->count() > 0 ) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVES}->children() : [];

        $this->tenderAlternativesBills = ( $xmlData->{sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVES_BILLS}->count() > 0 ) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVES_BILLS}->children() : [];
    }

    public function process()
    {
        $this->insertRootProject();

        $this->insertInformation();

        $this->processRevision();

        $this->processBreakdown();
        
        $this->processTenderAlternatives();

        $this->endReader();
    }

    public function getProjectInformation()
    {
        if (!$this->xml)
        {
            parent::read();
        }

        $this->information = ( $this->xml->{sfBuildspaceExportProjectXML::TAG_INFORMATION}->count() > 0 ) ? $this->xml->{sfBuildspaceExportProjectXML::TAG_INFORMATION}->children() : false;

        $projectTitle     = "";
        $description      = "";
        $client           = "";
        $regionName       = false;
        $subRegionName    = false;
        $workCategoryName = false;
        $currencyName     = false;

        if($this->information)
        {
            $projectTitle = (string) $this->information->title;
            $description  = (string) $this->information->description;
            $client       = (string) $this->information->client;

            if ($this->information->{sfBuildspaceExportProjectXML::TAG_REGION}->count() > 0)
            {
                $region = $this->information->{sfBuildspaceExportProjectXML::TAG_REGION}->children();

                $regionName = (string) $region->country;
            }

            if ($this->information->{sfBuildspaceExportProjectXML::TAG_SUBREGION}->count() > 0)
            {
                $subregion = $this->information->{sfBuildspaceExportProjectXML::TAG_SUBREGION}->children();

                $subRegionName = (string) $subregion->name;
            }

            if ($this->information->{sfBuildspaceExportProjectXML::TAG_WORKCAT}->count() > 0)
            {
                $workCategory = $this->information->{sfBuildspaceExportProjectXML::TAG_WORKCAT}->children();

                $workCategoryName = (string) $workCategory->name;
            }

            if ($this->information->{sfBuildspaceExportProjectXML::TAG_CURRENCY}->count() > 0)
            {
                $currency = $this->information->{sfBuildspaceExportProjectXML::TAG_CURRENCY}->children();

                $currencyName = (string) $currency->currency_name;
            }
        }


        return array(
            'projectTitle'  => $projectTitle,
            'description'   => $description,
            'client'        => $client,
            'unique_id'     => (string) $this->projectUniqueId,
            'country'       => $regionName,
            'state'         => $subRegionName,
            'work_category' => $workCategoryName
        );
    }

    public function getProjectBreakdown()
    {
        if (!$this->xml)
        {
            parent::read();
        }

        $this->breakdown = ( $this->xml->{sfBuildspaceExportProjectXML::TAG_BREAKDOWN}->count() > 0 ) ? $this->xml->{sfBuildspaceExportProjectXML::TAG_BREAKDOWN}->children() : array();

        $records = array();

        $count = 0;

        $validBillTypes = array(
            ProjectStructure::TYPE_BILL,
            ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL,
        );

        foreach ($this->breakdown as $structure)
        {
            $count = in_array((int) $structure->type, $validBillTypes) ? $count + 1 : $count;

            array_push($records, array(
                'count' => $count,
                'id'    => (int) $structure->id,
                'level' => (int) $structure->level,
                'title' => (string) $structure->title,
                'type'  => (int) $structure->type
            ));
        }

        array_push($records, array(
            'count' => $count+1,
            'id'    => -1,
            'level' => 0,
            'title' => "",
            'type'  => -1
        ));

        return array(
            'identifier' => 'id',
            'items'      => $records
        );
    }

    protected function insertRootProject()
    {
        if (!$this->rootProject)
        {
            return false;
        }

        $originalId = (int) $this->rootProject->id;

        $projectSummaryFooter = ( $this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_FOOTER}->count() > 0 ) ? $this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_FOOTER}->children() : null;

        $projectSummaryGeneralSetting = ( $this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_GENERAL_SETTING}->count() > 0 ) ? $this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_GENERAL_SETTING}->children() : null;

        unset( $this->rootProject->id );

        $this->rootProject->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $originalId, $originalId);
        $this->rootProject->created_at       = 'NOW()';
        $this->rootProject->updated_at       = 'NOW()';
        $this->rootProject->created_by       = $this->userId;
        $this->rootProject->updated_by       = $this->userId;
        $this->rootProject->priority         = 0;

        $this->updateProjectPriority((int) $this->rootProject->priority);

        $dataAndStructure = parent::generateArrayOfSingleData($this->rootProject, true);

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(ProjectStructureTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();
        
        $this->projectId = $stmt->returningIds[0];

        $this->originalProjectId = $originalId;

        //Update Root Id
        $stmt->updateRecord(ProjectStructureTable::getInstance()->getTableName(),
            array(
                'id' => $this->projectId
            ),
            array(
                'root_id' => $this->projectId
            )
        );

        if ($projectSummaryFooter instanceof SimpleXMLElement)
        {
            $this->processProjectSummaryFooter($projectSummaryFooter, $stmt);
            unset( $projectSummaryFooter );
        }

        if ($projectSummaryGeneralSetting instanceof SimpleXMLElement)
        {
            $this->processProjectSummaryGeneralSetting($projectSummaryGeneralSetting, $stmt);
            unset( $projectSummaryGeneralSetting );
        }

        unset( $this->rootProject );

        return true;
    }

    protected function processProjectSummaryFooter(SimpleXMLElement $projectSummaryFooter, sfImportStatementGenerator $stmt)
    {
        unset( $projectSummaryFooter->id );

        $projectSummaryFooter->project_structure_id = (int) $this->projectId;

        $projectSummaryFooter->created_at = 'NOW()';
        $projectSummaryFooter->updated_at = 'NOW()';
        $projectSummaryFooter->created_by = $this->userId;
        $projectSummaryFooter->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData($projectSummaryFooter, true);

        $stmt->createInsert(ProjectSummaryFooterTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();
    }

    protected function processProjectSummaryGeneralSetting(SimpleXMLElement $projectSummaryGeneralSetting, sfImportStatementGenerator $stmt)
    {
        unset( $projectSummaryGeneralSetting->id );

        $projectSummaryGeneralSetting->project_structure_id = (int) $this->projectId;

        $projectSummaryGeneralSetting->created_at = 'NOW()';
        $projectSummaryGeneralSetting->updated_at = 'NOW()';
        $projectSummaryGeneralSetting->created_by = $this->userId;
        $projectSummaryGeneralSetting->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData($projectSummaryGeneralSetting, true);

        $stmt->createInsert(ProjectSummaryGeneralSettingTable::getInstance()->getTableName(),
            $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();
    }

    protected function updateProjectPriority($priority)
    {
        DoctrineQuery::create()
            ->update('ProjectStructure')
            ->set('priority', 'priority + 1')
            ->where('priority >= ?', $priority)
            ->execute();
    }

    protected function insertInformation()
    {
        if (!$this->information)
        {
            return false;
        }

        if ($this->information->id)
        {
            unset( $this->information->id );
        }

        $regionId       = null;
        $subRegionId    = null;
        $workCategoryId = null;
        $currencyId     = null;

        if ($this->information->{sfBuildspaceExportProjectXML::TAG_REGION}->count() > 0)
        {
            $regionObject = $this->information->{sfBuildspaceExportProjectXML::TAG_REGION}->children();

            if (!$region = $this->getRegionByName((string) $regionObject->country))
            {
                $regionId = $this->processRegion($regionObject);
            }
            else
            {
                $regionId = $region['id'];
            }
        }


        if (( $this->information->{sfBuildspaceExportProjectXML::TAG_SUBREGION}->count() > 0 ) && $regionId != null)
        {
            $subRegionObject = $this->information->{sfBuildspaceExportProjectXML::TAG_SUBREGION}->children();

            if (!$subRegion = $this->getSubRegionByName((string) $subRegionObject->name))
            {
                $subRegionId = $this->processSubRegion($subRegionObject, $regionId);
            }
            else
            {
                $subRegionId = $subRegion['id'];
            }
        }

        if ($this->information->{sfBuildspaceExportProjectXML::TAG_WORKCAT}->count() > 0)
        {
            $workCategoryObject = $this->information->{sfBuildspaceExportProjectXML::TAG_WORKCAT}->children();

            if (!$workCategory = $this->getWorkCategoryByName((string) $workCategoryObject->name))
            {
                $workCategoryId = $this->processWorkCategory($workCategoryObject);
            }
            else
            {
                $workCategoryId = $workCategory['id'];
            }
        }

        if ($this->information->{sfBuildspaceExportProjectXML::TAG_CURRENCY}->count() > 0)
        {
            $currencyObject = $this->information->{sfBuildspaceExportProjectXML::TAG_CURRENCY}->children();

            if (!$currency = $this->getCurrencyByName((string) $currencyObject->currency_name))
            {
                $currencyId = $this->processCurrency($currencyObject);
            }
            else
            {
                $currencyId = $currency['id'];
            }
        }

        $this->information->region_id            = $regionId;
        $this->information->subregion_id         = $subRegionId;
        $this->information->work_category_id     = $workCategoryId;
        $this->information->currency_id          = $currencyId;
        $this->information->project_structure_id = $this->projectId;

        switch ($this->exportType)
        {
            case ExportedFile::EXPORT_TYPE_TENDER:
                $this->information->status = ProjectMainInformation::STATUS_IMPORT;
                break;
            case ExportedFile::EXPORT_TYPE_SUB_PACKAGE:
                $this->information->status = ProjectMainInformation::STATUS_IMPORT_SUB_PACKAGE;
                break;
            default:
                $this->information->status = ProjectMainInformation::STATUS_IMPORT;
                break;
        }

        $this->information->tender_type_id = ProjectMainInformation::TENDER_TYPE_PARTICIPATED;
        $this->information->unique_id      = (string) $this->projectUniqueId;
        $this->information->created_at     = 'NOW()';
        $this->information->updated_at     = 'NOW()';
        $this->information->published_at   = 'NOW()';
        $this->information->created_by     = $this->userId;
        $this->information->updated_by     = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData($this->information, true);

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(ProjectMainInformationTable::getInstance()->getTableName(), $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();

        unset( $this->information );

        return true;
    }

    protected function getWorkCategoryByName($name)
    {
        $stmt = $this->pdo->prepare("SELECT  w.id, w.name, w.description FROM " . WorkCategoryTable::getInstance()->getTableName() . " w
        WHERE LOWER(w.name) LIKE :name");

        $stmt->execute(array(
            'name' => strtolower($name)
        ));

        return $workCategory = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function getCurrencyByName($name)
    {
        $stmt = $this->pdo->prepare("SELECT c.id, c.currency_name, c.currency_code FROM " . CurrencyTable::getInstance()->getTableName() . " c
        WHERE LOWER(c.currency_name) LIKE :name");

        $stmt->execute(array(
            'name' => strtolower($name)
        ));

        return $currency = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function getRegionByName($name)
    {
        $stmt = $this->pdo->prepare("SELECT  r.id, r.iso, r.iso3, r.country FROM " . RegionsTable::getInstance()->getTableName() . " r
        WHERE LOWER(r.country) LIKE :name");

        $stmt->execute(array(
            'name' => strtolower($name)
        ));

        return $region = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function getSubRegionByName($name)
    {
        $stmt = $this->pdo->prepare("SELECT  sr.id, sr.name, sr.timezone FROM " . SubregionsTable::getInstance()->getTableName() . " sr
        WHERE LOWER(sr.name) LIKE :name");

        $stmt->execute(array(
            'name' => strtolower($name)
        ));

        return $subRegion = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function processRegion($region)
    {
        if ($region->id)
        {
            unset( $region->id );
        }

        $dataAndStructure = parent::generateArrayOfSingleData($region, true);

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(RegionsTable::getInstance()->getTableName(), $dataAndStructure['structure']);
        $stmt->addRecord($dataAndStructure['data']);
        $stmt->save();

        return $returningId = $stmt->returningIds[0];
    }

    protected function processSubRegion($subRegion, $regionId)
    {
        if ($subRegion->id)
        {
            unset( $subRegion->id );
        }

        $subRegion->region_id = (int) $regionId;

        $dataAndStructure = parent::generateArrayOfSingleData($subRegion, true);

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(SubregionsTable::getInstance()->getTableName(), $dataAndStructure['structure']);
        $stmt->addRecord($dataAndStructure['data']);
        $stmt->save();

        return $returningId = $stmt->returningIds[0];
    }

    protected function processWorkCategory($workCategory)
    {
        if ($workCategory->id)
        {
            unset( $workCategory->id );
        }

        $dataAndStructure = parent::generateArrayOfSingleData($workCategory, true);

        if(!in_array('created_at', $dataAndStructure['structure']))
        {
            array_push($dataAndStructure['structure'], 'created_at');
            array_push($dataAndStructure['data'], 'NOW()');
        }

        if(!in_array('updated_at', $dataAndStructure['structure']))
        {
            array_push($dataAndStructure['structure'], 'updated_at');
            array_push($dataAndStructure['data'], 'NOW()');
        }

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(WorkCategoryTable::getInstance()->getTableName(), $dataAndStructure['structure']);
        $stmt->addRecord($dataAndStructure['data']);
        $stmt->save();

        return $returningId = $stmt->returningIds[0];
    }

    protected function processCurrency($currency)
    {
        if ($currency->id)
        {
            unset( $currency->id );
        }

        $dataAndStructure = parent::generateArrayOfSingleData($currency, true);

        $stmt = new sfImportStatementGenerator();

        $stmt->createInsert(CurrencyTable::getInstance()->getTableName(), $dataAndStructure['structure']);
        $stmt->addRecord($dataAndStructure['data']);
        $stmt->save();

        return $returningId = $stmt->returningIds[0];
    }

    protected function processRevision()
    {
        $stmt = new sfImportStatementGenerator();

        foreach ($this->revision as $version)
        {
            if ($version->id)
            {
                $originalId = (int) $version->id;

                unset( $version->id );
            }

            $version->created_at                = 'NOW()';
            $version->updated_at                = 'NOW()';
            $version->tender_origin_id          = ProjectStructureTable::generateTenderOriginId($this->buildspaceId,
                $originalId, $this->originalProjectId);
            $version->created_by                = $this->userId;
            $version->updated_by                = $this->userId;
            $version->locked_status             = true;
            $version->current_selected_revision = false;
            $version->project_structure_id      = $this->projectId;

            $dataAndStructure = parent::generateArrayOfSingleData($version, true);

            $stmt->createInsert(ProjectRevisionTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $this->versionIds[$originalId] = $stmt->returningIds[0];

            unset( $version );
        }

        //Update Revision Setting
        $lastRevision = DoctrineQuery::create()->select('r.id')
            ->from('ProjectRevision r')
            ->where('r.project_structure_id = ?', (int) $this->projectId)
            ->orderBy('r.id DESC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        //Update Root Id
        $stmt->updateRecord(ProjectRevisionTable::getInstance()->getTableName(),
            array(
                'id' => $lastRevision['id']
            ),
            array(
                'current_selected_revision' => true
            )
        );

        unset( $this->revision );
    }

    protected function processBreakdown()
    {
        $stmt = new sfImportStatementGenerator();

        foreach ($this->breakdown as $structure)
        {
            $originalId = (int) $structure->id;
            unset( $structure->id );

            $structure->tender_origin_id = ProjectStructureTable::generateTenderOriginId($this->buildspaceId, $originalId, $this->originalProjectId);
            $structure->created_at       = 'NOW()';
            $structure->updated_at       = 'NOW()';
            $structure->created_by       = $this->userId;
            $structure->updated_by       = $this->userId;
            $structure->root_id          = $this->projectId;

            $dataAndStructure = parent::generateArrayOfSingleData($structure, true);

            $stmt->createInsert(ProjectStructureTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $this->breakdownIds[$originalId] = $stmt->returningIds[0];

            if ($structure->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_STYLE}->count() > 0)
            {
                $this->processProjectSummaryStyle($structure->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_STYLE}->children(),
                    $this->breakdownIds[$originalId], $stmt);
            }

            if ($structure->type == ProjectStructure::TYPE_BILL)
            {
                $billMarkupSetting                       = new BillMarkupSetting();
                $billMarkupSetting->project_structure_id = $this->breakdownIds[$originalId];
                $billMarkupSetting->rounding_type        = BillMarkupSetting::ROUNDING_TYPE_DISABLED;
                $billMarkupSetting->save();

                unset( $billMarkupSetting );
            }

            unset( $structure );
        }

        unset( $this->breakdown );
    }

    protected function processTenderAlternatives()
    {
        $stmt = new sfImportStatementGenerator();

        $latestRevision = DoctrineQuery::create()->select('r.id')
            ->from('ProjectRevision r')
            ->where('r.project_structure_id = ?', (int) $this->projectId)
            ->orderBy('r.id DESC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        
        foreach ($this->tenderAlternatives as $tenderAlternative)
        {
            if ($tenderAlternative->id)
            {
                $originalId = (int) $tenderAlternative->id;

                unset( $tenderAlternative->id );
            }

            if($tenderAlternative->tender_amount)
            {
                /* we unset this values because there is not column related to this values in buildspace tenderAlternative table.
                 * This values is used for Eproject when importing TenderAlternative in Eproject.
                 */
                unset($tenderAlternative->tender_amount, $tenderAlternative->tender_amount_except_prime_cost_provisional, $tenderAlternative->tender_som_amount);
            }

            $tenderAlternative->created_at                = 'NOW()';
            $tenderAlternative->updated_at                = 'NOW()';
            $tenderAlternative->tender_origin_id          = ProjectStructureTable::generateTenderOriginId($this->buildspaceId,
                $originalId, $this->originalProjectId);
            $tenderAlternative->created_by                = (int)$this->userId;
            $tenderAlternative->updated_by                = (int)$this->userId;
            $tenderAlternative->project_structure_id      = (int)$this->projectId;
            $tenderAlternative->project_revision_id       = $latestRevision['id'];

            $dataAndStructure = parent::generateArrayOfSingleData($tenderAlternative, true);

            $stmt->createInsert(TenderAlternativeTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $this->tenderAlternativeIds[$originalId] = $stmt->returningIds[0];
            
            unset( $tenderAlternative );
        }

        foreach($this->tenderAlternativesBills as $xref)
        {
            if(array_key_exists((int)$xref->tender_alternative_id, $this->tenderAlternativeIds) && array_key_exists((int)$xref->project_structure_id, $this->breakdownIds))
            {
                $xref->tender_alternative_id = $this->tenderAlternativeIds[(int)$xref->tender_alternative_id];
                $xref->project_structure_id  = $this->breakdownIds[(int)$xref->project_structure_id];
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

        unset( $this->tenderAlternatives, $this->tenderAlternativeIds, $this->tenderAlternativesBills );
    }

    protected function processProjectSummaryStyle(SimpleXMLElement $projectSummaryStyle, $projectStructureId, sfImportStatementGenerator $stmt)
    {
        unset( $projectSummaryStyle->id );

        $projectSummaryStyle->project_structure_id = (int) $projectStructureId;//overwrite the existing project structure id to a newly created project structure id

        $projectSummaryStyle->created_at = 'NOW()';
        $projectSummaryStyle->updated_at = 'NOW()';
        $projectSummaryStyle->created_by = $this->userId;
        $projectSummaryStyle->updated_by = $this->userId;

        $dataAndStructure = parent::generateArrayOfSingleData($projectSummaryStyle, true);

        $stmt->createInsert(ProjectSummaryBillStyleTable::getInstance()->getTableName(),
            $dataAndStructure['structure']);

        $stmt->addRecord($dataAndStructure['data']);

        $stmt->save();
    }

    protected function validateImportedFile($exportType)
    {
        if ($exportType != ExportedFile::EXPORT_TYPE_TENDER)
        {
            throw new Exception('Please Choose a correct Project File');
        }
    }

    protected function checkProjectExist($buildspaceId, $originalProjectId)
    {
        if (self::getOriginalProjectInformationByOriginId($buildspaceId, $originalProjectId))
        {
            throw new Exception('Project Already Exists.');
        }
    }

    public function getOriginalProjectInformation()
    {
        return self::getOriginalProjectInformationByOriginId($this->buildspaceId, $this->originalProjectId);
    }

    public static function getOriginalProjectInformationByOriginId($buildspaceId, $projectId, $subPackageId = null)
    {
        $originalProjectInfo = DoctrineQuery::create()->select('p.id, p.title, p.priority, p.tender_origin_id, m.*, r.country, sr.name, pc.published_type AS post_contract_type, p.created_at')
            ->from('ProjectStructure p')
            ->leftJoin('p.PostContract pc')
            ->leftJoin('p.MainInformation m')
            ->leftJoin('m.Regions r')
            ->leftJoin('m.Subregions sr')
            ->where('p.tender_origin_id = ?', ProjectStructureTable::generateTenderOriginId($buildspaceId, $projectId, $projectId))
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        return $originalProjectInfo;
    }

}
