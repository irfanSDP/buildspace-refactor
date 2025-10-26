<?php

class ImportPostContractClaimTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addArgument('filename', sfCommandArgument::REQUIRED, 'Filename is required');
        $this->addArgument('extension', sfCommandArgument::REQUIRED, 'Extension name is required');
        $this->addArgument('uploadPath', sfCommandArgument::REQUIRED, 'Upload path is required');
        $this->addArgument('revisionId', sfCommandArgument::REQUIRED, 'Post Contract Claim Revision ID is required');
        $this->addArgument('userId', sfCommandArgument::REQUIRED, 'User ID');

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace', 'backend'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace        = 'bgprocess';
        $this->name             = 'import_post_contract_claim';
        $this->briefDescription = 'Import Post Contract Claim EBQ file';
        $this->detailedDescription = <<<EOF
    The [import_post_contract_claim|INFO] task does things.
    Call it with:

    [php symfony bgprocess:import_post_contract_claim|INFO]
EOF;
    }

    protected function execute($arguments = [], $options = [])
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $filename   = trim($arguments['filename']);
        $extension  = trim($arguments['extension']);
        $uploadPath = trim($arguments['uploadPath']);
        $revisionId = trim($arguments['revisionId']);

        $claimRevision = PostContractClaimRevisionTable::getInstance()->find((int)$revisionId);

        if(!$claimRevision)
        {
            return $this->logSection('import_post_contract_claim', "Claim Revision does not exist!");
        }

        $user = sfGuardUserTable::getInstance()->find((int)$arguments['userId']);

        if(!$user)
        {
            return $this->logSection('import_post_contract_claim', "User does not exist!");
        }

        if(!file_exists($uploadPath . $filename.".".$extension) or !is_readable($uploadPath . $filename.".".$extension) )
        {
            return $this->logSection('import_post_contract_claim', "No file to be imported for revision id: ".$claimRevision->id." Path: ".$uploadPath . $filename.".".$extension);
        }

        set_time_limit(0);

        $this->import($claimRevision, $filename, $extension, $uploadPath, $user);

        return $this->logSection('import_post_contract_claim', "Successfully imported post contract claim!");
    }

    protected function import(PostContractClaimRevision $claimRevision, $filename, $extension, $uploadPath, sfGuardUser $user)
    {
        $logDir = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'importPostContractClaimLog';
        if(!is_dir($logDir) )
        {
            mkdir($logDir, 0777, true);
        }

        $project = $claimRevision->PostContract->ProjectStructure;

        try
        {
            $sfZipGenerator = new sfZipGenerator($filename, $uploadPath, $extension, true, true);

            $extractedFiles = $sfZipGenerator->unzip();

            $extractDir = $sfZipGenerator->extractDir;

            if( is_array($extractedFiles) && !empty($extractedFiles) )
            {
                $exporterBuildspaceId             = null;
                $exporterRootProject              = null;
                $exporterProjectOriginInformation = null;
                $attachmentPaths                  = [];

                $totalFiles = count($extractedFiles);
                $totalImportedFiles = 0;

                $executedTimestamp = date('Y-m-d H:i:s');
                $yamlDetails = [
                    'revision_id' => $claimRevision->id,
                    'project_id' => $project->id,
                    'total_files' => $totalFiles,
                    'total_imported_files' => 0,
                    'executed_by' => $user->id,
                    'executed_at' => $executedTimestamp
                ];
        
                $yaml = sfYaml::dump($yamlDetails);

                $yamlFilename = $project->id.'-import_claim.yaml';

                file_put_contents($logDir.DIRECTORY_SEPARATOR.$yamlFilename, $yaml);

                foreach((array)$extractedFiles as $file)
                {
                    if( basename($file['dirname']) != sfBuildspaceExportClaimsXML::FOLDER_NAME_ATTACHMENTS ) continue;
                    $attachmentPaths[ $file['filename'] ] = $file['dirname'] . DIRECTORY_SEPARATOR . $file['basename'];
                }

                foreach((array)$extractedFiles as $file)
                {
                    if( basename($file['dirname']) == sfBuildspaceExportClaimsXML::FOLDER_NAME_ATTACHMENTS ) continue;

                    if( $totalImportedFiles == 0 )
                    {
                        $importer = new sfBuildspaceImportProjectClaimsXML($file['filename'], $extractDir, null, true);

                        $importer->setParameters($project);

                        $importer->process();

                        $exporterRootProject              = $importer->exporterRootProject;
                        $exporterBuildspaceId             = $importer->exporterBuildspaceId;
                        $exporterProjectOriginInformation = $importer->exporterProjectOriginInformation;
                    }
                    else
                    {
                        $importer = new sfBuildspaceImportClaimsXML((string)$file['filename'], $extractDir, null, true);
                        $importer->setParameters($project, $claimRevision, $exporterBuildspaceId, $exporterRootProject->id, $exporterProjectOriginInformation, $user->id, $attachmentPaths);

                        $importer->process();
                    }

                    $totalImportedFiles++;

                    $this->logSection('import_post_contract_claim', "Total imported files ".$totalImportedFiles);

                    $yamlDetails = [
                        'revision_id' => $claimRevision->id,
                        'project_id' => $project->id,
                        'total_files' => $totalFiles,
                        'total_imported_files' => $totalImportedFiles,
                        'executed_by' => $user->id,
                        'executed_at' => $executedTimestamp,
                        'finished_at' => date('Y-m-d H:i:s')
                    ];
    
                    $yaml = sfYaml::dump($yamlDetails);
    
                    file_put_contents($logDir.DIRECTORY_SEPARATOR.$yamlFilename, $yaml);
                }

                $claimRevision->claim_submission_locked = true;
                $claimRevision->save();

                ClaimImportLogTable::log($claimRevision->id, $user->id);
                Notifications::sendContractorClaimSubmittedNotifications($claimRevision->id);

                if(file_exists($logDir.DIRECTORY_SEPARATOR.$yamlFilename))
                {
                    unlink($logDir.DIRECTORY_SEPARATOR.$yamlFilename);
                }

                return $this->logSection('import_post_contract_claim', "Successfully imported revision id: ".$claimRevision->id." project id:".$project->id);
            }

            return $this->logSection('import_post_contract_claim', "No file to be imported for revision id: ".$claimRevision->id." Path: ".$uploadPath . $filename.".".$extension);
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();

            if(file_exists($logDir.DIRECTORY_SEPARATOR.$yamlFilename))
            {
                unlink($logDir.DIRECTORY_SEPARATOR.$yamlFilename);
            }

            return $this->logSection('import_post_contract_claim', "Error Msg:".$errorMsg);
        }
    }

}