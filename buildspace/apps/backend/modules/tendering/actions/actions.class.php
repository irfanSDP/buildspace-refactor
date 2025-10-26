<?php

/**
 * tendering actions.
 *
 * @package    buildspace
 * @subpackage tendering
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class tenderingActions extends BaseActions {

    public function executeGetProjects(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $user = $this->getUser()->getGuardUser();

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => ProjectStructureTable::getProjectsByUser($user, ProjectUserPermission::STATUS_TENDERING)
        ));
    }

    public function executeMainInfoForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $data = array(
            'title'         => $structure->title,
            'description'   => $structure->MainInformation->description,
            'region'        => ProjectMainInformation::getCountryNameById($structure->MainInformation->region_id),
            'subregion'     => ProjectMainInformation::getStateNameById($structure->MainInformation->subregion_id),
            'work_category' => ProjectMainInformation::getWorkCategoryById($structure->MainInformation->work_category_id),
            'site_address'  => $structure->MainInformation->site_address,
            'client'        => $structure->MainInformation->client,
            'start_date'    => $structure->MainInformation->start_date ? date('Y-m-d', strtotime($structure->MainInformation->start_date)) : date('Y-m-d')
        );

        if( $structure->MainInformation->currency_id )
        {
            $data['currency'] = $structure->MainInformation->Currency->currency_code;
        }

        $data['eProjectReference'] = ( $structure->MainInformation->EProjectProject ) ? $structure->MainInformation->EProjectProject->reference : null;
        $data['isProjectOwner']    = ( $structure->created_by == $this->getUser()->getGuardUser()->getId() ) ? true : false;
        $data['isSuperAdmin']      = $this->getUser()->getGuardUser()->getIsSuperAdmin();

        return $this->renderJson($data);
    }

    public function executeGetProjectBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $records = DoctrineQuery::create()->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id, s.project_revision_id')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $structure->lft, $structure->rgt ))
            ->andWhere('s.root_id = ?', $structure->id)
            ->andWhere('s.type <= ?', ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL)
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        $count = 0;
        $form  = new BaseForm();

        $projectSumTotal                = ProjectStructureTable::getOverallTotalForProject($structure->id);
        $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($structure);
        $billsWithAddendums             = $structure->getBillsWithAddendums();
        $latestProjectRevision          = $structure->getLatestProjectRevision();
        $tenderAlternativesByBills      = $structure->getBillsWithTenderAlternatives();

        $currentlyEditingProjectRevision = ProjectRevisionTable::getCurrentlyEditingProjectRevisionFromBillId($structure->id);

        foreach($records as $key => $record)
        {
            $records[ $key ]['billLayoutSettingId'] = ( isset( $record['BillLayoutSetting']['id'] ) ) ? $record['BillLayoutSetting']['id'] : null;
            $count                                  = ( $record['type'] == ProjectStructure::TYPE_BILL or $record['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL or $record['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL ) ? $count + 1 : $count;

            if( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[ $key ]['BillType']) )
            {
                $records[ $key ]['bill_type']   = $record['BillType']['type'];
                $records[ $key ]['bill_status'] = $record['BillType']['status'];
            }

            $records[$key]['is_add_latest_rev'] = 0;
            $records[$key]['addendum_version']  = null;
            if($record['type'] == ProjectStructure::TYPE_BILL && array_key_exists($record['id'], $billsWithAddendums))
            {
                $latestAddendum = end($billsWithAddendums[$record['id']]);
                $records[$key]['is_add_latest_rev'] = (int)($latestAddendum['version']==$latestProjectRevision->version);
                $records[$key]['addendum_version']  = (int)$latestAddendum['version'];
                unset($billsWithAddendums[$record['id']]);
            }

            $records[ $key ]['count']                      = ( $record['type'] == ProjectStructure::TYPE_BILL or $record['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL or $record['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL ) ? $count : null;
            $records[ $key ]['original_total']             = $record['type'] == ProjectStructure::TYPE_BILL ? ProjectStructureTable::getOverallOriginalTotalByBillId($record['id']) : 0;
            $records[ $key ]['overall_total_after_markup'] = ($record['type'] == ProjectStructure::TYPE_BILL && array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;
            $records[ $key ]['bill_sum_total']             = $projectSumTotal;
            $records[ $key ]['recalculate']                = $record['id'];
            $records[ $key ]['tender_alternative_count']   = array_key_exists($record['id'], $tenderAlternativesByBills) ? count($tenderAlternativesByBills[$record['id']]) : 0;
            $records[ $key ]['can_delete']                 = $currentlyEditingProjectRevision ? ($currentlyEditingProjectRevision->id == $record['project_revision_id']) : false;
            $records[ $key ]['_csrf_token']                = $form->getCSRFToken();

            unset( $records[ $key ]['BillLayoutSetting'] );
            unset( $records[ $key ]['BillType'] );
            unset( $records[ $key ]['BillColumnSettings'] );
        }

        array_push($records, array(
            'id'                         => Constants::GRID_LAST_ROW,
            'title'                      => "",
            'type'                       => 1,
            'level'                      => 0,
            'billLayoutSettingId'        => null,
            'is_add_latest_rev'          => 0,
            'addendum_version'           => null,
            'count'                      => null,
            'original_total'             => 0,
            'overall_total_after_markup' => 0,
            'bill_sum_total'             => 0,
            '_csrf_token'                => $form->getCSRFToken(),
            'recalculate'                => false
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetAddendumInfoByBill(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );
        $addendums = $bill->getAddendumInfo();
        return $this->renderJson($addendums);
    }
    
    public function executeGetAddendumInfoByElement(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id'))
        );
        $addendums = $element->getAddendumInfo();
        return $this->renderJson($addendums);
    }
    
    public function executeGetProjectStructure(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $collection = DoctrineQuery::create()->select('s.id, s.title, s.type, s.level, bls.id')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillLayoutSetting bls')
            ->where('s.lft > ? AND s.rgt < ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        $form = new BaseForm();

        $trees = array();

        if( count($collection) > 0 )
        {
            // Node Stack. Used to help building the hierarchy
            $stack = array();

            foreach($collection as $child)
            {
                $item = array(
                    'id'                  => $child['id'],
                    'billLayoutSettingId' => ( isset( $child['BillLayoutSetting']['id'] ) ) ? $child['BillLayoutSetting']['id'] : null,
                    'title'               => $child['title'],
                    'type'                => $child['type'],
                    'level'               => $child['level'],
                    '_csrf_token'         => $form->getCSRFToken()
                );

                if( $child['type'] == ProjectStructure::TYPE_BILL )
                {
                    $billType = DoctrineQuery::create()->select('t.id, t.type, t.status')
                        ->from('BillType t')
                        ->where('t.project_structure_id = ?', $child['id'])
                        ->limit(1)
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->fetchOne();

                    $item['bill_type']   = $billType['type'];
                    $item['bill_status'] = $billType['status'];
                }

                $item['__children'] = array();

                // Number of stack items
                $l = count($stack);

                // Check if we're dealing with different levels
                while( $l > 0 && $stack[ $l - 1 ]['level'] >= $item['level'] )
                {
                    array_pop($stack);
                    $l--;
                }

                // Stack is empty (we are inspecting the root)
                if( $l == 0 )
                {
                    // Assigning the root child
                    $i           = count($trees);
                    $trees[ $i ] = $item;
                    $stack[]     = &$trees[ $i ];
                }
                else
                {
                    // Add child to parent
                    $i                                   = count($stack[ $l - 1 ]['__children']);
                    $stack[ $l - 1 ]['__children'][ $i ] = $item;
                    $stack[]                             = &$stack[ $l - 1 ]['__children'][ $i ];
                }
            }
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'title',
            'items'      => $trees
        ));
    }

    public function executeUploadAddendum(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_sys_temp_dir');

        $projectInformation = null;
        $projectBreakdown   = null;
        $errorMsg           = null;
        $pathToFile         = null;
        $fileToUnzip        = array();

        foreach($request->getFiles() as $file)
        {
            if( is_readable($file['tmp_name']) )
            {
                $fileToUnzip['name'] = $newName = Utilities::massageText(date('dmY_H_i_s'));
                $fileToUnzip['ext']  = $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $pathToFile          = $tempUploadPath . $newName . '.' . $ext;

                $fileToUnzip['path'] = $tempUploadPath;
                move_uploaded_file($file['tmp_name'], $pathToFile);
            }
            else
            {
                $success = false;
            }
        }

        $fileInfo = array();

        try
        {
            if( count($fileToUnzip) )
            {
                $sfZipGenerator = new sfZipGenerator($fileToUnzip['name'], $fileToUnzip['path'], $fileToUnzip['ext'],
                    true, true);

                $extractedFiles = $sfZipGenerator->unzip();

                $extractDir = $sfZipGenerator->extractDir;

                $count = 0;

                $userId = $this->getUser()->getGuardUser()->id;

                if( count($extractedFiles) )
                {
                    foreach($extractedFiles as $file)
                    {
                        if( $count == 0 )
                        {
                            $importer = new sfBuildspaceImportProjectAddendumXML($userId, $file['filename'],
                                $extractDir, null, true);

                            $importer->read();

                            $projectInformation = $importer->getProjectInformation();

                            $projectBreakdown = $importer->getProjectBreakdown();
                        }

                        $count++;
                    }

                    //Generate Temp File Info
                    $xmlGen = new sfBuildspaceFileInfoXML('temp_file_info', $extractDir);

                    $xmlGen->process($extractDir, $extractedFiles, true);

                    $fileInfo['uploadPath'] = $extractDir;
                    $fileInfo['filename']   = $xmlGen->filename;
                    $fileInfo['extension']  = $xmlGen->extension;

                }

                $success = true;
            }
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'errorMsg'         => $errorMsg,
            'success'          => $success,
            'tempFileInfo'     => $fileInfo,
            'projectInfo'      => ( $projectInformation ) ? $projectInformation : null,
            'projectBreakdown' => ( $projectBreakdown ) ? $projectBreakdown : null
        ));
    }

    public function executeImportAddendum(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);
        $filename   = $request->getParameter('filename');
        $extension  = $request->getParameter('extension');
        $uploadPath = $request->getParameter('uploadPath');
        $project    = ProjectStructureTable::getInstance()->find((int)$request->getParameter('pid'));
        $uniqueId   = trim($project->MainInformation->unique_id);

        $logDir = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'importTenderLog';
        $logFilename = $uniqueId."-import_addendum.yaml";

        $fileExists = file_exists($logDir.DIRECTORY_SEPARATOR.$logFilename);
        if($fileExists)
        {
            unlink($logDir.DIRECTORY_SEPARATOR.$logFilename);
        }

        $sfImport = new sfBuildspaceXMLParser($filename, $uploadPath, $extension);
        $sfImport->read();

        $fileInfo = $sfImport->getProcessedData();
        $extractDir = $fileInfo->attributes()->extractDir;

        foreach($fileInfo->{sfBuildspaceFileInfoXML::TAG_FILES}->children() as $file)
        {
            $file = $file->children();

            $importer = new sfBuildspaceImportProjectAddendumXML($this->getUser()->getGuardUser()->id, (string)$file->filename, $extractDir, null, true);
            
            try
            {
                $importer->validate();

                $xmlData = $importer->getProcessedData();
                $revisions = $xmlData->{sfBuildspaceExportProjectXML::TAG_REVISIONS}->children();
                $lastRevision = $revisions[count($revisions)-1];

                $version = (int)$lastRevision->version;
            }
            catch(Exception $e)
            {
                $errorMsg = $e->getMessage();

                return $this->renderJson([
                    'running'  => false,
                    'errorMsg' => $errorMsg
                ]);
            }
            break;//we only care for the project xml file which is the first from xml file list
        }

        $proc = new BackgroundProcess("exec php ".sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR."symfony bgprocess:import_tender_addendum ".$filename." ".$extension." '".$uploadPath."' ".$this->getUser()->getGuardUser()->id." 2>&1 | tee ".sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR."import_tender_addendum-".$uniqueId.".log");
        $proc->run();

        return $this->renderJson([
            'running' => true,
            'version' => $version
        ]);
    }

    public function executeGetImportAddendumProjectProgress(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $project = ProjectStructureTable::getInstance()->find((int)$request->getParameter('id'));

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();
            
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(r.version), 0)
        FROM " . ProjectRevisionTable::getInstance()->getTableName() . " r
        WHERE r.project_structure_id = ".$project->id."
        AND r.deleted_at IS NULL");
        
        $stmt->execute();
        
        $maxRevision = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        $exists = ((int)$request->getParameter('version') == $maxRevision);

        return $this->renderJson([
            'exists' => $exists
        ]);
    }

    public function executeGetImportAddendumBillProgress(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $project = ProjectStructureTable::getInstance()->find((int)$request->getParameter('id'));

        $totalBills = 0;
        $totalImportedBills = 0;

        if(!$project)
        {
            return $this->renderJson([
                'exists'               => false,
                'total_bills'          => $totalBills,
                'total_imported_bills' => $totalImportedBills
            ]);
        }

        $logDir   = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'importTenderLog';
        $filename = trim($project->MainInformation->unique_id)."-import_addendum.yaml";
        $exists   = file_exists($logDir.DIRECTORY_SEPARATOR.$filename);

        if($exists && $project)
        {
            $values             = sfYaml::load($logDir.DIRECTORY_SEPARATOR.$filename);
            $user               = sfGuardUserTable::getInstance()->find((int)$values['executed_by']);
            $totalBills         = (int)$values['total_bills'];
            $totalImportedBills = (int)$values['total_imported_bills'];
        }

        return $this->renderJson([
            'exists'               => $exists,
            'total_bills'          => $totalBills,
            'total_imported_bills' => $totalImportedBills
        ]);
    }

    public function executeUploadTenderProject(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_sys_temp_dir');

        $projectInformation = null;
        $projectBreakdown   = null;
        $errorMsg           = null;
        $pathToFile         = null;
        $fileToUnzip        = array();

        foreach($request->getFiles() as $file)
        {
            if( is_readable($file['tmp_name']) )
            {
                $fileToUnzip['name'] = $newName = Utilities::massageText(date('dmY_H_i_s'));
                $fileToUnzip['ext']  = $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $pathToFile          = $tempUploadPath . $newName . '.' . $ext;

                $fileToUnzip['path'] = $tempUploadPath;
                move_uploaded_file($file['tmp_name'], $pathToFile);
            }
            else
            {
                $success = false;
            }
        }

        $fileInfo = array();

        try
        {
            if( count($fileToUnzip) )
            {
                $sfZipGenerator = new sfZipGenerator($fileToUnzip['name'], $fileToUnzip['path'], $fileToUnzip['ext'],
                    true, true);

                $extractedFiles = $sfZipGenerator->unzip(false);

                $extractDir = $sfZipGenerator->extractDir;

                $count = 0;

                $userId = $this->getUser()->getGuardUser()->id;

                if( count($extractedFiles) )
                {
                    foreach($extractedFiles as $file)
                    {
                        if( $count == 0 )
                        {
                            $xmlParser = new sfBuildspaceXMLParser($file['filename'], $extractDir);
                            $xmlParser->read();

                            if( $xmlParser->xml->attributes()->exportType == ExportedFile::EXPORT_TYPE_SUB_PACKAGE )
                            {
                                $importer = new sfBuildspaceImportSubPackageXML($userId, $file['filename'], $extractDir,
                                    null, true);
                            }
                            else
                            {
                                $importer = new sfBuildspaceImportProjectXML($userId, $file['filename'], $extractDir,
                                    null, true);
                            }

                            $importer->read();

                            $projectInformation = $importer->getProjectInformation();

                            $projectBreakdown = $importer->getProjectBreakdown();
                        }

                        $count++;
                    }

                    //Generate Temp File Info
                    $xmlGen = new sfBuildspaceFileInfoXML('temp_file_info', $extractDir);

                    $xmlGen->process($extractDir, $extractedFiles, true);

                    $fileInfo['uploadPath'] = $extractDir;
                    $fileInfo['filename']   = $xmlGen->filename;
                    $fileInfo['extension']  = $xmlGen->extension;
                }

                $success = true;
            }
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'errorMsg'         => $errorMsg,
            'success'          => $success,
            'tempFileInfo'     => $fileInfo,
            'projectInfo'      => $projectInformation,
            'projectBreakdown' => $projectBreakdown
        ));
    }

    public function executeImportTenderProject(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $filename   = $request->getParameter('filename');
        $extension  = $request->getParameter('extension');
        $uploadPath = $request->getParameter('uploadPath');
        $uniqueId   = $request->getParameter('uid');

        $logDir = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'importTenderLog';
        $logFilename = $uniqueId."-import_tender.yaml";

        $fileExists = file_exists($logDir.DIRECTORY_SEPARATOR.$logFilename);
        if($fileExists)
        {
            unlink($logDir.DIRECTORY_SEPARATOR.$logFilename);
        }

        $proc = new BackgroundProcess("exec php ".sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR."symfony bgprocess:import_tender_project ".$filename." ".$extension." '".$uploadPath."' ".$this->getUser()->getGuardUser()->id." 2>&1 | tee ".sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR."import_tender_project-".$uniqueId.".log");
        $proc->run();

        return $this->renderJson([
            'running' => true
        ]);
    }

    public function executeGetImportTenderProjectProgress(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $exists = DoctrineQuery::create()->select('m.project_structure_id')
        ->from('ProjectMainInformation m')
        ->where('m.unique_id = ?', $request->getParameter('uid'))
        ->count();

        return $this->renderJson([
            'exists' => $exists
        ]);
    }

    public function executeGetImportTenderBillProgress(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $project = ProjectStructureTable::getInstance()->find((int)$request->getParameter('id'));

        $totalBills = 0;
        $totalImportedBills = 0;

        if(!$project)
        {
            return $this->renderJson([
                'exists'               => false,
                'total_bills'          => $totalBills,
                'total_imported_bills' => $totalImportedBills
            ]);
        }

        $logDir   = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'importTenderLog';
        $filename = trim($project->MainInformation->unique_id)."-import_tender.yaml";
        $exists   = file_exists($logDir.DIRECTORY_SEPARATOR.$filename);

        if($exists && $project)
        {
            $values             = sfYaml::load($logDir.DIRECTORY_SEPARATOR.$filename);
            $user               = sfGuardUserTable::getInstance()->find((int)$values['executed_by']);
            $totalBills         = (int)$values['total_bills'];
            $totalImportedBills = (int)$values['total_imported_bills'];
        }

        return $this->renderJson([
            'exists'               => $exists,
            'total_bills'          => $totalBills,
            'total_imported_bills' => $totalImportedBills
        ]);
    }

    public function executeGetStateByCountry(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $subRegions = DoctrineQuery::create()->select('c.id, c.name')
            ->from('Subregions c')
            ->where('c.region_id = ?', $request->getParameter('regionId'))
            ->fetchArray();

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $subRegions
        ));
    }

    public function executeGetCurrencyValueByCountry(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $region = DoctrineQuery::create()->select('r.id, r.country, r.currency_code, c.id, c.currency_code')
            ->from('Regions r, Currency c')
            ->where('r.id = ?', $request->getParameter('regionId'))
            ->andWhere('c.currency_code = r.currency_code')
            ->setHydrationMode(Doctrine_Core::HYDRATE_SCALAR)
            ->fetchOne();

        return $this->renderJson(array(
            'id'            => ( $region['c_id'] ) ? $region['c_id'] : null,
            'currency_code' => ( $region['c_currency_code'] ) ? $region['c_currency_code'] : null,
            'currency_name' => ( $region['c_currency_code'] ) ? $region['c_currency_code'] : null
        ));
    }

    public function executeGetCurrency(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $currencies = DoctrineQuery::create()->select('c.id, c.currency_code AS name')
            ->from('Currency c')
            ->fetchArray();

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $currencies
        ));
    }

    public function executeGetTenderingProjectGroupLists(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $form = new TenderingProjectGroupsAssignmentForm($projectStructure);

        $groupWithProjects = $projectStructure->TenderingGroups;

        $projectGroups = array();

        if( count($groupWithProjects) > 0 )
        {
            foreach($groupWithProjects as $groupWithProject)
            {
                $projectGroups[ $groupWithProject->id ] = $groupWithProject->id;
            }
        }

        $groups = Doctrine_Query::create()
            ->from('sfGuardGroup u')
            ->orderBy('u.id')
            ->execute();

        // get available user list
        $data = array();

        foreach($groups as $group)
        {
            $data[] = array(
                'id'          => $group->id,
                'name'        => $group->name,
                'updated_at'  => date('d/m/Y H:i', strtotime($group->updated_at)),
                '_csrf_token' => $form->getCSRFToken()
            );
        }

        return $this->renderJson(array(
            'groups' => array( $projectGroups ),
            'data'   => array(
                'identifier' => 'id',
                'items'      => $data
            )
        ));
    }

    public function executeUpdateTenderingProjectGroupInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $form = new TenderingProjectGroupsAssignmentForm($projectStructure);

        if( $this->isFormValid($request, $form) )
        {
            $group   = $form->save();
            $id      = $group->getId();
            $success = true;
            $errors  = array();
        }
        else
        {
            $id      = $request->getPostParameter('id');
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeCheckPublishRequirement(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and 
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $hasEProjectProject = ($project->MainInformation->EProjectProject) ? true : false;

        if($project->MainInformation->EProjectProject && ($project->MainInformation->EProjectProject->status_id == EProjectProject::STATUS_TYPE_POST_CONTRACT || $project->MainInformation->EProjectProject->status_id == EProjectProject::STATUS_TYPE_COMPLETED) && $project->MainInformation->EProjectProject->getLatestTender())
        {
            $EProject = $project->MainInformation->EProjectProject;
            $latestTender = $EProject->getLatestTender();
            $selectContractor = null;

            $selectContractor = $latestTender->getSelectedContractor();

            $bsAwardedCompany = null;

            if($project->TenderSetting->awarded_company_id)
            {
                $bsAwardedCompany = Doctrine_Core::getTable('Company')->find($project->TenderSetting->awarded_company_id);
            }

            if($selectContractor && is_null($bsAwardedCompany) || ($selectContractor && !is_null($bsAwardedCompany) && $bsAwardedCompany->reference_id != $selectContractor->Company->reference_id))
            {
                $company = $selectContractor->Company->toArray();

                return $this->renderJson(array(
                    'id'              => $project->id,
                    'title'           => $project->title,
                    'company_name'    => $company['name'],
                    'company_address' => $company['address'],
                    'can_publish'     => false
                ));
            }
        }

        try
        {
            switch($project->MainInformation->tender_type_id)
            {
                case ProjectMainInformation::TENDER_TYPE_TENDERED:
                    $checkResult = $project->checkConsultantPostContractRequirement();
                    break;
                case ProjectMainInformation::TENDER_TYPE_PARTICIPATED:
                    $checkResult = $project->checkContractorPostContractRequirement();
                    break;
                default:
                    throw new Exception(ProjectMainInformation::ERROR_MSG_UNKNOWN_TENDER_TYPE);
            }

            $success = true;
        }
        catch(Exception $e)
        {
            $success = false;
        }

        $list = array(
            'identifier' => 'count',
            'items'      => $checkResult['items']
        );

        if( ! count($list['items']) )
        {
            $list['items'][] = array( 'count' => 1, 'notice' => TenderSetting::MSG_PUBLISH_ENABLE, 'success' => true );
        }
        
        return $this->renderJson(array(
            'id'                 => $project->id,
            'title'              => $project->title,
            'success'            => $success,
            'requirementSuccess' => $checkResult['success'],
            'items'              => $list,
            'has_eproject'       => $hasEProjectProject,
            'can_publish'        => true
        ));
    }

    public function executeSendNewPostContractFormForApproval(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() && $project = Doctrine_Core::getTable('ProjectStructure')->find($projectId = $request->getParameter('id')));

        if( is_null($project->MainInformation->eproject_origin_id) ) $this->forward('tendering', 'publishToPostContract');

        $withoutNotListedItem  = ( $request->getParameter('withoutNotListedItem') == 'true' );
        $usersAssignedManually = ( $request->getParameter('usersAssignedManually') == 'true' );
        $useOriginalRate       = $request->getParameter('use_original_rate') == 'true';
        $rateType              = $useOriginalRate ? PublishToPostContractOption::RATE_TYPE_ESTIMATE : PublishToPostContractOption::RATE_TYPE_CONTRACTOR;

        // Save publish to post contract options.
        PublishToPostContractOptionTable::findOrCreate($project->id, ( ! $withoutNotListedItem ), $rateType, $usersAssignedManually);

        ContractManagementVerifierTable::initialiseVerifierList($project, PostContractClaim::TYPE_LETTER_OF_AWARD);
        ContractManagementVerifierTable::sendNotifications($project, PostContractClaim::TYPE_LETTER_OF_AWARD);

        return $this->renderJson(array(
            'success'                 => true,
            'tendering_module_locked' => ProjectStructureTable::tenderingModuleLocked($projectId),
        ));
    }

    public function executePublishToPostContract(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $actingUser = $request->hasParameter('user_id') ? Doctrine_Core::getTable('sfGuardUser')->find($request->getParameter('user_id')) : $this->getUser()->getGuardUser();

        $errorMsg = false;

        $projectMainInformation = $project->MainInformation;

        $withoutNotListedItem = ( $request->getParameter('withoutNotListedItem') == 'true');
        $useOriginalRate      = ( $request->getParameter('use_original_rate') == 'true');

        $usersAssignedManually = ( $request->getParameter('usersAssignedManually') == 'true' );

        try
        {
            $postContract                       = new PostContract();
            $postContract->project_structure_id = $project->id;
            $postContract->published_type       = PostContract::PUBLISHED_TYPE_NEW;
            $postContract->published_at         = 'NOW()';

            if( $useOriginalRate )
            {
                $postContract->selected_type_rate = PostContract::RATE_TYPE_ORIGINAL;
            }
            elseif( !$useOriginalRate && $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED )
            {
                $postContract->selected_type_rate = PostContract::RATE_TYPE_RATIONALIZED;
            }
            else
            {
                $postContract->selected_type_rate = PostContract::RATE_TYPE_CONTRACTOR;
            }

            $postContract->save();

            $postContract->cloneBillItemRates($withoutNotListedItem);

            CostDataItemTable::updateProjectCostDataItemValues([$project->id]);
            CostDataPrimeCostRateTable::updateProjectCostDataItemValues([$project->id]);

            $projectMainInformation->status = ( $projectMainInformation->status == ProjectMainInformation::STATUS_IMPORT_SUB_PACKAGE ) ? ProjectMainInformation::STATUS_POSTCONTRACT_SUB_PACKAGE : ProjectMainInformation::STATUS_POSTCONTRACT;
            $projectMainInformation->save();

            $claimRevision                            = new PostContractClaimRevision();
            $claimRevision->post_contract_id          = $postContract->id;
            $claimRevision->current_selected_revision = true;
            $claimRevision->version                   = PostContractClaimRevision::ORIGINAL_BILL_VERSION;
            $claimRevision->save();

            ProjectRevisionTable::updateProjectRevisionStatusToLocked($project);

            if( (! $usersAssignedManually) && $project->MainInformation->eproject_origin_id )
            {
                ProjectUserPermissionTable::automaticallyAssignUsers($project, ProjectUserPermission::STATUS_POST_CONTRACT, $actingUser);
            }
            else
            {
                $assignedUsers = ProjectUserPermissionTable::getAssignedUserIdsByProjectAndStatus($project, ProjectUserPermission::STATUS_POST_CONTRACT);
                //if user permission for post contract is empty then we use user permission from tendering as user permission for post contract
                if( empty( $assignedUsers ) )
                {
                    ProjectUserPermissionTable::copyExistingUsersPermissionByStatus($project, ProjectUserPermission::STATUS_TENDERING, ProjectUserPermission::STATUS_POST_CONTRACT, $actingUser);
                }
            }

            $assignedProjectManagementUsers = ProjectUserPermissionTable::getAssignedUserIdsByProjectAndStatus($project, ProjectUserPermission::STATUS_PROJECT_MANAGEMENT);

            if( empty( $assignedProjectManagementUsers ) )
            {
                ProjectUserPermissionTable::copyExistingUsersPermissionByStatus($project, ProjectUserPermission::STATUS_POST_CONTRACT, ProjectUserPermission::STATUS_PROJECT_MANAGEMENT, $actingUser);
            }

            if( ! is_null($project->MainInformation->eproject_origin_id) ) ProjectStructureTable::pushEProjectToPostContract($project, $actingUser);

            // create default data for Project Code Settings
            ProjectCodeSettings::createEntry($project);

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'id'                      => $project->id,
            'title'                   => $project->title,
            'errorMsg'                => $errorMsg,
            'status'                  => ProjectMainInformation::getProjectStatusById($projectMainInformation->status),
            'status_id'               => $projectMainInformation->status,
            'tendering_module_locked' => ProjectStructureTable::tenderingModuleLocked($project->id),
            'success'                 => $success,
        ));
    }

    public function executeGetStandardPhrasesForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId'))
        );

        $standardPhrases = $structure->BillLayoutSetting->BillPhrase;
        $form            = new TenderingStandardPhrasesForm($standardPhrases);

        $data = array(
            'tendering_standard_phrases[summary_page_one]'   => $form->getObject()->summary_page_one,
            'tendering_standard_phrases[summary_page_two]'   => $form->getObject()->summary_page_two,
            'tendering_standard_phrases[summary_page_three]' => $form->getObject()->summary_page_three,
            'tendering_standard_phrases[summary_page_four]'  => $form->getObject()->summary_page_four,
            'tendering_standard_phrases[summary_page_five]'  => $form->getObject()->summary_page_five,
            'tendering_standard_phrases[summary_page_six]'   => $form->getObject()->summary_page_six,
            'tendering_standard_phrases[summary_page_seven]' => $form->getObject()->summary_page_seven,
            'tendering_standard_phrases[summary_page_eight]' => $form->getObject()->summary_page_eight,
            'tendering_standard_phrases[summary_page_nine]'  => $form->getObject()->summary_page_nine,
            'tendering_standard_phrases[_csrf_token]'        => $form->getCSRFToken(),
        );

        return $this->renderJson($data);
    }

    public function executeUpdateStandardPhrases(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('billId'))
        );

        $standardPhrases = $structure->BillLayoutSetting->BillPhrase;
        $form            = new TenderingStandardPhrasesForm($standardPhrases);

        if( $this->isFormValid($request, $form) )
        {
            $setting = $form->save();
            $id      = $setting->getId();
            $success = true;
            $errors  = array();
        }
        else
        {
            $id      = $request->getPostParameter('billId');
            $errors  = $form->getErrors();
            $success = false;
        }

        $data = array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors );

        return $this->renderJson($data);
    }

    public function executeGetProjectRateLogCount(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $pdo = BillItemTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT r.id, r.revision, COALESCE(MAX(l.changes_count), 0) AS nol
            FROM " . ProjectRevisionTable::getInstance()->getTableName() . " r
            LEFT JOIN " . BillItemRateLogTable::getInstance()->getTableName() . " l ON l.project_revision_id = r.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON r.project_structure_id = b.root_id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.project_structure_id = b.id
            LEFT JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id AND l.bill_item_id = i.id
            WHERE r.project_structure_id = " . $project->id . "
            AND r.deleted_at IS NULL AND b. deleted_at IS NULL AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            GROUP BY r.id
            ORDER BY r.version");

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if( empty( $data ) )
        {
            $data[] = array(
                'id'       => Constants::GRID_LAST_ROW,
                'revision' => '-',
                'nol'      => 0
            );
        }

        return $this->renderJson($data);
    }

    public function executeGetLogProjectBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $projectRevision = ProjectRevisionTable::getInstance()->find($request->getParameter('prid'));

        if( ! $projectRevision )
        {
            return $this->renderJson(array(
                'identifier' => 'id',
                'items'      => array( array(
                    'id'                             => Constants::GRID_LAST_ROW,
                    'title'                          => "",
                    'type'                           => 1,
                    'level'                          => 0,
                    'count'                          => null,
                    'overall_total_after_markup'     => 0,
                    'rev_overall_total_after_markup' => 0
                ) )
            ));
        }

        $records = DoctrineQuery::create()->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', ProjectStructure::TYPE_BILL)
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        $logRates = BillItemRateLogTable::getBillTotalAmountByProjectAndRevision($project, $projectRevision);

        $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($project);

        foreach($records as $key => $record)
        {
            if( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[ $key ]['BillType']) )
            {
                $records[ $key ]['bill_type']   = $record['BillType']['type'];
                $records[ $key ]['bill_status'] = $record['BillType']['status'];

                $records[ $key ]['overall_total_after_markup'] = (array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;

                if( array_key_exists($record['id'], $logRates['bills']) )
                {
                    $records[ $key ]['rev_overall_total_after_markup'] = $logRates['bills'][ $record['id'] ];
                }
                else
                {
                    $records[ $key ]['rev_overall_total_after_markup'] = 0;
                }
            }
            else if( $record['type'] == ProjectStructure::TYPE_ROOT )
            {
                $records[ $key ]['overall_total_after_markup']     = ProjectStructureTable::getOverallTotalForProject($record['id']);
                $records[ $key ]['rev_overall_total_after_markup'] = $logRates['project_total'];
            }

            unset( $records[ $key ]['BillLayoutSetting'] );
            unset( $records[ $key ]['BillType'] );
            unset( $records[ $key ]['BillColumnSettings'] );
        }

        array_push($records, array(
            'id'                             => Constants::GRID_LAST_ROW,
            'title'                          => "",
            'type'                           => 1,
            'level'                          => 0,
            'count'                          => null,
            'overall_total_after_markup'     => 0,
            'rev_overall_total_after_markup' => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetLogElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')) and
            $projectRevision = ProjectRevisionTable::getInstance()->find($request->getParameter('prid'))
        );

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $elementGrandTotals = ProjectStructureTable::getElementGrandTotalByBillIdGroupByElement($bill->id);
        $logsGrandTotal     = BillItemRateLogTable::getElementGrandTotalByBillAndRevision($bill, $projectRevision);

        foreach($elements as $key => $element)
        {
            if( array_key_exists($element['id'], $elementGrandTotals) )
            {
                $elements[ $key ]['overall_total_after_markup'] = $elementGrandTotals[ $element['id'] ][0]['grand_total_after_markup'];
            }
            else
            {
                $elements[ $key ]['overall_total_after_markup'] = 0;
            }

            if( array_key_exists($element['id'], $logsGrandTotal['elements']) )
            {
                $elements[ $key ]['rev_overall_total_after_markup'] = $logsGrandTotal['elements'][ $element['id'] ];
            }
            else
            {
                $elements[ $key ]['rev_overall_total_after_markup'] = 0;
            }
        }

        array_push($elements, array(
            'id'                             => Constants::GRID_LAST_ROW,
            'description'                    => '',
            'overall_total_after_markup'     => 0,
            'rev_overall_total_after_markup' => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetLogItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('eid')) and
            $projectRevision = ProjectRevisionTable::getInstance()->find($request->getParameter('prid'))
        );

        $bill                    = $element->ProjectStructure;
        $pdo                     = $element->getTable()->getConnection()->getDbh();
        $pageNoPrefix            = $bill->BillLayoutSetting->page_no_prefix;
        $elementMarkupPercentage = 0;

        /*
         * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
         */
        if( $bill->BillMarkupSetting->element_markup_enabled )
        {
            $stmt = $pdo->prepare("SELECT COALESCE(c.final_value, 0) as value FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
                JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
                WHERE e.id = " . $element->id . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
                AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

            $stmt->execute();

            $elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
            $elementMarkupPercentage = $elementMarkupResult ? (float)$elementMarkupResult['value'] : 0;
        }

        $roundingType = $bill->BillMarkupSetting->rounding_type;

        $markupSettingsInfo = array(
            'bill_markup_enabled'       => $bill->BillMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage'    => $bill->BillMarkupSetting->bill_markup_percentage,
            'element_markup_enabled'    => $bill->BillMarkupSetting->element_markup_enabled,
            'element_markup_percentage' => $elementMarkupPercentage,
            'item_markup_enabled'       => $bill->BillMarkupSetting->item_markup_enabled,
            'rounding_type'             => $roundingType
        );

        list(
            $billItems, $formulatedColumns, $quantityPerUnitByColumns,
            $billItemTypeReferences, $billItemTypeRefFormulatedColumns
            ) = BillItemTable::getDataStructureForBillItemList($element, $bill);

        $billItemLogRates      = BillItemRateLogTable::getBillItemRatesByProjectAndRevision($bill->getRoot(), $projectRevision);
        $billItemLogGrandTotal = BillItemRateLogTable::getBillItemGrandTotalByProjectAndRevision($bill->getRoot(), $projectRevision);

        foreach($billItems as $key => $billItem)
        {
            $rate                  = 0;
            $rateAfterMarkup       = 0;
            $itemMarkupPercentage  = 0;
            $grandTotalAfterMarkup = 0;

            $billItems[ $key ]['bill_ref']                    = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
            $billItems[ $key ]['type']                        = (string)$billItem['type'];
            $billItems[ $key ]['uom_id']                      = $billItem['uom_id'] > 0 ? (string)$billItem['uom_id'] : '-1';
            $billItems[ $key ]['project_revision_deleted_at'] = is_null($billItem['project_revision_deleted_at']) ? false : $billItem['project_revision_deleted_at'];

            if( array_key_exists($billItem['id'], $formulatedColumns) )
            {
                $itemFormulatedColumns = $formulatedColumns[ $billItem['id'] ];

                foreach($itemFormulatedColumns as $formulatedColumn)
                {
                    if( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                    {
                        $rate = $formulatedColumn['final_value'];
                    }

                    if( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
                    {
                        $itemMarkupPercentage = $formulatedColumn['final_value'];
                    }
                }

                unset( $formulatedColumns[ $billItem['id'] ], $itemFormulatedColumns );

                $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
            }

            foreach($bill->BillColumnSettings as $column)
            {
                $quantityPerUnit = 0;

                if( array_key_exists($column->id, $quantityPerUnitByColumns) && array_key_exists($billItem['id'], $quantityPerUnitByColumns[ $column->id ]) )
                {
                    $quantityPerUnit = $quantityPerUnitByColumns[ $column->id ][ $billItem['id'] ][0];
                    unset( $quantityPerUnitByColumns[ $column->id ][ $billItem['id'] ] );
                }

                if( array_key_exists($column->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[ $column->id ]) )
                {
                    $totalPerUnit = number_format($rateAfterMarkup * $quantityPerUnit, 2, '.', '');
                    $total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                    unset( $billItemTypeReferences[ $column->id ][ $billItem['id'] ] );
                }
                else
                {
                    $total = 0;
                }

                $grandTotalAfterMarkup += $total;
            }

            $billItems[ $key ]['rate_after_markup']        = $rateAfterMarkup;
            $billItems[ $key ]['grand_total_after_markup'] = $grandTotalAfterMarkup;

            if( array_key_exists($billItem['id'], $billItemLogRates) )
            {
                $billItems[ $key ]['rev_rate_after_markup'] = $billItemLogRates[ $billItem['id'] ];
                unset( $billItemLogRates[ $billItem['id'] ] );
            }
            else
            {
                $billItems[ $key ]['rev_rate_after_markup'] = 0;
            }

            if( array_key_exists($billItem['id'], $billItemLogGrandTotal) )
            {
                $billItems[ $key ]['rev_grand_total_after_markup'] = $billItemLogGrandTotal[ $billItem['id'] ];
                unset( $billItemLogGrandTotal[ $billItem['id'] ] );
            }
            else
            {
                $billItems[ $key ]['rev_grand_total_after_markup'] = 0;
            }
        }

        $defaultLastRow = array(
            'id'                           => Constants::GRID_LAST_ROW,
            'bill_ref'                     => '',
            'description'                  => '',
            'type'                         => (string)ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                       => '-1',
            'uom_symbol'                   => '',
            'level'                        => 0,
            'rate_after_markup'            => 0,
            'grand_total_after_markup'     => 0,
            'rev_rate_after_markup'        => 0,
            'rev_grand_total_after_markup' => 0
        );

        array_push($billItems, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $billItems
        ));
    }

    public function executeSubmitNewContractForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('pid'))) and
            $project->node->isRoot()
        );

        $estimationRate = ($request->getParameter('use_original_rate')=='true');
        $withNotListedItems = ($request->getParameter('without_not_listed_item')=='false');

        $form = new NewPostContractFormInformationForm($project->NewPostContractFormInformation);

        $form->setParameters($project, $estimationRate, $withNotListedItems);

        if ( $this->isFormValid($request, $form) )
        {
            $item = $form->save();

            $errors  = null;
            $success = true;

            $subPackageWork1 = $item->getSubPackageWorkByType(SubPackageWorks::TYPE_1, Doctrine_Core::HYDRATE_ARRAY);
            $subPackageWork2 = $item->getSubPackageWorkByType(SubPackageWorks::TYPE_2, Doctrine_Core::HYDRATE_ARRAY);

            $includeVO             = ( $request->getParameter('includeVO') == 't' );
            $includeMaterialOnSite = ( $request->getParameter('includeMaterialOnSite') == 't' );

            $includedTypes = [];

            if( $includeVO ) $includedTypes[] = PostContractClaim::TYPE_VARIATION_ORDER;
            if( $includeMaterialOnSite ) $includedTypes[] = PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE;

            LetterOfAwardRetentionSumModulesTable::sync($item->id, $includedTypes);

            $values  = array(
                'new_post_contract_form_information[type]'                         => $item->type,
                'new_post_contract_form_information[pre_defined_location_code_id]' => $item->pre_defined_location_code_id,
                'new_post_contract_form_information[works_1]'                      => ( $subPackageWork1 ) ? $subPackageWork1['id'] : null,
                'new_post_contract_form_information[works_2]'                      => ( $subPackageWork2 ) ? $subPackageWork2['id'] : null,
                'new_post_contract_form_information[form_number]'                  => $item->form_number,
                'new_post_contract_form_information[contract_period_from]'         => $item->contract_period_from ? date('Y-m-d', strtotime($item->contract_period_from)) : null,
                'new_post_contract_form_information[contract_period_to]'           => $item->contract_period_to ? date('Y-m-d', strtotime($item->contract_period_to)) : null,
                'new_post_contract_form_information[awarded_date]'                 => $item->awarded_date ? date('Y-m-d', strtotime($item->awarded_date)) : null,
                'new_post_contract_form_information[creditor_code]'                => $item->creditor_code,
                'new_post_contract_form_information[retention]'                    => $item->retention,
                'includeVO'                                                        => $includeVO,
                'includeMaterialOnSite'                                            => $includeMaterialOnSite,
                'new_post_contract_form_information[remarks]'                      => $item->remarks,
                'new_post_contract_form_information[_csrf_token]'                  => $form->getCSRFToken()
            );

            if( is_null($project->MainInformation->eproject_origin_id) )
            {
                $this->request->setParameter('id', $project->id);
                $this->forward('tendering', 'publishToPostContract');
            }
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
            $values  = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'values' => $values ));
    }

    public function executeGetProjectLabourRateRecords(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() && ($project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id'))));

        $tradeId = $request->getParameter('trade_id');

        $projectLabourRates = EProjectProjectLabourRateTable::getProjectLabourRateRecords($project,$tradeId);

        return $this->renderJson(array( 'projectLabourRates' =>$projectLabourRates ));
    }

    public function executeCheckIfCanSubmitNewPostContractForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $canSubmit    = true;
        $errorMessage = null;

        // Check for publish requirements.
        switch($project->MainInformation->tender_type_id)
        {
            case ProjectMainInformation::TENDER_TYPE_TENDERED:

                $checkResult = $project->checkConsultantPostContractRequirement();

                break;
            case ProjectMainInformation::TENDER_TYPE_PARTICIPATED:

                $checkResult = $project->checkContractorPostContractRequirement();

                break;
            default:
                throw new Exception(ProjectMainInformation::ERROR_MSG_UNKNOWN_TENDER_TYPE);
        }

        foreach($checkResult['items'] as $item)
        {
            if( ! $item['success'] )
            {
                $canSubmit    = false;
                $errorMessage = "Ensure: {$item['notice']}";
                break;
            }
        }

        if( $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_TENDERED )
        {
            // Check for Letter of Award verifiers.
            $verifierList = ContractManagementVerifierTable::getVerifierList($project, PostContractClaim::TYPE_LETTER_OF_AWARD);

            if( ( count($verifierList) < 1 ) )
            {
                $canSubmit    = false;
                $errorMessage = "Verifiers need to be assigned for approval to publish this project to Post Contract.";
            }
        }

        return $this->renderJson(array(
            'canSubmit'    => $canSubmit,
            'errorMessage' => $errorMessage,
        ));
    }

    public function executeNewPostContractFormInformationForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find(intval($request->getParameter('project_id'))) and
            $project->node->isRoot()
        );

        $form = new NewPostContractFormInformationForm($project->NewPostContractFormInformation);

        $subPackageWork1 = $form->getObject()->getSubPackageWorkByType(SubPackageWorks::TYPE_1, Doctrine_Core::HYDRATE_ARRAY);
        $subPackageWork2 = $form->getObject()->getSubPackageWorkByType(SubPackageWorks::TYPE_2, Doctrine_Core::HYDRATE_ARRAY);

        $EProjectPostContractStatus = false;

        $contractPeriodFrom = null;
        $contractPeriodTo = null;
        $selectedTrade = null;
        $labourRates = EProjectProjectLabourRateTable::getProjectLabourRates($project);

        if($project->MainInformation->EProjectProject && ($project->MainInformation->EProjectProject->status_id == EProjectProject::STATUS_TYPE_POST_CONTRACT || $project->MainInformation->EProjectProject->status_id == EProjectProject::STATUS_TYPE_COMPLETED) && $project->MainInformation->EProjectProject->getLatestTender())
        {
            $EProject = $project->MainInformation->EProjectProject;

            $contract = $EProject->getPostContractInfo();

            if($contract)
            {
                $contractPeriodFrom = date("d-m-Y", strtotime($contract->commencement_date));
                $contractPeriodTo = date("d-m-Y", strtotime($contract->completion_date));
                $selectedTrade = $contract->PreDefinedLocationCode->name;
            }

            $EProjectPostContractStatus = true;
        }

        $estimationRate = ($request->getParameter('use_original_rate')=='true');
        $withNotListedItems = ($request->getParameter('without_not_listed_item')=='false');

        $contractSum = ProjectStructureTable::getContractSumByProjectId($project->id, $estimationRate, $withNotListedItems);
        $currencyCode = ( $project->MainInformation->currency_id ) ? $project->MainInformation->Currency->currency_code : "";

        return $this->renderJson(array(
            'formValues' => array(
                'new_post_contract_form_information[type]'                         => $formType = ( $form->getObject()->type ?? NewPostContractFormInformation::TYPE_1 ),
                'new_post_contract_form_information[pre_defined_location_code_id]' => $form->getObject()->pre_defined_location_code_id,
                'new_post_contract_form_information[works_1]'                      => ( $subPackageWork1 ) ? $subPackageWork1['id'] : null,
                'new_post_contract_form_information[works_2]'                      => ( $subPackageWork2 ) ? $subPackageWork2['id'] : null,
                'new_post_contract_form_information[form_number]'                  => $formNumber = ( $form->getObject()->form_number ?? NewPostContractFormInformationTable::getNextFormNumber($project, NewPostContractFormInformation::TYPE_1) ),
                'new_post_contract_form_information[reference]'                    => $form->getObject()->reference ?? NewPostContractFormInformation::generateLetterOfAwardCode($project, $formType, $formNumber),
                'new_post_contract_form_information[contract_period_from]'         => $form->getObject()->contract_period_from ? date('Y-m-d', strtotime($form->getObject()->contract_period_from)) : null,
                'new_post_contract_form_information[contract_period_to]'           => $form->getObject()->contract_period_to ? date('Y-m-d', strtotime($form->getObject()->contract_period_to)) : null,
                'new_post_contract_form_information[awarded_date]'                 => $form->getObject()->awarded_date ? date('Y-m-d', strtotime($form->getObject()->awarded_date)) : null,
                'new_post_contract_form_information[creditor_code]'                => $form->getObject()->creditor_code,
                'new_post_contract_form_information[retention]'                    => $form->getObject()->retention,
                'new_post_contract_form_information[max_retention_sum]'            => $form->getObject()->max_retention_sum,
                'new_post_contract_form_information[remarks]'                      => $form->getObject()->remarks,
                'includeVO'                                                        => $form->getObject()->exists() ? LetterOfAwardRetentionSumModulesTable::isIncluded($form->getObject()->id, PostContractClaim::TYPE_VARIATION_ORDER) : true,
                'includeMaterialOnSite'                                            => $form->getObject()->exists() ? LetterOfAwardRetentionSumModulesTable::isIncluded($form->getObject()->id, PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE) : false,
                'new_post_contract_form_information[contract_sum]'                 => $currencyCode . " " . number_format($contractSum, 2, '.', ','),
                'new_post_contract_form_information[_csrf_token]'                  => $form->getCSRFToken()
            ),
            'eproject_in_post_contract'       => $EProjectPostContractStatus,
            'commencement_date'               => $contractPeriodFrom,
            'completion_date'                 => $contractPeriodTo,
            'selected_trade'                  => $selectedTrade,
            'labour_rates'                    => $labourRates,
            'eTenderWaiverOption'             => $form->getObject()->e_tender_waiver_option_type,
            'eAuctionWaiverOption'            => $form->getObject()->e_auction_waiver_option_type,
            'eTenderWaiverUserDefinedOption'  => ($form->getObject()->e_tender_waiver_option_type == NewPostContractFormInformation::E_TENDER_WAIVER_OPTION_OTHERS) ?  WaiverUserDefinedOption::find($project, $form->getObject()->e_tender_waiver_option_type)->description : null,
            'eAuctionWaiverUserDefinedOption' => ($form->getObject()->e_auction_waiver_option_type == NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_OTHERS) ?  WaiverUserDefinedOption::find($project, $form->getObject()->e_auction_waiver_option_type)->description : null,
        ));
    }

    public function executeGetNewPostContractFormType(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => array(
                array('id' => NewPostContractFormInformation::TYPE_1, 'name' => NewPostContractFormInformation::TYPE_1_TEXT),
                array('id' => NewPostContractFormInformation::TYPE_2, 'name' => NewPostContractFormInformation::TYPE_2_TEXT),
                array('id' => NewPostContractFormInformation::TYPE_3, 'name' => NewPostContractFormInformation::TYPE_3_TEXT),
            )
        ));
    }

    public function executeGetWaiverOptionType(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $type = $request->getParameter('type');
        $itemsArray = [];

        switch($type)
        {
            case NewPostContractFormInformation::WAIVER_OPTION_TYPE_E_TENDER:
                array_push($itemsArray, ['id' => NewPostContractFormInformation::E_TENDER_WAIVER_OPTION_SITE_URGENCY, 'name' => NewPostContractFormInformation::E_TENDER_WAIVER_OPTION_SITE_URGENCY_TEXT]);
                array_push($itemsArray, ['id' => NewPostContractFormInformation::E_TENDER_WAIVER_OPTION_INTER_COMPANY, 'name' => NewPostContractFormInformation::E_TENDER_WAIVER_OPTION_INTER_COMPANY_TEXT]);
                array_push($itemsArray, ['id' => NewPostContractFormInformation::E_TENDER_WAIVER_OPTION_OTHERS, 'name' => NewPostContractFormInformation::E_TENDER_WAIVER_OPTION_OTHERS_TEXT]);

                break;
            case NewPostContractFormInformation::WAIVER_OPTION_TYPE_E_AUCTION:
                array_push($itemsArray, ['id' => NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_SITE_URGENCY, 'name' => NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_SITE_URGENCY_TEXT]);
                array_push($itemsArray, ['id' => NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_INTER_COMPANY, 'name' => NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_INTER_COMPANY_TEXT]);
                array_push($itemsArray, ['id' => NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_OTHERS, 'name' => NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_OTHERS_TEXT]);

                break;
        }

        return $this->renderJson([
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $itemsArray,
        ]);
    }

    public function executeGetSelectedContractor(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $company = $project->getSelectedContractor();

        $tenderAlternative = null;

        if(!empty($project->getTenderAlternatives(true)))
        {
            $tenderAlternative = $project->getAwardedTenderAlternative(true);
        }

        
        return $this->renderJson([
            'id'                       => $company ? $company->id : -1,
            'name'                     => $company ? $company->name : null,
            'tender_alternative_id'    => $tenderAlternative ? $tenderAlternative['id'] : -1,
            'tender_alternative_title' => $tenderAlternative ? $tenderAlternative['title'] : null
        ]);
    }

    public function executeGetSubPackageWorksDropDownList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $companies = DoctrineQuery::create()
            ->select('w.id, w.name')
            ->from('SubPackageWorks w')
            ->where('w.type = ?', $request->getParameter('type'))
            ->orderBy('w.id')
            ->fetchArray();

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $companies
        ));
    }

    public function executeGetLocationTradesDropDownList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $trades = DoctrineQuery::create()
            ->select('c.id, c.name')
            ->from('PreDefinedLocationCode c')
            ->where('c.level = ?', PreDefinedLocationCode::TRADE_LEVEL)
            ->orderBy('c.id')
            ->fetchArray();

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $trades
        ));
    }

    public function executeGetLetterOfAwardInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $item = $project->NewPostContractFormInformation;

        $publishToPostContractOptions = PublishToPostContractOptionTable::findByProjectId($project->id);

        $contractSum = ProjectStructureTable::getContractSumByProjectId($project->id, ($publishToPostContractOptions->rate_type == PublishToPostContractOption::RATE_TYPE_ESTIMATE), $publishToPostContractOptions->with_not_listed_item);

        $currencyCode = ( $project->MainInformation->currency_id ) ? $project->MainInformation->Currency->currency_code : "";

        $awardedTenderAlternative = $project->getAwardedTenderAlternative();

        $updatedBy = !empty($item->updated_by) ? DoctrineQuery::create()->select('u.id, p.name')
        ->from('sfGuardUser u')
        ->leftJoin('u.Profile p')
        ->where('u.id = ?', $item->updated_by)
        ->andWhere('(u.deleted_at IS NULL OR u.deleted_at IS NOT NULL)')
        ->andWhere('(p.deleted_at IS NULL OR p.deleted_at IS NOT NULL)')
        ->fetchOne([], Doctrine_Core::HYDRATE_SCALAR) : null;

        return $this->renderJson(array(
            'id'                               => $item->id,
            'project_owner'                    => $project->MainInformation->getEProjectProject()->Subsidiary->name ?? Doctrine_Core::getTable('myCompanyProfile')->find(1)->name,
            'title'                            => $project->MainInformation->title,
            'sub_con'                          => $project->getSelectedContractor()->name ?? '-',
            'type'                             => NewPostContractFormInformation::getTypeText($item->type),
            'reference'                        => $item->reference,
            'trade'                            => Doctrine_Core::getTable('PreDefinedLocationCode')->find($item->pre_defined_location_code_id)->name,
            'contract_period_from'             => date('d-m-Y', strtotime($item->contract_period_from)),
            'contract_period_to'               => date('d-m-Y', strtotime($item->contract_period_to)),
            'awarded_date'                     => date('d-m-Y', strtotime($item->awarded_date)),
            'works'                            => NewPostContractFormInformationTable::getSubPackageWork($item->id, 1)['name'],
            'works_2'                          => NewPostContractFormInformationTable::getSubPackageWork($item->id, 2)['name'],
            'contract_sum'                     => $currencyCode . " " . number_format($contractSum, 2, '.', ','),
            'retention'                        => $item->retention,
            'max_retention_sum'                => $item->max_retention_sum,
            'remarks'                          => empty( $item->remarks ) ? '-' : $item->remarks,
            'creditor_code'                    => empty( $item->creditor_code ) ? '-' : $item->creditor_code,
            'project_labour_rates'             => EProjectProjectLabourRateTable::getProjectLabourRates($project),
            'submitted_by'                     => empty( $updatedBy ) ? '-' : $updatedBy['p_name'],
            'submitted_at'                     => date('d/m/Y g:i a', strtotime($item->updated_at)),
            'includeVO'                        => LetterOfAwardRetentionSumModulesTable::isIncluded($item->id, PostContractClaim::TYPE_VARIATION_ORDER),
            'includeMaterialOnSite'            => LetterOfAwardRetentionSumModulesTable::isIncluded($item->id, PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE),
            'eTenderWaiverOption'              => NewPostContractFormInformation::getWaiverTypeText($project->NewPostContractFormInformation->e_tender_waiver_option_type),
            'eAuctionWaiverOption'             => NewPostContractFormInformation::getWaiverTypeText($project->NewPostContractFormInformation->e_auction_waiver_option_type),
            'eTenderWaiverUserDefinedOption'   => ($project->NewPostContractFormInformation->e_tender_waiver_option_type == NewPostContractFormInformation::E_TENDER_WAIVER_OPTION_OTHERS) ?  WaiverUserDefinedOption::find($project, $project->NewPostContractFormInformation->e_tender_waiver_option_type)->description : null,
            'eAuctionWaiverUserDefinedOption'  => ($project->NewPostContractFormInformation->e_auction_waiver_option_type == NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_OTHERS) ?  WaiverUserDefinedOption::find($project, $project->NewPostContractFormInformation->e_auction_waiver_option_type)->description : null,
            'awarded_tender_alternative_id'    => ($awardedTenderAlternative) ? $awardedTenderAlternative->id : -1,
            'awarded_tender_alternative_title' => ($awardedTenderAlternative) ? $awardedTenderAlternative->title : ""
        ));
    }

    public function executeGetNewPostContractFormNumber(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() &&
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')));

        $formNumber = NewPostContractFormInformationTable::getNextFormNumber($project, $request->getParameter('form_type'));

        return $this->renderJson(array(
            'success'     => true,
            'form_number' => $formNumber,
        ));
    }

    public function executeGetLetterOfAwardCode(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() &&
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')));

        $formNumber = $request->getParameter('form_number');
        $formType   = $request->getParameter('form_type');

        $code = NewPostContractFormInformation::generateLetterOfAwardCode($project, $formType, $formNumber);

        return $this->renderJson(array(
            'success' => true,
            'code'    => $code,
        ));
    }

    public function executeGetProjectRevisionInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $latestProjectRevision = $project->getLatestProjectRevision();

        return $this->renderJson([
            'id' => $latestProjectRevision->id,
            'project_structure_id' => $latestProjectRevision->project_structure_id,
            'revision' => $latestProjectRevision->revision,
            'version' => $latestProjectRevision->version,
            'current_selected_revision' => (int)$latestProjectRevision->current_selected_revision,
            'locked_status' => (int)$latestProjectRevision->locked_status,
            'tender_origin_id' => $latestProjectRevision->tender_origin_id
        ]);
    }

    public function executeGenerateAddendumFileInTenderDocument(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $revision = Doctrine_Core::getTable('ProjectRevision')->find($request->getParameter('rid'))
        );

        $success = false;

        // will generate an export addendum's zip file and then send to eProject's module
        if( $revision->version > 0 and $revision->locked_status )
        {
            $userOriginId = $this->getUser()->getProfile()->eproject_user_id;
            $proc = new BackgroundProcess('exec php '.sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR.'symfony bgprocess:generate_addendum_file '.$project->id.' '.$revision->id.' '.$userOriginId.' 2>&1 | tee '.sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR.'generate_addendum_file-'.$project->id.'-'.$revision->id.'.log');
            $proc->run();

            $success = true;
        }

        return $this->renderJson([
            'success' => $success
        ]);
    }

    public function executeBillUpdate(sfWebRequest $request)
    {
        // $this->forward404Unless($request->isXmlHttpRequest());
        $billType = $request->getParameter('bill_type');

        if( $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) )
        {
            $parent      = $structure->node->getParent();
            $billSetting = $structure->BillSetting;
        }
        else
        {
            $parent      = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('parent_id'));
            $billSetting = new BillSetting();
        }

        $form = new BillSettingForm($billSetting, array( 'parent' => $parent, 'billType' => $billType['type'] ));

        if( $this->isFormValid($request, $form) )
        {
            try
            {
                $billSetting = $form->save();

                // clone the global bill printout setting if still new and is bill
                if( $form->isNew() )
                {
                    // get global default printing setting
                    $defaultPrintingSetting = BillLayoutSettingTable::getInstance()->find(1);
                    $defaultSetting         = $defaultPrintingSetting->toArray();

                    // get global default printing setting
                    $billPhraseSetting = $defaultPrintingSetting->getBillPhrase()->toArray();
                    $headSettings      = $defaultPrintingSetting->getBillHeadSettings()->toArray();

                    BillLayoutSettingTable::cloneExistingPrintingLayoutSettingsForBill($billSetting->ProjectStructure->id, $defaultSetting, $billPhraseSetting, $headSettings);

                    $billSetting->refresh();
                }

                $form = new BaseForm();

                $item = array(
                    'id'                  => $billSetting->ProjectStructure->id,
                    'billLayoutSettingId' => $billSetting->ProjectStructure->BillLayoutSetting->id,
                    'title'               => $billSetting->ProjectStructure->title,
                    'type'                => $billSetting->ProjectStructure->type,
                    'level'               => $billSetting->ProjectStructure->level,
                    'bill_type'           => $billSetting->ProjectStructure->BillType->type,
                    'bill_status'         => $billSetting->ProjectStructure->BillType->status,
                    '_csrf_token'         => $form->getCSRFToken()
                );

                $parentId = $billSetting->ProjectStructure->level > 1 ? $billSetting->ProjectStructure->node->getParent()->id : null;
                $errors   = null;
                $success  = true;
            }
            catch(Exception $e)
            {
                $errors   = $e->getMessage();
                $item     = array();
                $parentId = null;
                $success  = false;
                $title    = null;
            }
        }
        else
        {
            $errors   = $form->getErrors();
            $item     = array();
            $parentId = null;
            $success  = false;
            $title    = null;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'errors'    => $errors,
            'item'      => $item,
            'parent_id' => $parentId
        ));
    }
}
