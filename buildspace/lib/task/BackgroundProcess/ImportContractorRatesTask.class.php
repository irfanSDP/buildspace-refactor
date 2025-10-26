<?php

class ImportContractorRatesTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addArgument('projectId', sfCommandArgument::REQUIRED, 'Project ID is required');
        $this->addArgument('companyId', sfCommandArgument::REQUIRED, 'Company ID is required');
        $this->addArgument('filePath', sfCommandArgument::REQUIRED, 'File path is required');
        $this->addArgument('userId', sfCommandArgument::OPTIONAL, '');

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace', 'backend'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace        = 'bgprocess';
        $this->name             = 'import_contractor_rates';
        $this->briefDescription = 'Import Contractor rates file for tendering';
        $this->detailedDescription = <<<EOF
    The [import_contractor_rates|INFO] task does things.
    Call it with:

    [php symfony bgprocess:import_contractor_rates|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $projectId  = (int)trim($arguments['projectId']);
        $companyId  = (int)trim($arguments['companyId']);
        $filePath = trim($arguments['filePath']);

        $user = null;

        if((int)$arguments['userId'])
        {
            $user = sfGuardUserTable::getInstance()->find((int)$arguments['userId']);
        }

        $project = ProjectStructureTable::getInstance()->find($projectId);
        $company = CompanyTable::getInstance()->find($companyId);

        set_time_limit(0);

        $this->logSection('import_contractor_rates', "Starting to import contractor rates!");

        if($project && $company && $filePath)
        {
            $this->import($project, $company, $filePath);

            return $this->logSection('import_contractor_rates', "Successfully imported contractor rates!");
        }

        return $this->logSection('Error: ', "Invalid info to import contractor rates");
    }

   protected function import(ProjectStructure $project, Company $company, $filePath)
   {
        $tenderCompanyInfo = DoctrineQuery::create()
            ->select('tc.id, tc.project_structure_id, tc.company_id, tc.total_amount')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $project->id)
            ->andWhere('tc.company_id = ?', $company->id)
            ->fetchOne();

        if ( !$tenderCompanyInfo )
        {
            $tenderCompanyInfo                       = new TenderCompany();
            $tenderCompanyInfo->project_structure_id = $project->id;
            $tenderCompanyInfo->company_id           = $company->id;
            $tenderCompanyInfo->show                 = true;
            $tenderCompanyInfo->save();
        }

        // flush contractor existing rates
        $tenderCompanyInfo->flushExistingRates();

        $this->logSection('import_contractor_rates', "Flushed previous rates");

        try
        {
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            $filename = pathinfo($filePath, PATHINFO_FILENAME);
            $dirname = pathinfo($filePath, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR;

            $sfZipGenerator = new sfZipGenerator($filename, $dirname, $ext, true, true);

            $extractedFiles = $sfZipGenerator->unzip();

            if(!is_array($extractedFiles))
            {
                return $this->logSection('import_contractor_rates', "No zip file");
            }

            $extractDir = $sfZipGenerator->extractDir;

            $count      = 0;
            $userId     = null;
            $totalFiles = count($extractedFiles);

            foreach ($extractedFiles as $file)
            {
                if ($count == 0)
                {
                    $importer = new sfBuildspaceXMLParser($file['filename'], $extractDir, null, false);

                    $importer->read();

                    $xmlData = $importer->getProcessedData();

                    if ($project->MainInformation->unique_id != $xmlData->attributes()->uniqueId)
                    {
                        throw new Exception(ProjectMainInformation::ERROR_MSG_WRONG_PROJECT_RATES);
                    }

                    if ($xmlData->attributes()->exportType != ExportedFile::EXPORT_TYPE_RATES)
                    {
                        throw new Exception(ExportedFile::ERROR_MSG_WRONG_RATES_FILE);
                    }
                }
                else
                {
                    $this->logSection('Importing file '.$count.': ', $file['filename']);

                    $importer = new sfBuildspaceXMLParser($file['filename'], $extractDir, null, false);

                    $importer->read();

                    if ($importer->xml && $importer->xml->attributes()->isSupplyOfMaterialBill)
                    {
                        $importer = new sfBuildspaceImportSupplyOfMaterialBillRatesXML(
                            $userId,
                            $project->toArray(),
                            $company->toArray(),
                            $tenderCompanyInfo->toArray(),
                            $file['filename'],
                            $extractDir,
                            null,
                            false
                        );
                    }
                    else if ($importer->xml && $importer->xml->attributes()->isScheduleOfRateBill)
                    {
                        $importer = new sfBuildspaceImportScheduleOfRateBillRatesXML(
                            $userId,
                            $project->toArray(),
                            $company->toArray(),
                            $tenderCompanyInfo->toArray(),
                            $file['filename'],
                            $extractDir,
                            null,
                            false
                        );
                    }
                    else
                    {
                        $importer = new sfBuildspaceImportBillRatesXML(
                            $userId,
                            $project->toArray(),
                            $company->toArray(),
                            $tenderCompanyInfo->toArray(),
                            $file['filename'],
                            $extractDir,
                            null,
                            false
                        );
                    }

                    $importer->process();

                    unset($importer);

                    $this->logSection('Successfully imported file '.$count.': ', $file['filename']);
                }

                $count ++;
            }

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;

            $this->logSection('Error: ', $errorMsg);
        }

        if(file_exists($filePath))
        {
            unlink($filePath);
        }

        $logFile = sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR.'sync_contractor_rates-'.$project->id.'-'.$company->id.'.log';

        if($totalFiles == $count && file_exists($logFile))
        {
            //successfully imported all files
            unlink($logFile);
        }

        return $this->logSection('import_contractor_rates', $project->id." ".$company->name." ".$filePath);
   }
}
