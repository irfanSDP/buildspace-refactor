<?php

class sfBuildspaceImportProjectClaimsXML extends sfBuildspaceXMLParser {
    public $exporterRootProject;
    public $exporterProjectOriginInformation;
    public $exporterBuildspaceId;
    public $targetIsOriginProject = false;
    public $targetProject;

    function __construct($filename = null, $uploadPath = null, $extension = null, $deleteFile = null)
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        parent::__construct($filename, $uploadPath, $extension, $deleteFile);
    }

    public function setParameters($targetProject)
    {
        $this->targetProject = $targetProject;
    }

    public function process()
    {
        parent::read();

        $xmlData = parent::getProcessedData();

        $this->validateImportedFile((int)$xmlData->attributes()->exportType);

        $this->exporterRootProject = $xmlData->{sfBuildspaceExportProjectXML::TAG_ROOT}->children();

        $this->exporterBuildspaceId = (string)$xmlData->attributes()->buildspaceId;

        $this->exporterProjectOriginInformation = ProjectStructureTable::extractOriginId((string)$this->exporterRootProject->tender_origin_id);

        if( $this->exporterProjectOriginInformation && ( $this->exporterProjectOriginInformation['buildspace_id'] == sfConfig::get('app_register_buildspace_id') ) ) $this->targetIsOriginProject = true;

        $this->validateTargetProject();
    }

    protected function validateTargetProject()
    {
        if( $this->targetIsOriginProject )
        {
            $originProjectId = $this->exporterProjectOriginInformation['origin_id'];
        }
        else
        {
            $exporterProjectId = (int)$this->exporterRootProject->id;

            $projectTenderOriginId = ProjectStructureTable::generateTenderOriginId($this->exporterBuildspaceId, $exporterProjectId, $exporterProjectId);

            $stmt = $this->pdo->prepare("SELECT p.id
                FROM " . ProjectStructureTable::getInstance()->getTableName() . " p
                WHERE p.tender_origin_id = :projectTenderOriginId");

            $stmt->execute(array( 'projectTenderOriginId' => $projectTenderOriginId ));

            $originProjectId = $stmt->fetch(PDO::FETCH_COLUMN);

            if( ! $originProjectId ) $originProjectId = 0;
        }

        if( ( ! $originProject = Doctrine_Core::getTable('ProjectStructure')->find($originProjectId) ) || ( $originProject->id != $this->targetProject->id ) ) throw new Exception('Project mismatch detected. Please select the correct claims file.');
    }

    public function validateImportedFile($exportType)
    {
        if( $exportType != ExportedFile::EXPORT_TYPE_CLAIM )
            throw new Exception('Please Choose a correct Claim File');
    }

}