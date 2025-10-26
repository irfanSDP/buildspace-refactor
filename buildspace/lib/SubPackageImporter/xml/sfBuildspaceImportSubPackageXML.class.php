<?php
class sfBuildspaceImportSubPackageXML extends sfBuildspaceImportProjectXML
{
	public $subPackageId;

	protected function extractData()
    {
        parent::read();

        $xmlData = parent::getProcessedData();

        $this->rootProject = ($xmlData->{sfBuildspaceExportProjectXML::TAG_ROOT}->count() > 0) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_ROOT}->children() : false;
	
		$this->subPackageId = $xmlData->attributes()->subPackageId;
	
        $this->validateImportedFile((int) $xmlData->attributes()->exportType);   
		
		$this->exportType = $xmlData->attributes()->exportType;

        $this->checkProjectExist((string) $xmlData->attributes()->buildspaceId, (int) $this->rootProject->id, (int) $xmlData->attributes()->subPackageId);

        $this->buildspaceId = ((string) $xmlData->attributes()->buildspaceId) ? (string) $xmlData->attributes()->buildspaceId : false;

        $this->projectUniqueId = $xmlData->attributes()->uniqueId;

        $this->information = ($xmlData->{sfBuildspaceExportProjectXML::TAG_INFORMATION}->count() > 0) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_INFORMATION}->children() : false;

        $this->revision = ($xmlData->{sfBuildspaceExportProjectXML::TAG_REVISIONS}->count() > 0) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_REVISIONS}->children() : false; 
        
        $this->breakdown = ($xmlData->{sfBuildspaceExportProjectXML::TAG_BREAKDOWN}->count() > 0) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_BREAKDOWN}->children() : false;
    }

	protected function insertRootProject()
    {
        if(!$this->rootProject)
            return false;

        $originalId = (int) $this->rootProject->id;

        $projectSummaryFooter = ($this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_FOOTER}->count() > 0) ? $this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_FOOTER}->children() : null;

        $projectSummaryGeneralSetting = ($this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_GENERAL_SETTING}->count() > 0) ? $this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_GENERAL_SETTING}->children() : null;

        unset($this->rootProject->id);

        $this->rootProject->tender_origin_id = ProjectStructureTable::generateSubPackageOriginId($this->buildspaceId, $originalId, $originalId, $this->subPackageId);
        $this->rootProject->created_at = 'NOW()';
        $this->rootProject->updated_at = 'NOW()';
        $this->rootProject->created_by = $this->userId;
        $this->rootProject->updated_by = $this->userId;
        $this->rootProject->priority = 0;

        $this->updateProjectPriority( (int) $this->rootProject->priority);

        $dataAndStructure = parent::generateArrayOfSingleData( $this->rootProject, true );

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

        if($projectSummaryFooter instanceof SimpleXMLElement)
        {
            parent::processProjectSummaryFooter($projectSummaryFooter, $stmt);
            unset($projectSummaryFooter);
        }

        if($projectSummaryGeneralSetting instanceof SimpleXMLElement)
        {
            parent::processProjectSummaryGeneralSetting($projectSummaryGeneralSetting, $stmt);
            unset($projectSummaryGeneralSetting);
        }

        unset($this->rootProject);

        return true;
    }

    protected function processBreakdown()
    {
        $stmt = new sfImportStatementGenerator();

        foreach($this->breakdown as $structure)
        {
            $originalId = (int) $structure->id;
            unset($structure->id);

            $structure->tender_origin_id = ProjectStructureTable::generateSubPackageOriginId($this->buildspaceId, $originalId, $this->originalProjectId, $this->subPackageId);
            $structure->created_at = 'NOW()';
            $structure->updated_at = 'NOW()';
            $structure->created_by = $this->userId;
            $structure->updated_by = $this->userId;
            $structure->root_id = $this->projectId;

            $dataAndStructure = parent::generateArrayOfSingleData( $structure, true );

            $stmt->createInsert(ProjectStructureTable::getInstance()->getTableName(), $dataAndStructure['structure']);

            $stmt->addRecord($dataAndStructure['data']);

            $stmt->save();

            $this->breakdownIds[$originalId] = $stmt->returningIds[0];


            if($structure->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_STYLE}->count() > 0)
            {
                $this->processProjectSummaryStyle($structure->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_STYLE}->children(), $this->breakdownIds[$originalId], $stmt);
            }

            if($structure->type == ProjectStructure::TYPE_BILL)
            {
                $billMarkupSetting = new BillMarkupSetting();
                $billMarkupSetting->project_structure_id = $this->breakdownIds[$originalId];
                $billMarkupSetting->rounding_type = BillMarkupSetting::ROUNDING_TYPE_DISABLED;
                $billMarkupSetting->save();

                unset($billMarkupSetting);
            }

            unset($structure);
        }

        unset($this->breakdown);
    }

    protected function validateImportedFile($exportType)
    {
        if($exportType != ExportedFile::EXPORT_TYPE_SUB_PACKAGE)
            throw new Exception('Please Choose a correct SubPackage File');
    }

    protected function checkProjectExist($buildspaceId, $originalProjectId)
    {
    	$subPackageId = $this->subPackageId;
		
        if(self::getOriginalProjectInformationByOriginId($buildspaceId, $originalProjectId, $subPackageId))
            throw new Exception('SubPackage Already Exists.');
    }
	
	public function getOriginalProjectInformation()
    {
        return self::getOriginalProjectInformationByOriginId($this->buildspaceId, $this->originalProjectId, $this->subPackageId);
    }

    public static function getOriginalProjectInformationByOriginId( $buildspaceId, $projectId, $subPackageId = null )
    {
    	$query = DoctrineQuery::create()->select('p.id, p.title, p.tender_origin_id, m.*, r.country, sr.name, p.created_at')
            ->from('ProjectStructure p')
            ->leftJoin('p.MainInformation m')
            ->leftJoin('m.Regions r')
            ->leftJoin('m.Subregions sr')
            ->where('p.tender_origin_id = ?', ProjectStructureTable::generateSubPackageOriginId($buildspaceId, $projectId, $projectId, $subPackageId))
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

        return $originalProjectInfo = ($query->count() > 0) ? $query->fetchOne() : false;
    }
}