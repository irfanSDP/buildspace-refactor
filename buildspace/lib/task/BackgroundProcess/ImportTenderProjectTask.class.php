<?php

class ImportTenderProjectTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addArgument('filename', sfCommandArgument::REQUIRED, 'Filename is required');
        $this->addArgument('extension', sfCommandArgument::REQUIRED, 'Extension name is required');
        $this->addArgument('uploadPath', sfCommandArgument::REQUIRED, 'Upload path is required');
        $this->addArgument('userId', sfCommandArgument::OPTIONAL, '');

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace', 'backend'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace        = 'bgprocess';
        $this->name             = 'import_tender_project';
        $this->briefDescription = 'Import EBQ file for tendering';
        $this->detailedDescription = <<<EOF
    The [import_tender_project|INFO] task does things.
    Call it with:

    [php symfony bgprocess:import_tender_project|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $filename   = trim($arguments['filename']);
        $extension  = trim($arguments['extension']);
        $uploadPath = trim($arguments['uploadPath']);

        $user = null;

        if((int)$arguments['userId'])
        {
            $user = sfGuardUserTable::getInstance()->find((int)$arguments['userId']);
        }

        set_time_limit(0);

        $this->import($filename, $extension, $uploadPath, $user);

        return $this->logSection('import_tender_project', "Successfully imported tender project!");
    }

   protected function import($filename, $extension, $uploadPath, sfGuardUser $user=null)
   {
        $con = ProjectStructureTable::getInstance()->getConnection();

        $logDir = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'importTenderLog';
        if(!is_dir($logDir) )
        {
            mkdir($logDir, 0777, true);
        }

        try
        {
            $con->beginTransaction();

            $sfImport = new sfBuildspaceXMLParser($filename, $uploadPath, $extension);
            $sfImport->read();
            $fileInfo = $sfImport->getProcessedData();
            unset( $sfImport );

            $extractDir = $fileInfo->attributes()->extractDir;

            $uniqueId      = null;
            $breakdownIds  = [];
            $versionIds    = [];
            $unitIds       = [];
            $project       = null;
            $count         = 0;
            $userId        = ($user) ? $user->id : 1;
            $yamlFilename  = "import_tender.yaml";
            $billCount     = 0;

            $projectFiles = [];

            foreach($fileInfo->{sfBuildspaceFileInfoXML::TAG_FILES}->children() as $file)
            {
                $file = $file->children();

                switch($count)
                {
                    case 0:

                        $xmlParser = new sfBuildspaceXMLParser((string)$file->filename, $extractDir);
                        $xmlParser->read();

                        if( $xmlParser->xml->attributes()->exportType == ExportedFile::EXPORT_TYPE_SUB_PACKAGE )
                        {
                            $importer = new sfBuildspaceImportSubPackageXML($userId, (string)$file->filename,
                                $extractDir, null, true);
                        }
                        else
                        {
                            $importer = new sfBuildspaceImportProjectXML($userId, (string)$file->filename, $extractDir,
                                null, true);
                        }

                        $projectInfo = $importer->getProjectInformation();
                        $uniqueId = $projectInfo['unique_id'];

                        $importer->process();

                        $breakdownIds = $importer->breakdownIds;

                        $versionIds = $importer->versionIds;

                        $project = $importer->getOriginalProjectInformation();

                        
                        break;
                    default:

                        if( count($breakdownIds) && $project )
                        {
                            $projectFiles[] = $file;

                            $billCount++;
                        }
                        break;
                }

                unset( $importer, $file );

                $count++;
            }

            if( $user && ! $user->is_super_admin )
            {
                $userPermission                       = new ProjectUserPermission();
                $userPermission->project_structure_id = $project['id'];
                $userPermission->project_status       = ProjectUserPermission::STATUS_TENDERING;
                $userPermission->user_id              = $user->id;
                $userPermission->is_admin             = true;

                $userPermission->save($con);
            }

            if($project)
            {
                $this->logSection('import_tender_project', "Successfully imported project with id:".$project['id']);
            }

            $con->commit();

            if($project)
            {
                $executedTimestamp = date('Y-m-d H:i:s');
                $yamlDetails = [
                    'project_id' => $project['id'],
                    'total_bills' => count($projectFiles),
                    'total_imported_bills' => 0,
                    'executed_by' => ($user) ? $user->id : 1,
                    'executed_at' => $executedTimestamp
                ];
        
                $yaml = sfYaml::dump($yamlDetails);

                $yamlFilename = $uniqueId.'-import_tender.yaml';

                file_put_contents($logDir.DIRECTORY_SEPARATOR.$yamlFilename, $yaml);
            }
        }
        catch(Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();

            if(file_exists($logDir.DIRECTORY_SEPARATOR.$yamlFilename))
            {
                unlink($logDir.DIRECTORY_SEPARATOR.$yamlFilename);
            }

            return $this->logSection('import_tender_project', "Error Msg:".$errorMsg);
        }

        $totalBills = count($projectFiles);
        $totalImportedBills = 0;

        if(!$project or empty($totalBills))
        {
            return $this->logSection('import_tender_bills', "No bill to be imported!");
        }

        foreach($projectFiles as $file)
        {
            try
            {
                $con->beginTransaction();

                $xmlParser = new sfBuildspaceXMLParser((string)$file->filename, $extractDir);
                $xmlParser->read();

                if( $xmlParser->xml->attributes()->isSupplyOfMaterialBill )
                {
                    $importer = new sfBuildspaceImportSupplyOfMaterialBillXML($userId,
                        (string)$file->filename,
                        $extractDir, $project, $breakdownIds, $unitIds, null, true);
                }
                else if( $xmlParser->xml->attributes()->isScheduleOfRateBill )
                {
                    $importer = new sfBuildspaceImportScheduleOfRateBillXML($userId,
                        (string)$file->filename,
                        $extractDir, $project, $breakdownIds, $unitIds, null, true);
                }
                else
                {
                    $importer = new sfBuildspaceImportBillXML($userId, (string)$file->filename,
                        $extractDir, $project, $breakdownIds, $unitIds, $versionIds, null, true);
                }

                $importer->process();

                $unitIds = $importer->unitIds;

                unset($xmlParser, $importer, $file );

                $totalImportedBills++;

                $con->commit();

                $this->logSection('import_tender_bills', "Total imported bills ".$totalImportedBills);

                $yamlDetails = [
                    'project_id' => $project['id'],
                    'total_bills' => $totalBills,
                    'total_imported_bills' => $totalImportedBills,
                    'executed_by' => ($user) ? $user->id : 1,
                    'executed_at' => $executedTimestamp,
                    'finished_at' => date('Y-m-d H:i:s')
                ];

                $yaml = sfYaml::dump($yamlDetails);

                file_put_contents($logDir.DIRECTORY_SEPARATOR.$yamlFilename, $yaml);
            }
            catch(Exception $e)
            {
                $con->rollback();
                $errorMsg = $e->getMessage();

                if(file_exists($logDir.DIRECTORY_SEPARATOR.$yamlFilename))
                {
                    unlink($logDir.DIRECTORY_SEPARATOR.$yamlFilename);
                }

                return $this->logSection('import_tender_bills', "Error Msg:".$errorMsg);
            }
        }

        if(file_exists($logDir.DIRECTORY_SEPARATOR.$yamlFilename))
        {
            unlink($logDir.DIRECTORY_SEPARATOR.$yamlFilename);
        }
   }
}
