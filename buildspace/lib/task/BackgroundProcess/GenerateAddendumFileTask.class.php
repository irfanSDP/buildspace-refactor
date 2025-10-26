<?php

use GuzzleHttp\Client;

class GenerateAddendumFileTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addArgument('projectId', sfCommandArgument::REQUIRED, 'Project ID is required');
        $this->addArgument('projectRevisionId', sfCommandArgument::REQUIRED, 'Project Revision ID is required');
        $this->addArgument('eProjectUserId', sfCommandArgument::REQUIRED, 'EProject User ID is required');

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace', 'backend'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace        = 'bgprocess';
        $this->name             = 'generate_addendum_file';
        $this->briefDescription = 'Generate addendum file in zip format and upload it to EProject directory';
        $this->detailedDescription = <<<EOF
    The [generate_addendum_file|INFO] task does things.
    Call it with:

    [php symfony bgprocess:generate_addendum_file|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $project        = Doctrine_Core::getTable('ProjectStructure')->find((int)$arguments['projectId']);
        $revision       = Doctrine_Core::getTable('ProjectRevision')->find((int)$arguments['projectRevisionId']);
        $eProjectUserId = (int)$arguments['eProjectUserId'];

        $fileInfo = $this->generateAddendumZipFile($project, $revision);

        $this->uploadFileToEProject($project->MainInformation, $fileInfo, 'project_addendum', $revision, $eProjectUserId);

        return $this->logSection('generate_addendum_file', "Successfully created addendum file!");
    }

    protected function generateAddendumZipFile(ProjectStructure $project, ProjectRevision $revision)
    {
        $projectInfo = ProjectStructureTable::getProjectInformationByProjectId($project->id);

        $filesToZip = array();

        $projectUniqueId = $projectInfo['mainInformation']['unique_id'];

        $count = 0;

        $sfProjectExport = new sfBuildspaceExportProjectXML($count . "-" . $projectInfo['structure']['id'], $projectUniqueId, ExportedFile::EXPORT_TYPE_ADDENDUM);

        $sfProjectExport->process($projectInfo['structure'], $projectInfo['mainInformation'], $projectInfo['breakdown'], array( $revision ), $projectInfo['tenderAlternatives'], true);

        array_push($filesToZip, $sfProjectExport->getFileInformation());

        $bills = DoctrineQuery::create()
            ->from('ProjectStructure s')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', ProjectStructure::TYPE_BILL)
            ->addOrderBy('s.lft ASC')
            ->execute();

        $revisionArray = $revision->toArray();

        foreach($bills as $bill)
        {
            $count++;

            $billData = $bill->getBillRevisionData($revision);

            if( $billData AND is_array($billData) )
            {
                $sfBillExport = new sfBuildspaceExportBillAddendumXML($count . '-' . $bill->id . '-' . $bill->title, $sfProjectExport->uploadPath, $bill->id, $revisionArray);

                $sfBillExport->process($billData, true);

                array_push($filesToZip, $sfBillExport->getFileInformation());
            }
        }

        $filename = "bs_project_{$projectInfo['mainInformation']['id']}_{$revision->revision}";

        $sfZipGenerator = new sfZipGenerator($filename, null, null, true, true);

        $sfZipGenerator->createZip($filesToZip);

        return $sfZipGenerator->getFileInfo();
    }

    protected function uploadFileToEProject(ProjectMainInformation $mainInfo, Array $fileInfo, $apiLink, ProjectRevision $revision, $eProjectUserId)
    {
        $projectOriginId = $mainInfo->eproject_origin_id;

        // $projectOriginId has been set to null before, no identifiable cause.
        if(empty($projectOriginId))
        {
            throw new Exception('Column eproject_origin_id is null (in ' . ProjectMainInformationTable::getInstance()->getTableName() . ' table), project does not exist in eproject.');
        }

        $client = new Client(array(
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        try
        {
            $res = $client->post($apiLink."/".$projectOriginId, [
                'multipart' => [
                    [
                        'name'     => 'posted_by',
                        'contents' => $eProjectUserId
                    ],[
                        'name'     => 'file',
                        'contents' => fopen($fileInfo['pathToFile'], 'r')
                    ],[
                        'name'     => 'addendumVersion',
                        'contents' => $revision->version
                    ]
                ]
            ]);

            unlink($fileInfo['pathToFile']);

            return $res;
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }
}
