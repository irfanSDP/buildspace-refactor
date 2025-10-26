<?php

/**
 * eproject_api actions.
 *
 * @package    buildspace
 * @subpackage eproject_api
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class eproject_apiActions extends BaseActions {

    public function executeUpdateClaimCertificate(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $request->isMethod('post') and
            $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find(intval($request->getParameter('ccid'))) and
            $eprojectUser = Doctrine_Core::getTable('EProjectUser')->find(intval($request->getParameter('uid'))) and
            $user = sfGuardUserTable::getInstance()->find($eprojectUser->getBuildSpaceUser()->user_id)
        );

        $errorMsg =  null;

        try
        {
            sfContext::getInstance()->getUser()->signIn($user);

            $claimCertificate->updated_by = $user->id;
            $claimCertificate->save();

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success = false;
        }
  
        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
        ));
    }

    public function executeDeleteProject(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $request->isMethod('post')
        );

        $projectMainInformation = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('eproject_project_id'));

        if( ! empty( $projectMainInformation ) )
        {
            $project = $projectMainInformation->ProjectStructure;
            try
            {
                $project->setAsDeleted();
                $success = true;
                $errorMsg = null;
            }
            catch(Exception $e)
            {
                $errorMsg = $e->getMessage();
                $success = false;
            }
        }
        else
        {
            // Project does not exist within BuildSpace (SAML)
            $success = true;
            $errorMsg = null;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeImportContractorRates(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $request->isMethod('post') and
            $projectMainInformation = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectIdAndStatus($request->getParameter('eproject_project_id'), ProjectMainInformation::STATUS_TENDERING) and
            $company = Doctrine_Core::getTable('Company')->findOneBy('reference_id', $request->getParameter('contractor_unique_id')) and
            is_readable($request->getParameter('rates_file'))
        );

        $project = $projectMainInformation->ProjectStructure;

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
        
        $ratesFile      = $request->getParameter('rates_file');
        $tempUploadPath = sfConfig::get('sf_sys_temp_dir');
        $success        = null;
        $errorMsg       = null;

        $newName    = Utilities::massageText(date('dmY_H_i_s')) . '-tc' . $tenderCompanyInfo->id;
        $ext        = pathinfo($ratesFile, PATHINFO_EXTENSION);
        $pathToFile = $tempUploadPath . $newName . '.' . $ext;
        copy($ratesFile, $pathToFile);

        $proc = new BackgroundProcess('exec php '.sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR.'symfony bgprocess:import_contractor_rates '.$project->id.' '.$company->id.' '.$pathToFile.' 2>&1 | tee '.sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR.'sync_contractor_rates-'.$project->id.'-'.$company->id.'.log');
        $proc->run();

        return $this->renderJson(array(
            'success'  => true,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeGetTotalClaimAndContractAmountInfo(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $request->isMethod('get')
        );

        $projectMainInformation = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('epid'));

        $contractAmount = 0;
        $variationAmount = 0;
        $totalUpToDateClaimAmount = 0;

        if( ! empty( $projectMainInformation ) and  $projectMainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT)
        {
            $project = $projectMainInformation->ProjectStructure;
            $postContract = $project->PostContract;

            $revision = PostContractClaimRevisionTable::getCurrentSelectedProjectRevision($postContract, false);

            $records = DoctrineQuery::create()
                ->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id')
                ->from('ProjectStructure s')
                ->leftJoin('s.BillType t')
                ->leftJoin('s.BillColumnSettings c')
                ->leftJoin('s.BillLayoutSetting bls')
                ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
                ->andWhere('s.root_id = ?', $project->id)
                ->andWhere('s.type <= ?', ProjectStructure::TYPE_BILL)
                ->addOrderBy('s.lft ASC')
                ->fetchArray();

            foreach ( $records as $key => $record )
            {
                $billTotal      = 0;
                $upToDateAmount = 0;

                if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
                {
                    if ( $records[$key]['BillType']['type'] == BillType::TYPE_PRELIMINARY )
                    {
                        list( $billTotal, $upToDateAmount ) = PreliminariesClaimTable::getUpToDateAmountByBillId($postContract, $record['id'], $revision);
                    }
                    else
                    {
                        $billTotal      = PostContractTable::getOverallTotalByBillId($record['id'], $revision->toArray());
                        $upToDateAmount = PostContractTable::getUpToDateAmountByBillId($record['id'], $revision->toArray());
                    }
                }

                $contractAmount += $billTotal;
                $totalUpToDateClaimAmount += $upToDateAmount;

                unset( $records[$key]);
            }

            $variationAmount          = $project->getVariationOrderOverallTotal();
            $totalUpToDateClaimAmount += $project->getVariationOrderUpToDateClaimAmount();

            $success = true;
            $errorMsg = null;
        }
        else
        {
            // Project does not exist within BuildSpace (SAML)
            $success = true;
            $errorMsg = null;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'records'  => array(
                'contract_amt'         => $contractAmount,
                'variation_amt'        => $variationAmount,
                'up_to_date_claim_amt' => $totalUpToDateClaimAmount
            )
        ));
    }

    public function executeGetProjectScheduleList(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $request->isMethod('get')
        );

        $projectMainInformation = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('epid'));
        $items = array();

        if( ! empty( $projectMainInformation ) and $projectMainInformation->status == ProjectMainInformation::STATUS_POSTCONTRACT)
        {
            $project = $projectMainInformation->ProjectStructure;

            $records = DoctrineQuery::create()->select('s.id, s.title, s.project_structure_id, s.type')
                ->from('ProjectSchedule s')
                ->andWhere('s.project_structure_id = ?', $project->id)
                ->addOrderBy('s.id ASC')
                ->execute();

            foreach ( $records as $key => $record )
            {
                $items[$key]['id']    = $record->id;
                $items[$key]['title'] = $record->title;
            }
        }

        return $this->renderJson($items);
    }

    public function executeGetAccumulativeCost(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $request->isMethod('get')
        );

        $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id'));
        $data = array(
            'accumulative_cost' => array(),
            'cost_vs_time'      => array()
        );

        if($projectSchedule)
        {
            $accumulativeCost = 0;
            foreach($projectSchedule->getCostVersusTimeData() as $key => $cost)
            {
                $accumulativeCost += $cost;
                $data['accumulative_cost'][] = array(strtotime($key.'-01') * 1000, $accumulativeCost);//js datetime
            }

            foreach($projectSchedule->getCostVersusTimeData() as $key => $cost)
            {
                $data['cost_vs_time'][] = array(strtotime($key.'-01') * 1000, $cost);//js datetime
            }
        }

        return $this->renderJson($data);
    }

    public function executeUploadEbqFile(sfWebRequest $request)
    {
        $eprojectProjectId = $request->getParameter('eproject_project_id');
        $userId = $request->getParameter('bs_user_id');

        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_sys_temp_dir');

        $projectInformation = null;
        $projectBreakdown   = null;
        $errorMsg           = null;
        $pathToFile         = null;
        $fileToUnzip        = array();

        foreach ($request->getFiles() as $file)
        {
            if (is_readable($file['tmp_name']))
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
            if (!empty($fileToUnzip))
            {
                $sfZipGenerator = new sfZipGenerator($fileToUnzip['name'], $fileToUnzip['path'], $fileToUnzip['ext'],
                    true, true);

                $extractedFiles = $sfZipGenerator->unzip(true);

                $extractDir = $sfZipGenerator->extractDir;

                $count = 0;

                if (!empty($extractedFiles) && is_array($extractedFiles))
                {
                    foreach ($extractedFiles as $file)
                    {
                        if ($count == 0)
                        {
                            $xmlParser = new sfBuildspaceXMLParser($file['filename'], $extractDir);
                            $xmlParser->read();

                            if ($xmlParser->xml->attributes()->exportType == ExportedFile::EXPORT_TYPE_SUB_PACKAGE)
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
                        }
                        $count ++;
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
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;

            return $this->renderJson(array(
                'errorMsg' => $errorMsg,
                'success'  => $success,
            ));
        }

        $importData = array(
            'uploadPath' => $fileInfo['uploadPath'],
            'filename'   => $fileInfo['filename'],
            'extension'  => $fileInfo['extension'],
        );

        return $this->importEBQFile($eprojectProjectId, $userId, $importData);
    }

    private function importEBQFile($eprojectProjectId, $userId, array $data)
    {
        sfConfig::set('sf_web_debug', false);

        $filename   = $data['filename'];
        $extension  = $data['extension'];
        $uploadPath = $data['uploadPath'];
        $withRate   = true;
        $withQty    = true;

        $con = ProjectStructureTable::getInstance()->getConnection();
        $errorMsg = null;
        $item     = array();
        $success  = false;

        try
        {
            $con->beginTransaction();

            $sfImport = new sfBuildspaceXMLParser($filename, $uploadPath, $extension);
            $sfImport->read();
            $fileInfo = $sfImport->getProcessedData();
            unset( $sfImport );

            $extractDir = $fileInfo->attributes()->extractDir;

            $breakdownIds = array();
            $project      = null;

            $files = (array) $fileInfo->{sfBuildspaceFileInfoXML::TAG_FILES}->children();

            if(!empty($files))
            {
                if(!array_key_exists(0,$files[sfBuildspaceFileInfoXML::TAG_FILE]))
                {
                    $fileXML   = $files[sfBuildspaceFileInfoXML::TAG_FILE];
                    $emptyBill = true;
                }
                else
                {
                    $fileXML   = $files[sfBuildspaceFileInfoXML::TAG_FILE][0];
                    $emptyBill = false;
                }

                $file = $fileXML->children();

                $xmlParser = new sfBuildspaceXMLParser((string) $file->filename, $extractDir);
                $xmlParser->read();

                $attributes = $xmlParser->xml->attributes();

                if(isset($attributes['exportType']) && !empty($attributes['exportType']) && isset($attributes['uniqueId']) && !empty($attributes['exportType']))
                {
                    $importer = new sfBuildspaceImportProjectBuilderXML($userId, (string) $file->filename, $extractDir, $con);

                    $importer->setEprojectId($eprojectProjectId);

                    $importer->process();

                    $breakdownIds = $importer->breakdownIds;

                    $project = Doctrine_Core::getTable('ProjectStructure')->find($importer->projectId);
                }

                if(!$emptyBill)
                {
                    unset($files[sfBuildspaceFileInfoXML::TAG_FILE][0]);

                    $billImporter = new sfBuildspaceImportProjectBuilderBillXML($project->MainInformation, $userId, $breakdownIds, $withQty, $withRate, $con);

                    //Bills
                    foreach($files[sfBuildspaceFileInfoXML::TAG_FILE] as $file)
                    {
                        if (!empty($breakdownIds) && $project)
                        {
                            $billImporter->processXMLFile((string) $file->filename, $extractDir);
                        }
                    }
                }

                $con->commit();

                $form = new BaseForm();
                $user = Doctrine_Core::getTable('sfGuardUser')->find($userId);

                $item = array(
                    'id'             => $project->id,
                    'title'          => $project->title,
                    'is_admin'       => $user->isAdminForProject(ProjectStructureTable::getInstance()->find($project->id), ProjectUserPermission::STATUS_PROJECT_BUILDER),
                    'status'         => ProjectMainInformation::getProjectStatusById($project->MainInformation->status),
                    'status_id'      => $project->MainInformation->status,
                    'tender_type_id' => $project->MainInformation->tender_type_id,
                    'state'          => ( $project->MainInformation->Subregions->name ) ? $project->MainInformation->Subregions->name : "N/a",
                    'country'        => ( $project->MainInformation->Regions->country ) ? $project->MainInformation->Regions->country : "N/a",
                    'created_at'     => date('d/m/Y H:i', strtotime($project->created_at)),
                    'created_by'     => ( $project->Creator->Profile->name ) ? $project->Creator->Profile->name : '-',
                    'can_be_deleted' => true,
                    '_csrf_token'    => $form->getCSRFToken()
                );

                $success = true;
            }

        }
        catch (Exception $e)
        {
            $con->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'errorMsg' => $errorMsg,
            'success'  => $success,
            'item'     => $item
        ));
    }

    public function executeUpdateLetterOfAwardStatus(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod('post'));

        $projectMainInformation = Doctrine_Core::getTable('ProjectMainInformation')->findOneByEProjectId($request->getParameter('project_id'));

        $actingUser = Doctrine_Core::getTable('EProjectUser')->find(base64_decode($request->getParameter('user_identifier')))->getBuildSpaceUser();

        $project = $projectMainInformation->ProjectStructure;

        // Do nothing in regards to letter of award.
        // The status is determined by the verifiers; if all of them have approved, the letter of award is taken to be submitted.
        if($request->getParameter('approved'))
        {
            // Push to post contract.
            $publishToPostContractOptions = PublishToPostContractOptionTable::findByProjectId($project->id);
            $useOriginalRate              = ( $publishToPostContractOptions->rate_type == PublishToPostContractOption::RATE_TYPE_ESTIMATE ) ? true : false;
            $withoutNotListedItem         = ! $publishToPostContractOptions->with_not_listed_item;
            $usersAssignedManually        = $publishToPostContractOptions->assign_users_manually;

            $this->getRequest()->setParameter('id', $project->id);
            $this->getRequest()->setParameter('usersAssignedManually', $usersAssignedManually);
            $this->getRequest()->setParameter('use_original_rate', $useOriginalRate);
            $this->getRequest()->setParameter('withoutNotListedItem', $withoutNotListedItem);
            $this->getRequest()->setParameter('user_id', $actingUser->id);

            $this->forward('tendering', 'publishToPostContract');
        }
    }

    public function executeGetClaimCertInfo(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod('post'));

        $claimCertificates = array();

        foreach($request->getParameter('claim_certificate_ids') as $certId)
        {
            if( ! $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find($certId) ) continue;

            $claimCertificates[] = $claimCertificate;
        }

        $claimCertificates = ClaimCertificateTable::getClaimCertInfo($claimCertificates);

        return $this->renderJson(array(
            'success'           => true,
            'claimCertificates' => $claimCertificates,
        ));
    }

    public function executeGetProjectOverallTotal(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isMethod('post') and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('projectStructureId'))
        );

        $overallProjectTotal = ProjectStructureTable::getOverallTotalForProject($project->id);

        $includeTax = $project->ProjectSummaryGeneralSetting->include_tax;

        if($includeTax)
        {
            $taxPercentage = $project->ProjectSummaryGeneralSetting->tax_percentage;
            $overallProjectTotal += $overallProjectTotal * ($taxPercentage / 100);
        }

        return $this->renderJson(array(
            'success'             => true,
            'overallProjectTotal' => $overallProjectTotal
        ));
    }

    public function executeGetOverallTotalByTenderAlternatives(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isMethod('post') and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('projectStructureId'))
        );

        $records = TenderAlternativeTable::getOverallTotalForTenderAlternatives($project);

        $includeTax = $project->ProjectSummaryGeneralSetting->include_tax;

        if($includeTax)
        {
            foreach($records as $tenderAlternativeId => $total)
            {
                $taxPercentage = $project->ProjectSummaryGeneralSetting->tax_percentage;
                $total += $total * ($taxPercentage / 100);

                $records[$tenderAlternativeId] = $total;
            }
        }

        return $this->renderJson(array(
            'success' => true,
            'records' => $records
        ));
    }

    public function executeGetPostContractProjectOverallTotal(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod('post'));

        $projectStructureId = $request->getParameter('projectStructureId');
        $postContractProjectOverallTotal = PostContractTable::getOverallTotalByProjectId($projectStructureId);

        return $this->renderJson(array(
            'success'                         => true,
            'postContractProjectOverallTotal' => $postContractProjectOverallTotal,
        ));
    }

    public function executeGetOverallTotalByProject(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod('post'));

        $overallTotalByProject = [];

        $projectStructureIds = $request->getParameter('projectStructureIds');

        foreach($projectStructureIds as $projectStructureId)
        {
            $overallTotalByProject[$projectStructureId] = ProjectStructureTable::getOverallTotalForProject($projectStructureId, false);
        }

        return $this->renderJson(array(
            'success'                => true,
            'overallTotalByProject' => $overallTotalByProject,
        ));
    }

    public function executeCheckClaimCertificateAccountingExportValidity(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod('post'));

        $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectStructureId'));
        $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find($request->getParameter('claimCertificateId'));
        $isValid = ItemCodeSetting::checkClaimCertificateAccountingExportValidity($projectStructure, $claimCertificate);
        
        return $this->renderJson([
            'isValid' => $isValid,
        ]);
    }
}