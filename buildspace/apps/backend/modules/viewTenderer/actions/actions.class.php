<?php

/**
 * viewTenderer actions.
 *
 * @package    buildspace
 * @subpackage viewTenderer
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class viewTendererActions extends BaseActions {

    public function executeCompanyForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $form = new ContractorForm();

        return $this->renderJson(array( 'company[_csrf_token]' => $form->getCSRFToken() ));
    }

    public function executeGetContractorList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $companies = array();

        $eProjectPDO = Doctrine_Manager::getInstance()->getConnection('eproject_conn')->getDbh();
        Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectCompany')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

        $stmt = $eProjectPDO->prepare("SELECT DISTINCT c.id, c.reference_id FROM ".EProjectCompanyTable::getInstance()->getTableName()." c
        JOIN ".EProjectContractGroupContractGroupCategoryTable::getInstance()->getTableName()." cgc ON c.contract_group_category_id  = cgc.contract_group_category_id
        JOIN ".EProjectContractGroupTable::getInstance()->getTableName()." cg ON cgc.contract_group_id = cg.id
        WHERE cg.group = ".PAM2006::CONTRACTOR." AND c.confirmed IS TRUE");

        $stmt->execute();

        $companyReferenceIdArray = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);

        if(!empty($companyReferenceIdArray))
        {
            $pdo  = $project->getTable()->getConnection()->getDbh();
            $form = new BaseForm();
            Doctrine_Manager::getInstance()->getConnectionForComponent('Company')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, 'BS_%s');

            $stmt = $pdo->prepare("SELECT c.id, c.name || ' (' || c.registration_no || ')' as name FROM " . CompanyTable::getInstance()->getTableName() . " c WHERE
            c.id NOT IN (SELECT company_id FROM " . TenderCompanyTable::getInstance()->getTableName() . " WHERE project_structure_id = " . $project->id . ")
            AND c.reference_id IN ('".implode("','", $companyReferenceIdArray)."') AND c.deleted_at IS NULL ORDER BY c.name ASC");

            $stmt->execute();

            $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $companies as $key => $company )
            {
                $companies[$key]['_csrf_token'] = $form->getCSRFToken();
            }
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $companies
        ));
    }

    public function executeGetContractors(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $tenderAlternative = null;
        if($request->hasParameter('tid'))
        {
            $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('tid'));
        }

        $tenderSetting = $project->TenderSetting;

        $pdo = $project->getTable()->getConnection()->getDbh();

        $form = new BaseForm();

        switch ($tenderSetting->contractor_sort_by)
        {
            case TenderSetting::SORT_CONTRACTOR_NAME_ASC:
                $contractorSqlOrder = "c.name ASC";
                $tenderAmountSqlOrder = "total ASC";
                break;
            case TenderSetting::SORT_CONTRACTOR_NAME_DESC:
                $contractorSqlOrder = "c.name DESC";
                $tenderAmountSqlOrder = "total ASC";
                break;
            case TenderSetting::SORT_CONTRACTOR_HIGHEST_LOWEST:
                $tenderAmountSqlOrder = "total DESC";
                $contractorSqlOrder = "c.name ASC";
                break;
            case TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST:
                $tenderAmountSqlOrder = "total ASC";
                $contractorSqlOrder = "c.name ASC";
                break;
            default:
                throw new Exception('invalid sort option');
        }

        $awardedCompanyId = $tenderSetting->awarded_company_id > 0 ? $tenderSetting->awarded_company_id : - 1;

        if($awardedCompanyId > 0 && $tenderAlternative && !$tenderAlternative->is_awarded)
        {
            $awardedCompanyId = -1;
        }

        $stmt = $pdo->prepare("SELECT c.id, c.name, xref.id AS tender_company_id, xref.show
            FROM " . CompanyTable::getInstance()->getTableName() . " c
            JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
            WHERE xref.project_structure_id = " . $project->id . "
            AND c.id <> " . $awardedCompanyId . "
            AND c.deleted_at IS NULL
            ORDER BY " . $contractorSqlOrder);
        
        $stmt->execute();

        $tenderers = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

        $tenderAlternativeJoinSql  = "";
        $tenderAlternativeWhereSql = "";

        if($tenderAlternative)
        {
            $tenderAlternativeJoinSql = " JOIN ".TenderAlternativeBillTable::getInstance()->getTableName()." tax ON e.project_structure_id = tax.project_structure_id
            JOIN ".TenderAlternativeTable::getInstance()->getTableName()." ta ON tax.tender_alternative_id = ta.id ";

            $tenderAlternativeWhereSql = " AND ta.id = ".$tenderAlternative->id." AND ta.deleted_at IS NULL AND ta.project_revision_deleted_at IS NULL ";
        }

        $stmt = $pdo->prepare("SELECT c.id, COALESCE(SUM(r.grand_total), 0) AS total
            FROM " . CompanyTable::getInstance()->getTableName() . " c
            JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
            JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON r.bill_element_id = e.id
            ".$tenderAlternativeJoinSql."
            WHERE xref.project_structure_id = " . $project->id . "
            AND c.id <> " . $awardedCompanyId . " AND r.grand_total <> 0
            AND e.deleted_at IS NULL
            ".$tenderAlternativeWhereSql."
            AND c.deleted_at IS NULL
            GROUP BY c.id
            ORDER BY " . $tenderAmountSqlOrder);
        
        $stmt->execute();

        $tenderAmountRecords = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $records = [];

        switch ($tenderSetting->contractor_sort_by)
        {
            case TenderSetting::SORT_CONTRACTOR_NAME_ASC:
            case TenderSetting::SORT_CONTRACTOR_NAME_DESC:
                foreach($tenderers as $companyId => $tenderer)
                {
                    $tenderer['id']    = $companyId;
                    $tenderer['total'] = (array_key_exists($tenderer['id'], $tenderAmountRecords)) ? $tenderAmountRecords[$tenderer['id']] : 0;
                    $records[] = $tenderer;
                }
                break;
            case TenderSetting::SORT_CONTRACTOR_HIGHEST_LOWEST:
            case TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST:
                foreach($tenderAmountRecords as $companyId => $tenderAmount)
                {
                    if(array_key_exists($companyId, $tenderers))
                    {
                        $tenderers[$companyId]['id']    = $companyId;
                        $tenderers[$companyId]['total'] = $tenderAmount;
                        $records[] = $tenderers[$companyId];

                        unset($tenderers[$companyId]);
                    }
                    unset($tenderAmountRecords[$companyId]);
                }

                foreach($tenderers as $companyId => $tenderer)
                {
                    $tenderer['id']    = $companyId;
                    $tenderer['total'] = 0;
                    
                    if($tenderSetting->contractor_sort_by == TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST)
                    {
                        array_unshift($records , $tenderer);
                    }
                    else
                    {
                        $records[] = $tenderer;
                    }
                    unset($tenderers[$companyId]);
                }
                break;
            default:
                throw new Exception('invalid sort option');
        }
        
        $companies = [];

        if ( $awardedCompanyId > 0)
        {
            $awardedCompany = $tenderSetting->AwardedCompany;

            $companySetting = DoctrineQuery::create()->select('s.id, s.show')
                ->from('TenderCompany s')
                ->where('s.company_id = ?', $awardedCompany->id)
                ->andWhere('s.project_structure_id = ?', $project->id)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();
            
            if($tenderAlternative)
            {
                $adjustedTotal = ($tenderAlternative->is_awarded) ? $awardedCompany->getTenderTotalByTenderAlternative($tenderAlternative) : 0;
                $total         = ($tenderAlternative->is_awarded) ? $tenderSetting->original_tender_value : 0;
                $awarded       = ($tenderAlternative->is_awarded);
            }
            else
            {
                $adjustedTotal = $awardedCompany->getTenderTotalByProjectId($project->id);
                $total         = $tenderSetting->original_tender_value;
                $awarded       = true;
            }

            array_push($companies, array(
                'id'                => $awardedCompany->id,
                'name'              => $awardedCompany->name,
                'total'             => $total,
                'adjusted_total'    => $adjustedTotal,
                'show'              => $companySetting['show'],
                'tender_company_id' => $companySetting['id'],
                '_csrf_token'       => $form->getCSRFToken(),
                'awarded'           => $awarded
            ));

            unset( $awardedCompany );
        }

        foreach ( $records as $key => $record )
        {
            $record['_csrf_token']    = $form->getCSRFToken();
            $record['adjusted_total'] = 0;
            $record['awarded']        = false;

            array_push($companies, $record);

            unset( $records[$key], $record );
        }

        array_push($companies, array(
            'id'             => Constants::GRID_LAST_ROW,
            'name'           => '',
            'show'           => '',
            'adjusted_total' => 0,
            'total'          => 0,
            'awarded'        => false,
            '_csrf_token'    => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $companies
        ));
    }

    public function executeTenderCompanyForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $form = new TenderCompanyForm();

        return $this->renderJson(array(
            'tender_company[project_structure_id]' => $project->id,
            'tender_company[_csrf_token]'          => $form->getCSRFToken()
        ));
    }

    public function executeTenderCompanyAdd(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post')
        );

        $params = $request->getParameter('tender_company');

        $projectId = $params['project_structure_id'];
        $companyId = $params['company_id'];

        $this->forward404Unless(
            strlen($projectId) > 0 and
            strlen($companyId) > 0 and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($projectId) and
            $company = Doctrine_Core::getTable('Company')->find($companyId)
        );

        $tenderCompanyXref = TenderCompanyTable::getByProjectIdAndCompanyId($project->id, $company->id);

        $form = new TenderCompanyForm($tenderCompanyXref);

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();
            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors ));
    }

    public function executeTenderCompanyDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid'))
        );

        $tenderAlternative = null;
        if($request->hasParameter('tid'))
        {
            $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('tid'));
        }

        $tenderSetting = $project->TenderSetting;

        $errorMsg = null;
        try
        {
            if ( $company->id == $tenderSetting->awarded_company_id )
            {
                $tenderSetting->awarded_company_id    = null;
                $tenderSetting->original_tender_value = 0;
                $tenderSetting->save();

                if($tenderAlternative && $tenderAlternative->is_awarded)
                {
                    $tenderAlternative->is_awarded = false;
                    $tenderAlternative->save();
                }
            }

            $tenderCompany = TenderCompanyTable::getByProjectIdAndCompanyId($project->id, $company->id);

            $tenderCompany->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeAwardCompany(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid'))
        );

        $tenderAlternative = null;
        if($request->hasParameter('tid'))
        {
            $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('tid'));
        }

        $tenderSetting = $project->TenderSetting;

        $con = $tenderSetting->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $originalAwardedId = $tenderSetting->awarded_company_id;

            if($tenderAlternative)
            {
                $isAwarded = $tenderAlternative->is_awarded;

                $pdo = $con->getDbh();

                $stmt = $pdo->prepare("UPDATE " . TenderAlternativeTable::getInstance()->getTableName() . "
                SET is_awarded = FALSE
                WHERE id <> ".$tenderAlternative->id." AND project_structure_id = ".$project->id);

                $stmt->execute([]);
                
                if($isAwarded)
                {
                    $awarded = !($tenderSetting->awarded_company_id == $company->id);
                }
                else
                {
                    $awarded = true;
                }

                $tenderAlternative->is_awarded = $awarded;
                $tenderAlternative->save($con);

                $tenderSetting->awarded_company_id    = ($awarded) ? $company->id : null;
                $tenderSetting->original_tender_value = ($awarded) ? $company->getTenderTotalByTenderAlternative($tenderAlternative) : 0;
                $tenderSetting->save($con);
            }
            else
            {
                $tenderSetting->awarded_company_id    = $tenderSetting->awarded_company_id == $company->id ? null : $company->id;
                $tenderSetting->original_tender_value = $originalAwardedId == $company->id ? 0 : $company->getTenderTotalByProjectId($project->id);
                
                $tenderSetting->save($con);
            }

            $eprojectProject = $project->MainInformation->getEProjectProject();
            $eprojectCompany = $company->getEProjectCompany();
            if($eprojectProject && $eprojectCompany)
            {
                $tender = $eprojectProject->getLatestTender();
                if($tender)
                {
                    $tender->currently_selected_tenderer_id = ($tenderSetting->awarded_company_id) ? $eprojectCompany->id : null;
                    $tender->save();

                    $tenderAmountSql = "";
                    if($tenderAlternative)
                    {
                        /* we need to set tender amount in company_tender if there is tender alternative because tender_amount is default to zero. 
                         * tender_amount in eproject for project with tender alternative will be save in company_tender_tender_alternatives
                         */
                        $tenderAmountSql = ", tender_amount = '".$tenderSetting->original_tender_value."' ";
                    }

                    $pdo = EProjectTenderTable::getInstance()->getConnection()->getDbh();

                    $stmt = $pdo->prepare("UPDATE company_tender
                        SET selected_contractor = FALSE
                        WHERE tender_id = ".$tender->id);

                    $stmt->execute();

                    if($tenderSetting->awarded_company_id)
                    {
                        $stmt = $pdo->prepare("UPDATE company_tender
                        SET selected_contractor = TRUE ".$tenderAmountSql."
                        WHERE company_id = ".$eprojectCompany->id." AND tender_id = ".$tender->id);

                        $stmt->execute();
                    }

                    $proc = new BackgroundProcess($s = "exec php ".Utilities::getEProjectArtisanPath()." project:create-award-recommendation-bill-details ".$tender->id." 2>&1 ");
                    $proc->run();
                }
            }

            $con->commit();

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeSortUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $tenderSetting = $project->TenderSetting;

        $con = $tenderSetting->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            switch ($request->getParameter('opt'))
            {
                case TenderSetting::SORT_CONTRACTOR_NAME_ASC:
                    $sortBy = TenderSetting::SORT_CONTRACTOR_NAME_ASC;
                    break;
                case TenderSetting::SORT_CONTRACTOR_NAME_DESC:
                    $sortBy = TenderSetting::SORT_CONTRACTOR_NAME_DESC;
                    break;
                case TenderSetting::SORT_CONTRACTOR_HIGHEST_LOWEST:
                    $sortBy = TenderSetting::SORT_CONTRACTOR_HIGHEST_LOWEST;
                    break;
                case TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST:
                    $sortBy = TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST;
                    break;
                default:
                    throw new Exception('invalid sort option');
            }

            $tenderSetting->contractor_sort_by = $sortBy;
            $tenderSetting->save($con);

            $con->commit();

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeGetTendererAttachments(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('company_id'))
        );

        $data = array();
        $form = new CsrfForm();

        foreach(TenderCompanyAttachmentsTable::getAttachments($project->id, $company->id) as $key => $info)
        {
            $extension = '';

            if( ! empty( $info['extension'] ) ) $extension .= ".{$info['extension']}";

            // Todo: check if file exists.

            array_push($data, array(
                'id'          => $info['id'],
                'name'        => $info['filename'] . $extension,
                'file_path'   => $info['filepath'],
                'updated_by'  => $info['name'],
                'updated_at'  => date('d/m/Y g:i a', strtotime($info['updated_at'])),
                'delete'      => 'remove',
                '_csrf_token' => $form->getCSRFToken(),
            ));
        }

        array_push($data, array(
            'id'          => Constants::GRID_LAST_ROW,
            'name'        => '',
            'file_path'   => '',
            'updated_by'  => '',
            'updated_at'  => '',
            'delete'      => '',
            '_csrf_token' => $form->getCSRFToken(),
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetTendererRemarks(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('company_id'))
        );

        $success = true;
        $errorMsg = null;
        $data = array();

        $data['remarks'] = null;

        if($record = TenderCompanyTable::getRecordByProjectAndCompany($project->id, $company->id))
        {
            $data['remarks'] = $record->remarks;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data ));
    }

    public function executeUploadTendererAttachment(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('company'))
        );

        $success  = null;
        $errorMsg = null;

        try
        {
            foreach($request->getFiles() as $file)
            {
                if( ! is_readable($file['tmp_name']) )
                {
                    $success = false;
                    $errorMsg = 'File unreadable';
                    break;
                }

                $filename = Utilities::sanitize_file_name(pathinfo($file['name'], PATHINFO_FILENAME));
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

                $absolutePathToFile = Utilities::createFilePath(array( sfConfig::get('sf_upload_dir'), 'tender-company-attachments', 'project-' . $project->id, 'company-' . $company->id ), $filename, $extension);

                move_uploaded_file($file['tmp_name'], $absolutePathToFile);

                $relativePathToFile = DIRECTORY_SEPARATOR . 'uploads' . substr($absolutePathToFile, strlen(sfConfig::get('sf_upload_dir')));

                TenderCompanyAttachmentsTable::saveAttachment($project->id, $company->id, $relativePathToFile, $filename, $extension);

                $success = true;
            }
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeGetCompanyDetails(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('company_id'))
        );

        $success = true;
        $errorMsg = null;

        $data = DoctrineQuery::create()->select('c.id, c.name')
            ->from('Company c')
            ->where('c.id = ?', $company->id)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $data ));
    }

    public function executeDeleteTendererAttachment(sfWebRequest $request)
    {
        $this->forward404Unless(
            $attachmentRecord = Doctrine_Core::getTable('TenderCompanyAttachments')->find($request->getParameter('id'))
        );

        // Todo: Check for permission.

        $form = new CsrfForm();
        $success  = false;
        $errorMsg = null;

        if ( $this->isFormValid($request, $form) )
        {
            try
            {
                $success =true;
                $pathToFile = sfConfig::get('sf_upload_dir') . substr($attachmentRecord->filepath, strlen(DIRECTORY_SEPARATOR . 'uploads'));
                unlink($pathToFile);
                TenderCompanyAttachmentsTable::deleteAttachment($attachmentRecord->id);
            }
            catch(Exception $e)
            {
                $errorMsg = $e->getMessage();
            }
        }
        else
        {
            $errorMsg  = $form->getErrors();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeUpdateTendererRemarks(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('company_id'))
        );

        $form    = new CsrfForm();
        $success = false;
        $errorMsg = null;

        if( $this->isFormValid($request, $form) )
        {
            try
            {
                TenderCompanyTable::updateCompanyRemarks($project->id, $company->id, $request->getParameter('remarks'));
                $success = true;
            }
            catch(Exception $e)
            {
                $errorMsg = $e->getMessage();
            }
        }
        else
        {
            $errorMsg = 'Form is not valid';
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeImportContractorRates(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless($request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid'))
        );

        $tempUploadPath = sfConfig::get('sf_sys_temp_dir');
        $success        = null;
        $errorMsg       = null;

        $tenderCompanyInfo = DoctrineQuery::create()->select('tc.id, tc.project_structure_id, tc.company_id, tc.total_amount')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $project->id)
            ->andWhere('tc.company_id = ?', $company->id)
            ->fetchOne();

        $fileToUnzip = array();

        foreach ( $request->getFiles() as $file )
        {
            if ( is_readable($file['tmp_name']) )
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

        try
        {
            if ( count($fileToUnzip) )
            {
                $sfZipGenerator = new sfZipGenerator($fileToUnzip['name'], $fileToUnzip['path'], $fileToUnzip['ext'], true, true);

                $extractedFiles = $sfZipGenerator->unzip();

                $extractDir = $sfZipGenerator->extractDir;

                $count = 0;

                $userId = $this->getUser()->getGuardUser()->id;

                foreach ($extractedFiles as $file)
                {
                    if ($count == 0)
                    {
                        $importer = new sfBuildspaceXMLParser($file['filename'], $extractDir, null, false);

                        $importer->read();

                        $xmlData = $importer->getProcessedData();

                        $this->validateRatesFile($project, $xmlData);

                        //flush contractor existing rates
                        $tenderCompanyInfo->flushExistingRates();
                    }
                    else
                    {
                        $importer = new sfBuildspaceXMLParser($file['filename'], $extractDir, null, false);

                        $importer->read();

                        if ($importer->xml->attributes()->isSupplyOfMaterialBill)
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
                        else if ($importer->xml->attributes()->isScheduleOfRateBill)
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
                    }

                    $count ++;
                }

                $success = true;
            }
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeExportContractorRates(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            strlen($request->getParameter('filename')) > 0 and
            $project = ProjectStructureTable::getProjectInformationByProjectId($request->getParameter('pid')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $request->hasParameter('wnli')
        );

        $withNotListedItem = strtolower($request->getParameter('wnli')) == "true" ? true : false;

        $errorMsg = null;

        try
        {
            $count = 0;

            $filesToZip = array();

            $projectId = $project['structure']['id'];

            $tenderCompanyXref = TenderCompanyTable::getByProjectIdAndCompanyId($projectId, $company->id);

            unset( $project['structure']['tender_origin_id'], $project['mainInformation']['id'] );

            $projectUniqueId = $project['mainInformation']['unique_id'];

            $sfProjectExport = new sfBuildspaceExportProjectXML($count . "_" . $project['structure']['id'], $projectUniqueId, ExportedFile::EXPORT_TYPE_RATES);

            $currentRevision = ProjectRevisionTable::getCurrentSelectedProjectRevisionFromBillId($project['structure']['root_id'], Doctrine_Core::HYDRATE_ARRAY);

            $sfProjectExport->process($project['structure'], $project['mainInformation'], null, array( $currentRevision ), $project['tenderAlternatives'], true);

            array_push($filesToZip, $sfProjectExport->getFileInformation());

            foreach ( $project['breakdown'] as $structure )
            {
                $count ++;

                if ( $structure['type'] == ProjectStructure::TYPE_BILL )
                {
                    $billData = TenderBillItemRateTable::getContractorBillRatesByBillId($structure['id'], $tenderCompanyXref->id, $withNotListedItem);

                    $sfBillExport = new sfBuildspaceExportContractorBillRatesXML($tenderCompanyXref, $count . '_' . $structure['title'], $sfProjectExport->uploadPath, $structure['id']);

                    $sfBillExport->process($billData, true);

                    array_push($filesToZip, $sfBillExport->getFileInformation());
                }

                unset( $structure );
            }

            $sfZipGenerator = new sfZipGenerator("RationalizedRate_" . $projectId, null, null, true, true);

            $sfZipGenerator->createZip($filesToZip);

            $fileInfo = $sfZipGenerator->getFileInfo();

            $fileSize     = filesize($fileInfo['pathToFile']);
            $fileContents = file_get_contents($fileInfo['pathToFile']);
            $mimeType     = Utilities::mimeContentType($fileInfo['pathToFile']);

            unlink($fileInfo['pathToFile']);

            $this->getResponse()->clearHttpHeaders();
            $this->getResponse()->setStatusCode(200);
            $this->getResponse()->setContentType($mimeType);
            $this->getResponse()->setHttpHeader(
                "Content-Disposition",
                "attachment; filename*=UTF-8''" . rawurlencode($request->getParameter('filename')) . "." . $fileInfo['extension']
            );
            $this->getResponse()->setHttpHeader('Content-Description', 'File Transfer');
            $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
            $this->getResponse()->setHttpHeader('Content-Length', $fileSize);
            $this->getResponse()->setHttpHeader('Cache-Control', 'public, must-revalidate');
            // if https then always give a Pragma header like this  to overwrite the "pragma: no-cache" header which
            // will hint IE8 from caching the file during download and leads to a download error!!!
            $this->getResponse()->setHttpHeader('Pragma', 'public');
            $this->getResponse()->sendHttpHeaders();

            if (ob_get_contents()) ob_end_flush();

            return $this->renderText($fileContents);
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => false,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeUpdateCompanySelection(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
        );

        $errorMsg = null;
        $success  = null;

        if ( $request->getParameter('companyIds') == '' || $request->getParameter('companyIds') == null )
        {
            TenderCompanyTable::updateAllShowStatusByProjectId($project->id, 0);

            $success = true;
        }
        else
        {
            $companyIds = Utilities::array_filter_integer(explode(',', $request->getParameter('companyIds')));

            //Flush all status
            TenderCompanyTable::updateAllShowStatusByProjectId($project->id, 0);

            //Update All status by company Ids
            TenderCompanyTable::updateAllShowStatusByCompanyIdsAndProjectId($companyIds, $project->id, 1);

            $success = true;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeGetProjectBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('tid'));

        $tenderAlternativeProjectStructureIds = [];

        if($tenderAlternative)
        {
            //we set default to -1 so it should return empty query if there is no assigned bill for this tender alternative;
            $tenderAlternativeProjectStructureIds = [-1];
            $tenderAlternativesBills = $tenderAlternative->getAssignedBills();

            if($tenderAlternativesBills)
            {
                $tenderAlternativeProjectStructureIds = array_column($tenderAlternativesBills, 'id');
            }
        }

        $query = DoctrineQuery::create()->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL);
        
        if(!empty($tenderAlternativeProjectStructureIds))
        {
            $query->whereIn('s.id', $tenderAlternativeProjectStructureIds);
        }
        
        $records = $query->addOrderBy('s.lft ASC')->fetchArray();

        $count = 0;

        $form = new BaseForm();

        $projectSumTotal = 0;

        if($tenderAlternative)
        {
            $tenderAlternativeId = $tenderAlternative->id;
            $tenderAlternativesSumTotal = TenderAlternativeTable::getOverallTotalForTenderAlternatives($project);
            if(array_key_exists($tenderAlternative->id, $tenderAlternativesSumTotal))
            {
                $projectSumTotal = $tenderAlternativesSumTotal[$tenderAlternative->id];
            }
        }
        else
        {
            $tenderAlternativeId = null;
            $projectSumTotal = ProjectStructureTable::getOverallTotalForProject($project->id);
        }
        
        $contractorRates = TenderCompanyTable::getAllDisplayedContractorBillAmountByProjectId($project->id, $tenderAlternativeId);

        $contractorSupplyOfMaterialTotal = TenderCompanyTable::getAllDisplayedContractorSupplyOfMaterialAmountByProjectId($project->id);

        $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($project);

        foreach ( $records as $key => $record )
        {
            $count = ($record['type'] == ProjectStructure::TYPE_BILL or $record['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL or $record['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL) ? $count + 1 : $count;

            if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
            {
                $records[$key]['bill_type']                  = $record['BillType']['type'];
                $records[$key]['bill_status']                = $record['BillType']['status'];
                $records[$key]['overall_total_after_markup'] = (array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;

                foreach ( $contractorRates as $contractorId => $contractor )
                {
                    if ( array_key_exists($record['id'], $contractor['bill']) )
                    {
                        $records[$key][$contractorId . '-overall_total_after_markup'] = $contractor['bill'][$record['id']];
                    }
                    else
                    {
                        $records[$key][$contractorId . '-overall_total_after_markup'] = 0;
                    }
                }
            }
            else if ( $record['type'] == ProjectStructure::TYPE_ROOT )
            {
                $records[$key]['overall_total_after_markup'] = $projectSumTotal;

                foreach ( $contractorRates as $contractorId => $contractor )
                {
                    $records[$key][$contractorId . '-overall_total_after_markup'] = $contractor['project_total'];
                }
            }
            else if($record['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL)
            {
                foreach($contractorSupplyOfMaterialTotal as $somKey => $supplyOfMaterialTotal)
                {
                    if($record['id'] == $supplyOfMaterialTotal['bill_id'])
                    {
                        $records[$key][$supplyOfMaterialTotal['company_id'].'-overall_total_after_markup'] = $supplyOfMaterialTotal['total'];

                        unset($contractorSupplyOfMaterialTotal[$somKey]);
                    }
                }
            }

            $records[$key]['count']       = ($record['type'] == ProjectStructure::TYPE_BILL or $record['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL or $record['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL) ? $count : null;
            $records[$key]['_csrf_token'] = $form->getCSRFToken();

            unset( $records[$key]['BillLayoutSetting'] );
            unset( $records[$key]['BillType'] );
            unset( $records[$key]['BillColumnSettings'] );
        }

        array_push($records, array(
            'id'                         => Constants::GRID_LAST_ROW,
            'title'                      => "",
            'type'                       => 1,
            'level'                      => 0,
            'count'                      => null,
            'overall_total_after_markup' => 0,
            '_csrf_token'                => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetElementList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $elements = DoctrineQuery::create()->select('e.id, e.description, fc.column_name, fc.value, fc.final_value')
            ->from('BillElement e')->leftJoin('e.FormulatedColumns fc')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $form = new BaseForm();

        $billMarkupSetting = $bill->BillMarkupSetting;

        //We get All Element Sum Group By Element Here so that we don't have to reapeat query within element loop
        $markupSettingsInfo = array(
            'bill_markup_enabled'    => $billMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage' => $billMarkupSetting->bill_markup_percentage,
            'element_markup_enabled' => $billMarkupSetting->element_markup_enabled,
            'item_markup_enabled'    => $billMarkupSetting->item_markup_enabled,
            'rounding_type'          => $billMarkupSetting->rounding_type > 0 ? $billMarkupSetting->rounding_type : BillMarkupSetting::ROUNDING_TYPE_DISABLED
        );

        $elementSumByBillColumnSetting = array();

        $contractorRates = TenderCompanyTable::getAllDisplayedContractorElementGrandTotalByProjectAndBillId($bill->root_id, $bill->id);

        //we get sum of elements total by bill column setting so we won't keep on calling the same query in element list loop
        foreach ( $bill->BillColumnSettings as $column )
        {
            //Get Element Total Rates
            $ElementTotalRates                          = ProjectStructureTable::getTotalItemRateByAndBillColumnSettingIdGroupByElement($bill, $column);
            $elementSumByBillColumnSetting[$column->id] = $ElementTotalRates['grandTotalElement'];
            $totalRateByBillColumnSetting[$column->id]  = $ElementTotalRates['elementToRates'];
            unset( $column );
        }

        foreach ( $elements as $key => $element )
        {
            $elements[$key]['markup_rounding_type'] = $markupSettingsInfo['rounding_type'];
            $overallTotalAfterMarkup                = 0;

            foreach ( $bill->BillColumnSettings as $column )
            {
                $total = $totalRateByBillColumnSetting[$column->id][$element['id']][0]['total_rate_after_markup'];
                $overallTotalAfterMarkup += $total;

                unset( $column );
            }

            unset( $elements[$key]['FormulatedColumns'] );

            foreach ( $contractorRates as $contractorId => $contractor )
            {
                if ( array_key_exists($element['id'], $contractor['element']) )
                {
                    $elements[$key][$contractorId . '-overall_total_after_markup'] = $contractor['element'][$element['id']];
                }
            }

            $elements[$key]['overall_total_after_markup'] = $overallTotalAfterMarkup;
            $elements[$key]['_csrf_token']                = $form->getCSRFToken();
        }

        array_push($elements, array(
            'id'                         => Constants::GRID_LAST_ROW,
            'description'                => '',
            'overall_total_after_markup' => 0,
            'relation_id'                => $bill->id,
            '_csrf_token'                => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bill_id'))
        );

        $pdo                     = $bill->getTable()->getConnection()->getDbh();
        $form                    = new BaseForm();
        $items                   = array();
        $elementMarkupPercentage = 0;
        $pageNoPrefix            = $bill->BillLayoutSetting->page_no_prefix;

        /*
         * This bit here is to get bill and element markup information so we can use to calculate rate after markup for each items
         */
        if ( $bill->BillMarkupSetting->element_markup_enabled )
        {
            $stmt = $pdo->prepare("SELECT COALESCE(c.final_value, 0) as value FROM " . BillElementFormulatedColumnTable::getInstance()->getTableName() . " c
                JOIN " . BillElementTable::getInstance()->getTableName() . " e ON c.relation_id = e.id
                WHERE e.id = " . $element->id . " AND c.column_name = '" . BillElement::FORMULATED_COLUMN_MARKUP_PERCENTAGE . "'
                AND c.deleted_at IS NULL AND e.deleted_at IS NULL");

            $stmt->execute();

            $elementMarkupResult     = $stmt->fetch(PDO::FETCH_ASSOC);
            $elementMarkupPercentage = $elementMarkupResult ? (float) $elementMarkupResult['value'] : 0;
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

        $billItemIds = array();

        foreach($billItems as $billItem)
        {
            $billItemIds[] = $billItem['id'];
        }

        $contractorRates = TenderCompanyTable::getAllDisplayedContractorRatesByElementId($element->id);
        $contractorIds   = array();

        foreach ( $contractorRates as $contractorId => $rates )
        {
            array_push($contractorIds, $contractorId);
        }

        $originBillItemInfo = BillItemTable::getOriginBillItems($billItemIds);

        foreach ( $billItems as $billItem )
        {
            $rate                  = 0;
            $rateAfterMarkup       = 0;
            $itemMarkupPercentage  = 0;
            $grandTotalAfterMarkup = 0;

            $billItem['bill_ref']                    = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
            $billItem['type']                        = (string) $billItem['type'];
            $billItem['uom_id']                      = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
            $billItem['relation_id']                 = $element->id;
            $billItem['linked']                      = false;
            $billItem['_csrf_token']                 = $form->getCSRFToken();
            $billItem['project_revision_deleted_at'] = is_null($billItem['project_revision_deleted_at']) ? false : $billItem['project_revision_deleted_at'];

            if ( array_key_exists($billItem['id'], $formulatedColumns) )
            {
                $itemFormulatedColumns = $formulatedColumns[$billItem['id']];

                foreach ( $itemFormulatedColumns as $formulatedColumn )
                {
                    if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_RATE )
                    {
                        $rate = $formulatedColumn['final_value'];
                    }

                    if ( $formulatedColumn['column_name'] == BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE )
                    {
                        $itemMarkupPercentage = $formulatedColumn['final_value'];
                    }
                }

                unset( $formulatedColumns[$billItem['id']], $itemFormulatedColumns );

                $rateAfterMarkup = BillItemTable::calculateRateAfterMarkup($rate, $itemMarkupPercentage, $markupSettingsInfo);
            }

            $billItem['rate_after_markup'] = $rateAfterMarkup;

            foreach ( $bill->BillColumnSettings as $column )
            {
                $quantityPerUnit = 0;

                if ( array_key_exists($column->id, $quantityPerUnitByColumns) && array_key_exists($billItem['id'], $quantityPerUnitByColumns[$column->id]) )
                {
                    $quantityPerUnit = $quantityPerUnitByColumns[$column->id][$billItem['id']][0];
                    unset( $quantityPerUnitByColumns[$column->id][$billItem['id']] );
                }

                $total = 0;

                if ( array_key_exists($column->id, $billItemTypeReferences) && array_key_exists($billItem['id'], $billItemTypeReferences[$column->id]) )
                {
                    $totalPerUnit = $rateAfterMarkup * $quantityPerUnit;
                    $total        = number_format($totalPerUnit, 2, '.', '') * $column->quantity;

                    unset( $billItemTypeReferences[$column->id][$billItem['id']] );
                }

                $grandTotalAfterMarkup += $total;

            }

            $billItem['grand_total_after_markup'] = $grandTotalAfterMarkup;

            $billItemNotListed = array();

            if ( $billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED && !empty($contractorIds) )
            {
                //Get Bill Item Not Listed From Contractors
                $stmt = $pdo->prepare("SELECT inl.id, r.rate, r.grand_total, tc.company_id, CONCAT('<b>', c.name,'</b><br/><br/>',inl.description) as description,
                    inl.tender_company_id, uom.id AS uom_id, uom.symbol AS uom_symbol
                    FROM " . TenderBillItemNotListedTable::getInstance()->getTableName() . " inl
                    LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON inl.uom_id = uom.id AND uom.deleted_at IS NULL
                    JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.id = inl.tender_company_id
                    JOIN " . CompanyTable::getInstance()->getTableName(). " c ON  c.id = tc.company_id AND c.deleted_at IS NULL
                    LEFT JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " r ON r.tender_bill_item_not_listed_id = inl.id
                    WHERE inl.bill_item_id = " . $billItem['id'] . " AND tc.company_id IN (" . implode(',', $contractorIds) . ")");

                $stmt->execute();

                $billItemNotListed = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            else
            {
                foreach ( $contractorRates as $contractorId => $rates )
                {
                    if ( array_key_exists($billItem['id'], $rates) )
                    {
                        $billItem[$contractorId . '-rate-value']               = $rates[$billItem['id']][0]['rate'];
                        $billItem[$contractorId . '-grand_total_after_markup'] = $rates[$billItem['id']][0]['grand_total'];

                        unset( $rates, $contractorRates[$contractorId][$billItem['id']] );
                    }
                    else
                    {
                        $billItem[$contractorId . '-rate-value']               = 0;
                        $billItem[$contractorId . '-grand_total_after_markup'] = 0;
                    }
                }
            }

            if( array_key_exists($billItem['id'], $originBillItemInfo) )
            {
                $originIds = ProjectStructureTable::extractOriginId($bill->tender_origin_id);

                $originalItemTotalQty = 0;

                if( isset( $originIds['buildspace_id'] ) && ( $originIds['buildspace_id'] == sfConfig::get('app_register_buildspace_id') ) )
                {
                    $subPackage = Doctrine_Core::getTable('SubPackage')->find($originIds['sub_package_id']);

                    $totalAssignedUnits = SubPackageTable::getSelectedUnitsBySubPackageAndBillId($subPackage, $originIds['origin_id']);
                }

                foreach($originBillItemInfo[ $billItem['id'] ]['ref_formulated_columns'] ?? array() as $refFormulatedColumn)
                {
                    $billItem[ $refFormulatedColumn['column_name'] ] = $refFormulatedColumn['final_value'];

                    if( $refFormulatedColumn['column_name'] !== 'quantity_per_unit_remeasurement' ) continue;

                    $originalItemTotalQty += ( $totalAssignedUnits[ $refFormulatedColumn['bill_column_setting_id'] ] ?? 0 ) * $billItem['quantity_per_unit_remeasurement'];
                }

                $billItem['quantity_per_unit_remeasurement'] = $originalItemTotalQty;

                $billItem['remeasurement_amount'] = $billItem['quantity_per_unit_remeasurement'] * $billItem['rate_after_markup'];
            }

            array_push($items, $billItem);

            if ( !empty($billItemNotListed) )
            {
                $itemNotListedData = $billItem;

                foreach ( $billItemNotListed as $itemNotListed )
                {
                    $stmt = $pdo->prepare("SELECT  inl_q.bill_column_setting_id, inl_q.final_value FROM " . TenderBillItemNotListedQuantityTable::getInstance()->getTableName() . " inl_q
                        WHERE inl_q.tender_bill_item_not_listed_id = " . $itemNotListed['id']);

                    $stmt->execute();

                    $itemNotListedQuantities = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                    $grandTotalQty = 0;

                    foreach ( $bill->BillColumnSettings as $column )
                    {
                        if ( array_key_exists($column->id, $itemNotListedQuantities) )
                        {
                            $typeTotal = $column->quantity * $itemNotListedQuantities[$column->id];

                            $grandTotalQty += $typeTotal;
                        }
                    }

                    $itemNotListedData['grand_total_quantity']     = $grandTotalQty;
                    $itemNotListedData['id']                       = $itemNotListed['company_id'] . '-' . $billItem['id'];
                    $itemNotListedData['description']              = $itemNotListed['description'];
                    $itemNotListedData['uom_id']                   = $itemNotListed['uom_id'];
                    $itemNotListedData['uom_symbol']               = $itemNotListed['uom_symbol'];
                    $itemNotListedData['rate_after_markup']        = 0;
                    $itemNotListedData['grand_total_after_markup'] = 0;
                    $itemNotListedData['linked']                   = false;
                    $itemNotListedData['level']                    = $billItem['level'] + 1;

                    foreach ( $contractorRates as $contractorId => $rates )
                    {
                        if ( $contractorId == $itemNotListed['company_id'] )
                        {
                            $itemNotListedData[$contractorId . '-rate-value']               = $itemNotListed['rate'];
                            $itemNotListedData[$contractorId . '-grand_total_after_markup'] = $itemNotListed['grand_total'];

                            unset( $rates, $contractorRates[$contractorId][$billItem['id']] );
                        }
                        else
                        {
                            $itemNotListedData[$contractorId . '-rate-value']               = 0;
                            $itemNotListedData[$contractorId . '-grand_total_after_markup'] = 0;
                        }
                    }

                    array_push($items, $itemNotListedData);
                }
            }

            unset( $billItem );
        }

        unset( $billItems );

        $defaultLastRow = array(
            'id'                       => Constants::GRID_LAST_ROW,
            'bill_ref'                 => '',
            'description'              => '',
            'type'                     => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
            'uom_id'                   => '-1',
            'uom_symbol'               => '',
            'relation_id'              => $element->id,
            'level'                    => 0,
            'linked'                   => false,
            'rate_after_markup'        => 0,
            'grand_total_after_markup' => 0,
            '_csrf_token'              => $form->getCSRFToken()
        );

        foreach ( $contractorRates as $contractorId => $rates )
        {
            $defaultLastRow[$contractorId . '-rate_after_markup']        = 0;
            $defaultLastRow[$contractorId . '-grand_total_after_markup'] = 0;
        }

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeBillItemRateUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id'))
        );

        $item       = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));
        $fieldName  = $request->getParameter('attr_name');
        $fieldValue = trim($request->getParameter('val'));
        $rowData    = array();

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldAttr = explode('-', $fieldName);

            if ( count($fieldAttr) > 1 &&  $fieldAttr[1] == 'rate')
            {
                $this->forward404Unless(
                    $contractor = Doctrine_Core::getTable('Company')->find($fieldAttr[0]) and
                    $tenderCompany = TenderCompanyTable::getByProjectIdAndCompanyId($project->id, $contractor->id)
                );

                if ( !$contractorRate = $tenderCompany->getBillItemRateByBillItemId($item->id) )
                {
                    $contractorRate                    = new TenderBillItemRate();
                    $contractorRate->tender_company_id = $tenderCompany;
                    $contractorRate->bill_item_id      = $item->id;
                }

                $rate                 = (double) $fieldValue;
                $contractorRate->rate = number_format($rate, 2, '.', '');

                $contractorRate->save($con);

                $con->commit();

                $rowData['id']                                          = $item->id;
                $rowData[$contractor->id . '-rate-value']               = $contractorRate->rate;
                $rowData[$contractor->id . '-grand_total_after_markup'] = $contractorRate->grand_total;
            }

            $success = true;

            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeLumpSumPercentageForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $tenderCompany = DoctrineQuery::create()->select('tc.id, tc.project_structure_id, tc.company_id')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $project->id)
            ->andWhere('tc.company_id = ?', $company->id)
            ->fetchOne();

        $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));

        if ( !$billItemRate = $tenderCompany->getBillItemRateByBillItemId($billItem->id) )
        {
            $billItemRate                    = new TenderBillItemRate();
            $billItemRate->tender_company_id = $tenderCompany->id;
            $billItemRate->bill_item_id      = $billItem->id;
            $billItemRate->save();
        }

        $form = new TenderBillItemLumpSumPercentageForm($billItemRate->LumpSumPercentage);

        return $this->renderJson(array(
            'tender_bill_item_lump_sum_percentage[tender_bill_item_rate_id]' => $form->getObject()->id,
            'tender_bill_item_lump_sum_percentage[rate]'                     => number_format($form->getObject()->rate, 2, '.', ''),
            'tender_bill_item_lump_sum_percentage[percentage]'               => number_format($form->getObject()->percentage, 2, '.', ''),
            'tender_bill_item_lump_sum_percentage[amount]'                   => number_format($form->getObject()->amount, 2, '.', ''),
            'tender_bill_item_lump_sum_percentage[_csrf_token]'              => $form->getCSRFToken()
        ));
    }

    public function executeLumpSumPercentageUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $tenderCompany = DoctrineQuery::create()->select('tc.id, tc.project_structure_id, tc.company_id')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $project->id)
            ->andWhere('tc.company_id = ?', $company->id)
            ->fetchOne();

        $billItem     = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));
        $billItemRate = $tenderCompany->getBillItemRateByBillItemId($billItem->id);

        $form = new TenderBillItemLumpSumPercentageForm($billItemRate->LumpSumPercentage);

        if ( $this->isFormValid($request, $form) )
        {
            $lumpSumPercentage = $form->save();

            $rowData['id']                                       = $billItem->id;
            $rowData[$company->id . '-rate-value']               = $lumpSumPercentage->TenderBillItemRate->rate;
            $rowData[$company->id . '-grand_total_after_markup'] = $lumpSumPercentage->TenderBillItemRate->grand_total;

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $rowData = array();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'data' => $rowData ));
    }

    public function executePrimeCostRateForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $tenderCompany = DoctrineQuery::create()->select('tc.id, tc.project_structure_id, tc.company_id')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $project->id)
            ->andWhere('tc.company_id = ?', $company->id)
            ->fetchOne();

        $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));

        if ( !$billItemRate = $tenderCompany->getBillItemRateByBillItemId($billItem->id) )
        {
            $billItemRate                    = new TenderBillItemRate();
            $billItemRate->tender_company_id = $tenderCompany->id;
            $billItemRate->bill_item_id      = $billItem->id;
            $billItemRate->save();
        }

        $form = new TenderBillItemPrimeCostRateForm($billItemRate->PrimeCostRate);

        return $this->renderJson(array(
            'tender_bill_item_prime_cost_rate[supply_rate]'             => number_format($form->getObject()->supply_rate, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[wastage_percentage]'      => number_format($form->getObject()->wastage_percentage, 3, '.', ''),
            'tender_bill_item_prime_cost_rate[wastage_amount]'          => number_format($form->getObject()->wastage_amount, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[labour_for_installation]' => number_format($form->getObject()->labour_for_installation, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[other_cost]'              => number_format($form->getObject()->other_cost, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[profit_percentage]'       => number_format($form->getObject()->profit_percentage, 3, '.', ''),
            'tender_bill_item_prime_cost_rate[profit_amount]'           => number_format($form->getObject()->profit_amount, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[total]'                   => number_format($form->getObject()->total, 2, '.', ''),
            'tender_bill_item_prime_cost_rate[_csrf_token]'             => $form->getCSRFToken()
        ));
    }

    public function executePrimeCostRateUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $tenderCompany = DoctrineQuery::create()->select('tc.id, tc.project_structure_id, tc.company_id')
            ->from('TenderCompany tc')
            ->where('tc.project_structure_id = ?', $project->id)
            ->andWhere('tc.company_id = ?', $company->id)
            ->fetchOne();

        $billItem = Doctrine_Core::getTable('BillItem')->find($request->getParameter('id'));

        if ( !$billItemRate = $tenderCompany->getBillItemRateByBillItemId($billItem->id) )
        {
            $billItemRate                    = new TenderBillItemRate();
            $billItemRate->tender_company_id = $tenderCompany->id;
            $billItemRate->bill_item_id      = $billItem->id;
            $billItemRate->save();
        }

        $form = new TenderBillItemPrimeCostRateForm($billItemRate->PrimeCostRate);

        if ( $this->isFormValid($request, $form) )
        {
            $primeCostRate = $form->save();

            $rowData['id']                                       = $billItem->id;
            $rowData[$company->id . '-rate-value']               = $primeCostRate->TenderBillItemRate->rate;
            $rowData[$company->id . '-grand_total_after_markup'] = $primeCostRate->TenderBillItemRate->grand_total;

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $rowData = array();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'data' => $rowData ));
    }

    public function executeRefreshContractorRates(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('cid')) and
            $tenderCompanyInfo = TenderCompanyTable::getByProjectIdAndCompanyId($project->id, $company->id)
        );

        $success  = null;
        $errorMsg = null;

        $con = $tenderCompanyInfo->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $tenderCompanyInfo->refreshContractorRates();

            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeGetTenderInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $form = new BaseForm();

        $tenderSetting = $project->TenderSetting->toArray();

        $data['tender_setting'] = $tenderSetting;

        switch ($tenderSetting['contractor_sort_by'])
        {
            case TenderSetting::SORT_CONTRACTOR_NAME_ASC:
                $sqlOrder = "c.name ASC";
                break;
            case TenderSetting::SORT_CONTRACTOR_NAME_DESC:
                $sqlOrder = "c.name DESC";
                break;
            case TenderSetting::SORT_CONTRACTOR_HIGHEST_LOWEST:
                $sqlOrder = "total DESC";
                break;
            case TenderSetting::SORT_CONTRACTOR_LOWEST_HIGHEST:
                $sqlOrder = "total ASC";
                break;
            default:
                throw new Exception('invalid sort option');
        }

        $awardedCompanyId = $tenderSetting['awarded_company_id'] > 0 ? $tenderSetting['awarded_company_id'] : - 1;

        $stmt = $pdo->prepare("SELECT c.id, c.name, xref.show, COALESCE(SUM(r.grand_total), 0) AS total
        FROM " . CompanyTable::getInstance()->getTableName() . " c
        JOIN " . TenderCompanyTable::getInstance()->getTableName() . " xref ON xref.company_id = c.id
        LEFT JOIN " . TenderBillElementGrandTotalTable::getInstance()->getTableName() . " r ON r.tender_company_id = xref.id
        WHERE xref.project_structure_id = " . $project->id . "
        AND c.id <> " . $awardedCompanyId . " AND xref.show IS TRUE
        AND c.deleted_at IS NULL GROUP BY c.id, xref.show ORDER BY " . $sqlOrder);

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $companies = array();

        if ( $tenderSetting['awarded_company_id'] > 0 )
        {
            $awardedCompany = $project->TenderSetting->AwardedCompany;

            $companySetting = DoctrineQuery::create()->select('s.id, s.show')
                ->from('TenderCompany s')
                ->where('s.company_id = ?', $awardedCompany->id)
                ->andWhere('s.project_structure_id = ?', $project->id)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();

            if ( $companySetting['show'] )
            {
                array_push($companies, array(
                    'id'          => $awardedCompany->id,
                    'name'        => $awardedCompany->name,
                    'show'        => $companySetting['show'],
                    '_csrf_token' => $form->getCSRFToken(),
                    'awarded'     => true
                ));
            }

            unset( $awardedCompany );
        }

        foreach ( $records as $key => $record )
        {
            $record['_csrf_token'] = $form->getCSRFToken();
            $record['awarded']     = false;

            array_push($companies, $record);
            unset( $records[$key], $record );
        }

        $data['tender_companies'] = $companies;

        return $this->renderJson($data);
    }
    
    public function executeHistoricalRateList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest()
        );
        
        $projectTitle = $request->getParameter('pt');
        $billItemDesc = $request->getParameter('bid');
        $records = array();
        
        if(strlen($billItemDesc) > 0)
        {
            $pdo = BillItemTable::getInstance()->getConnection()->getDbh();

            $projectTitleSql = strlen($projectTitle) > 0 ? "AND LOWER(mainInfo.title) LIKE '%".pg_escape_string(strtolower($projectTitle))."%'" : "";
            
            $stmt = $pdo->prepare("SELECT DISTINCT project.id AS project_id, postContract.project_structure_id AS post_contract_project_id, postContract.selected_type_rate AS post_contract_selected_type_rate, postContract.published_at, tc.id AS tender_company_id, mainInfo.title AS project_title, p.id, p.element_id, p.description, p.type, rate.id AS rate_id, ROUND(COALESCE(rate.rate,0), 2) AS rate, p.priority, p.lft, p.level,
            p.grand_total_quantity, p.grand_total, p.bill_ref_element_no, p.bill_ref_page_no, p.bill_ref_char,
            uom.id AS uom_id, uom.symbol AS uom_symbol, p.note, p.project_revision_id, p.project_revision_deleted_at,
            r.version, e.description AS element_description, bill.id AS bill_id, bill_setting.title AS bill_title, e.priority as element_priority, bill.priority AS bill_priority, bill.lft AS bill_lft, bill.level AS bill_level, project.priority AS project_priority
            FROM " . BillItemTable::getInstance()->getTableName() . " p
            JOIN " . PostContractBillItemRateTable::getInstance()->getTableName() . " rate ON rate.bill_item_id = p.id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON (p.uom_id = uom.id AND uom.deleted_at IS NULL)
            JOIN " . ProjectRevisionTable::getInstance()->getTableName() . " r ON (p.project_revision_id = r.id)
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON (p.element_id = e.id AND e.deleted_at IS NULL)
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON e.project_structure_id = bill.id AND bill.type = ".ProjectStructure::TYPE_BILL." AND bill.deleted_at IS NULL
            JOIN " . BillSettingTable::getInstance()->getTableName() . " bill_setting ON bill_setting.project_structure_id = bill.id AND bill.type = ".ProjectStructure::TYPE_BILL." AND bill_setting.deleted_at IS NULL
            JOIN " . ProjectStructureTable::getInstance()->getTableName() ." project ON bill.root_id = project.id AND project.type = ".ProjectStructure::TYPE_ROOT." AND project.deleted_at IS NULL
            JOIN " . ProjectMainInformationTable::getInstance()->getTableName() . " mainInfo on project.id = mainInfo.project_structure_id AND mainInfo.status = ".ProjectMainInformation::STATUS_POSTCONTRACT." AND mainInfo.deleted_at IS NULL
            JOIN " . PostContractTable::getInstance()->getTableName() . " postContract ON postContract.project_structure_id = project.id
            LEFT JOIN ". TenderSettingTable::getInstance()->getTableName() ." ts ON ts.project_structure_id = project.id AND ts.deleted_at IS NULL
            LEFT JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.company_id = ts.awarded_company_id AND tc.project_structure_id = ts.project_structure_id
            WHERE LOWER(p.description) LIKE '%" .pg_escape_string(strtolower($billItemDesc)). "%' ".$projectTitleSql."
            AND rate.rate <> 0
            AND p.root_id = p.root_id AND p.type <> " . BillItem::TYPE_HEADER . " AND p.type <> " . BillItem::TYPE_HEADER_N . " AND p.type <> " . BillItem::TYPE_NOID . "
            AND project.deleted_at IS NULL AND e.deleted_at IS NULL AND bill_setting.deleted_at IS NULL AND bill.deleted_at IS NULL
            AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL AND r.deleted_at IS NULL
            ORDER BY project_priority, bill_priority, bill_lft, bill_level, element_priority, p.priority, p.lft, p.level");
                
            $stmt->execute();

            $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $currentProjectId = null;
            $currentBillElementId = null;
                  
            foreach($billItems as $key => $billItem)
            {
                $billItems[$key]['type'] = (string)$billItem['type'];
                $billItems[$key]['uom_id'] = (string)$billItem['uom_id'];
                $billItems[$key]['level'] = 2;
                $billItems[$key]['published_at'] = date('d/m/Y', strtotime($billItem['published_at']));

                if($currentProjectId != $billItem['project_id'])
                {
                      array_push($records, array(
                        'id' => 'pt-'.$billItem['project_id']."-".$billItem['id'],
                        'description' => $billItem['project_title'],
                        'type' => "-5",
                        'uom_id' => "",
                        'uom_symbol' => "",
                        'rate' => 0,
                        'published_at' => "",
                        'level' => 0
                      ));
                      
                      $currentProjectId = $billItem['project_id'];
                }
                
                if($currentBillElementId != $currentProjectId."-".$billItem['bill_id']."-".$billItem['element_id'])
                {
                    $currentBillElementId = $currentProjectId."-".$billItem['bill_id']."-".$billItem['element_id'];
                    
                    array_push($records, array(
                        'id' => 'be-'.$currentBillElementId."-".$billItem['id'],
                        'description' => $billItem['bill_title'].'<span style="color:yellow;"> > </span>'.$billItem['element_description'],
                        'type' => "-4",
                        'uom_id' => "",
                        'uom_symbol' => "",
                        'rate' => 0,
                        'published_at' => "",
                        'level' => 1
                    ));
                }
                
                if($billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED)
                {
                    $tenderNotListedItem = null;
                    
                    if($billItem['post_contract_selected_type_rate'] == PostContract::RATE_TYPE_CONTRACTOR and $billItem['tender_company_id'])
                    {
                        $stmt = $pdo->prepare("SELECT tnl.id, tnl.bill_item_id, tnl.description, uom_not_listed.id AS uom_id, uom_not_listed.symbol AS uom_symbol FROM " . TenderBillItemNotListedTable::getInstance()->getTableName() . " tnl
                            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom_not_listed ON tnl.uom_id = uom_not_listed.id
                            WHERE tnl.bill_item_id = ".$billItem['id']." AND tnl.tender_company_id = " . $billItem['tender_company_id']);
                            
                        $stmt->execute();
                        
                        $tenderNotListedItem = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    else if($billItem['post_contract_selected_type_rate'] == PostContract::RATE_TYPE_RATIONALIZED)
                    {
                        $stmt = $pdo->prepare("SELECT tnl.id, tnl.bill_item_id, tnl.description, uom_not_listed.id AS uom_id, uom_not_listed.symbol AS uom_symbol FROM " . TenderBillItemNotListedRationalizedTable::getInstance()->getTableName() . " tnl
                            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom_not_listed ON tnl.uom_id = uom_not_listed.id
                            WHERE tnl.bill_item_id = ".$billItem['id']);
                            
                        $stmt->execute();
                        
                        $tenderNotListedItem = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    
                    if($tenderNotListedItem)
                    {
                        $billItems[$key]['description'] = $tenderNotListedItem['description'];
                        $billItems[$key]['uom_symbol'] = $tenderNotListedItem['uom_symbol'];
                        $billItems[$key]['uom_id'] = (string)$tenderNotListedItem['uom_id'];
                    }
                }
                
                array_push($records, $billItems[$key]);
                
                unset($billItems[$key]);
            }
            
        }
        
        array_push($records, array(
            'id' => Constants::GRID_LAST_ROW,
            'description' => "",
            'uom_id' => '1',
            'uom_symbol' => "",
            'level' => 0,
            'type' => (string)BillItem::TYPE_WORK_ITEM,
            'rate' => 0,
            'published_at' => ""
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }
    
    public function executeGetHistoricalItemInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $billItem = BillItemTable::getInstance()->find($request->getParameter('id'))
        );
        
        $bill = $billItem->Element->ProjectStructure;
        
        $columns = array();
        
        foreach($bill->BillColumnSettings as $columnSetting)
        {
            array_push($columns, array(
                'id'   => $columnSetting->id,
                'name' => $columnSetting->name,
                'qty'  => $columnSetting->quantity
            ));
        }
        
        $items = DoctrineQuery::create()->select('i.id, i.description, i.type, i.lft, i.level')
            ->from('BillItem i')
            ->andWhere('i.root_id = ?', $billItem->root_id)
            ->andWhere('i.lft <= ? AND i.rgt >= ?', array( $billItem->lft, $billItem->rgt ))
            ->andWhere('i.project_revision_deleted_at IS NULL')
            ->addOrderBy('i.lft')
            ->fetchArray();

        foreach ( $items as $idx => $item )
        {
            $items[$idx]['type'] = (string) $item['type'];

            unset( $item );
        }
        
        return $this->renderJson(array(
            'columns' => $columns,
            'items'   => $items
        ));
    }
    
    public function executeHistoricalRateUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $postContractRate = PostContractBillItemRateTable::getInstance()->find($request->getParameter('rid')) and
            $targetBillItem = BillItemTable::getInstance()->find($request->getParameter('tid'))
        );

        $rate = 0;

        $con = $postContractRate->getTable()->getConnection();

        try
        {
            $con->beginTransaction();
            
            $historicalRate = $targetBillItem->HistoricalRate;
            
            $historicalRate->rate = $postContractRate->rate;
            
            $historicalRate->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;
            
            $rate = $historicalRate->rate;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'rate' => $rate ));
    }

    public function executeGetTendererLogCount(sfWebRequest $request)
    {
        $this->forward404Unless(
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $company = CompanyTable::getInstance()->find($request->getParameter('cid'))
        );

        $tenderCompany = DoctrineQuery::create()->select('s.id')
            ->from('TenderCompany s')
            ->where('s.company_id = ?', $company->id)
            ->andWhere('s.project_structure_id = ?', $project->id)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        $pdo = BillItemTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT r.id, r.revision, COALESCE(MAX(l.changes_count), 0) AS nol
            FROM ".TenderBillItemRateLogTable::getInstance()->getTableName()." l
            JOIN ".ProjectRevisionTable::getInstance()->getTableName()." r ON l.project_revision_id = r.id
            JOIN ".ProjectStructureTable::getInstance()->getTableName()." b ON r.project_structure_id = b.root_id
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.project_structure_id = b.id
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.element_id = e.id AND l.bill_item_id = i.id
            WHERE l.tender_company_id = ".$tenderCompany['id']." AND r.project_structure_id = ".$project->id."
            AND r.deleted_at IS NULL AND b. deleted_at IS NULL AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            GROUP BY l.tender_company_id, r.id
            ORDER BY r.version");

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(empty($data))
        {
            $data[] = array(
                'id' => Constants::GRID_LAST_ROW,
                'revision' => '-',
                'nol' => 0
            );
        }

        return $this->renderJson($data);
    }

    public function executeGetTendererLogProjectBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $company = CompanyTable::getInstance()->find($request->getParameter('cid'))
        );

        $projectRevision = null;

        if($request->getParameter('prid') != Constants::GRID_LAST_ROW) $projectRevision = ProjectRevisionTable::getInstance()->find($request->getParameter('prid'));

        if(!$projectRevision)
        {
            return $this->renderJson(array(
                'identifier' => 'id',
                'items'      => array(array(
                    'id'                             => Constants::GRID_LAST_ROW,
                    'title'                          => "",
                    'type'                           => 1,
                    'level'                          => 0,
                    'count'                          => null,
                    'overall_total_after_markup'     => 0,
                    'rev_overall_total_after_markup' => 0
                ))
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

        $contractorRates = TenderCompanyTable::getBillAmountByContractorIdAndProjectId($company->id, $project->id);

        $logRates = TenderBillItemRateLogTable::getBillTotalAmountByCompanyIdProjectIdAndRevision($company, $project, $projectRevision);

        foreach ( $records as $key => $record )
        {
            if ( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[$key]['BillType']) )
            {
                $records[$key]['bill_type']   = $record['BillType']['type'];
                $records[$key]['bill_status'] = $record['BillType']['status'];

                if ( array_key_exists($record['id'], $contractorRates['bills']) )
                {
                    $records[$key]['overall_total_after_markup'] = $contractorRates['bills'][$record['id']];
                }
                else
                {
                    $records[$key]['overall_total_after_markup'] = 0;
                }

                if ( array_key_exists($record['id'], $logRates['bills']) )
                {
                    $records[$key]['rev_overall_total_after_markup'] = $logRates['bills'][$record['id']];
                }
                else
                {
                    $records[$key]['rev_overall_total_after_markup'] = 0;
                }
            }
            else if ( $record['type'] == ProjectStructure::TYPE_ROOT )
            {
                $records[$key]['overall_total_after_markup'] = $contractorRates['project_total'];
                $records[$key]['rev_overall_total_after_markup'] = $logRates['project_total'];
            }

            unset( $records[$key]['BillLayoutSetting'] );
            unset( $records[$key]['BillType'] );
            unset( $records[$key]['BillColumnSettings'] );
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

    public function executeGetTendererLogElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')) and
            $company = CompanyTable::getInstance()->find($request->getParameter('cid')) and
            $projectRevision = ProjectRevisionTable::getInstance()->find($request->getParameter('prid'))
        );

        $elements = DoctrineQuery::create()->select('e.id, e.description')
            ->from('BillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $elementsGrandTotal = TenderCompanyTable::getElementsAmountByContractorIdAndBillId($company->id, $bill->id);
        $logsGrandTotal = TenderBillItemRateLogTable::getElementGrandTotalByCompanyIdBillIdAndRevision($company, $bill, $projectRevision);

        foreach ( $elements as $key => $element )
        {
            if ( array_key_exists($element['id'], $elementsGrandTotal['elements']) )
            {
                $elements[$key]['overall_total_after_markup'] = $elementsGrandTotal['elements'][$element['id']];
            }
            else
            {
                $elements[$key]['overall_total_after_markup'] = 0;
            }

            if ( array_key_exists($element['id'], $logsGrandTotal['elements']) )
            {
                $elements[$key]['rev_overall_total_after_markup'] = $logsGrandTotal['elements'][$element['id']];
            }
            else
            {
                $elements[$key]['rev_overall_total_after_markup'] = 0;
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

    public function executeGetTendererLogItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('eid')) and
            $company = CompanyTable::getInstance()->find($request->getParameter('cid')) and
            $projectRevision = ProjectRevisionTable::getInstance()->find($request->getParameter('prid'))
        );

        $bill         = $element->ProjectStructure;
        $pdo          = $element->getTable()->getConnection()->getDbh();
        $pageNoPrefix = $bill->BillLayoutSetting->page_no_prefix;

        list(
            $billItems, $formulatedColumns, $quantityPerUnitByColumns,
            $billItemTypeReferences, $billItemTypeRefFormulatedColumns
            ) = BillItemTable::getDataStructureForBillItemList($element, $bill);

        $contractorRates = TenderCompanyTable::getContractorRatesByElementIdAndContractorIds($element->id, json_encode(array($company->id)));

        $billItemLogRates      = TenderBillItemRateLogTable::getBillItemRatesByCompanyIdProjectIdAndRevision($company, $bill->getRoot(), $projectRevision);
        $billItemLogGrandTotal = TenderBillItemRateLogTable::getBillItemGrandTotalByCompanyIdProjectIdAndRevision($company, $bill->getRoot(), $projectRevision);

        foreach ( $billItems as $key => $billItem )
        {
            $billItems[$key]['bill_ref']                    = BillItemTable::generateBillRef($pageNoPrefix, $billItem['bill_ref_element_no'], $billItem['bill_ref_page_no'], $billItem['bill_ref_char']);
            $billItems[$key]['type']                        = (string) $billItem['type'];
            $billItems[$key]['uom_id']                      = $billItem['uom_id'] > 0 ? (string) $billItem['uom_id'] : '-1';
            $billItems[$key]['project_revision_deleted_at'] = is_null($billItem['project_revision_deleted_at']) ? false : $billItem['project_revision_deleted_at'];

            if ( $billItem['type'] == BillItem::TYPE_ITEM_NOT_LISTED)
            {
                //Get Bill Item Not Listed From Contractors
                $stmt = $pdo->prepare("SELECT inl.id, inl.description, uom.id AS uom_id, uom.symbol AS uom_symbol
                    FROM " . TenderBillItemNotListedTable::getInstance()->getTableName() . " inl
                    JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON inl.uom_id = uom.id AND uom.deleted_at IS NULL
                    JOIN " . TenderCompanyTable::getInstance()->getTableName() . " tc ON tc.id = inl.tender_company_id
                    JOIN " . TenderBillItemRateTable::getInstance()->getTableName() . " r ON r.tender_bill_item_not_listed_id = inl.id
                    WHERE inl.bill_item_id = " . $billItem['id'] . " AND tc.company_id = ".$company->id." AND tc.project_structure_id =".$bill->root_id);

                $stmt->execute();

                $billItemNotListed = $stmt->fetch(PDO::FETCH_ASSOC);

                if($billItemNotListed)
                {
                    $billItems[$key]['description'] = $billItemNotListed['description'];
                    $billItems[$key]['uom_id']      = $billItemNotListed['uom_id'] ? (string)$billItemNotListed['uom_id'] : '-1';
                    $billItems[$key]['uom_symbol']  = $billItemNotListed['uom_symbol'];
                }
            }

            if ( array_key_exists($billItem['id'], $contractorRates[$company->id]) )
            {
                $billItems[$key]['rate_after_markup'] = $contractorRates[$company->id][$billItem['id']][0]['rate'];
                $billItems[$key]['grand_total_after_markup'] = $contractorRates[$company->id][$billItem['id']][0]['grand_total'];

                unset($contractorRates[$company->id][$billItem['id']] );
            }
            else
            {
                $billItems[$key]['rate_after_markup']        = 0;
                $billItems[$key]['grand_total_after_markup'] = 0;
            }

            if ( array_key_exists($billItem['id'], $billItemLogRates) )
            {
                $billItems[$key]['rev_rate_after_markup'] = $billItemLogRates[$billItem['id']];
                unset($billItemLogRates[$billItem['id']] );
            }
            else
            {
                $billItems[$key]['rev_rate_after_markup'] = 0;
            }

            if ( array_key_exists($billItem['id'], $billItemLogGrandTotal) )
            {
                $billItems[$key]['rev_grand_total_after_markup'] = $billItemLogGrandTotal[$billItem['id']];
                unset($billItemLogGrandTotal[$billItem['id']] );
            }
            else
            {
                $billItems[$key]['rev_grand_total_after_markup'] = 0;
            }
        }

        $defaultLastRow = array(
            'id'                           => Constants::GRID_LAST_ROW,
            'bill_ref'                     => '',
            'description'                  => '',
            'type'                         => (string) ProjectStructure::getDefaultItemType($bill->BillType->type),
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

    public function executeGetSupplyOfMaterialElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $elements = DoctrineQuery::create()->select('e.id, e.description, e.note')
            ->from('SupplyOfMaterialElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        $contractorTotalRates = TenderCompanyTable::getAllDisplayedContractorSupplyOfMaterialElementGrandTotalByBillId($bill->id);

        foreach ( $elements as $key => $element )
        {
            foreach($contractorTotalRates as $contractorTotalRate)
            {
                if ($element['id'] == $contractorTotalRate['element_id'])
                {
                    $elements[$key][$contractorTotalRate['company_id'] . '-total'] = $contractorTotalRate['total'];
                }
            }
            $elements[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        unset($contractorTotalRates);

        array_push($elements, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'relation_id' => $bill->id,
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetSupplyOfMaterialItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = SupplyOfMaterialElementTable::getInstance()->find($request->getParameter('id'))
        );

        $pdo  = $element->getTable()->getConnection()->getDbh();
        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.supply_rate, i.contractor_supply_rate,
            i.estimated_qty, i.percentage_of_wastage, i.difference, i.amount, uom.id AS uom_id, uom.symbol AS uom_symbol, i.note
            FROM " . SupplyOfMaterialItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE i.element_id = " . $element->id . " AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $contractorSupplyRates = TenderCompanyTable::getDisplayedContractorSupplyOfMaterialsByElementId($element->id);

        foreach ( $items as $key => $item )
        {
            $items[$key]['type']        = (string) $item['type'];
            $items[$key]['uom_id']      = $item['uom_id'] > 0 ? (string) $item['uom_id'] : '-1';
            $items[$key]['relation_id'] = $element->id;
            $items[$key]['linked']      = false;
            $items[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach ( $contractorSupplyRates as $contractorId => $supplyRates )
            {
                if ( array_key_exists($item['id'], $supplyRates) )
                {
                    $items[$key][$contractorId . '-contractor_supply_rate'] = $supplyRates[$item['id']][0]['contractor_supply_rate'];
                    $items[$key][$contractorId . '-estimated_qty']          = $supplyRates[$item['id']][0]['estimated_qty'];
                    $items[$key][$contractorId . '-percentage_of_wastage']  = $supplyRates[$item['id']][0]['percentage_of_wastage'];
                    $items[$key][$contractorId . '-difference']             = $supplyRates[$item['id']][0]['difference'];
                    $items[$key][$contractorId . '-amount']                 = $supplyRates[$item['id']][0]['amount'];

                    unset( $supplyRates, $contractorSupplyRates[$contractorId][$item['id']] );
                }
                else
                {
                    $items[$key][$contractorId . '-contractor_supply_rate'] = 0;
                    $items[$key][$contractorId . '-estimated_qty']          = 0;
                    $items[$key][$contractorId . '-percentage_of_wastage']  = 0;
                    $items[$key][$contractorId . '-difference']             = 0;
                    $items[$key][$contractorId . '-amount']                 = 0;
                }
            }

            unset(
            $items[$key]['contractor_supply_rate'],
            $items[$key]['estimated_qty'],
            $items[$key]['percentage_of_wastage'],
            $items[$key]['difference'],
            $items[$key]['amount']
            );
        }

        $defaultLastRow = array(
            'id'                     => Constants::GRID_LAST_ROW,
            'description'            => '',
            'type'                   => (string)SupplyOfMaterialItem::TYPE_WORK_ITEM,
            'uom_id'                 => '-1',
            'uom_symbol'             => '',
            'relation_id'            => $element->id,
            'level'                  => 0,
            'supply_rate'            => '',
            'contractor_supply_rate' => 0,
            'estimated_qty'          => 0,
            'percentage_of_wastage'  => 0,
            'difference'             =>  0,
            'amount'                 => 0,
            '_csrf_token'            => $form->getCSRFToken()
        );

        foreach ( $contractorSupplyRates as $contractorId => $supplyRates )
        {
            $defaultLastRow[$contractorId . '-contractor_supply_rate'] = 0;
            $defaultLastRow[$contractorId . '-estimated_qty']          = 0;
            $defaultLastRow[$contractorId . '-percentage_of_wastage']  = 0;
            $defaultLastRow[$contractorId . '-difference']             = 0;
            $defaultLastRow[$contractorId . '-amount']                 = 0;
        }

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeSupplyOfMaterialItemUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $item = SupplyOfMaterialItemTable::getInstance()->find($request->getParameter('id'))
        );

        $fieldName  = $request->getParameter('attr_name');
        $fieldValue = trim($request->getParameter('val'));
        $rowData    = array();

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldAttr = explode('-', $fieldName);

            if ( count($fieldAttr) > 1 &&  in_array($fieldAttr[1], array('contractor_supply_rate', 'estimated_qty', 'percentage_of_wastage', 'difference', 'amount')))
            {
                $this->forward404Unless(
                    $contractor = CompanyTable::getInstance()->find($fieldAttr[0]) and
                    $tenderCompany = TenderCompanyTable::getByProjectIdAndCompanyId($project->id, $contractor->id)
                );

                if ( !$contractorRate = $tenderCompany->getTenderSupplyOfMaterialRateByItemId($item->id) )
                {
                    $contractorRate                             = new TenderSupplyOfMaterialRate();
                    $contractorRate->tender_company_id          = $tenderCompany;
                    $contractorRate->supply_rate                = $item->supply_rate;
                    $contractorRate->supply_of_material_item_id = $item->id;
                }

                $contractorRate->{'set' . sfInflector::camelize($fieldAttr[1])}(number_format((double) $fieldValue, 2, '.', ''));

                $contractorRate->save($con);

                $con->commit();

                $contractorRate->refresh();

                $rowData['id']                                        = $item->id;
                $rowData[$contractor->id . '-contractor_supply_rate'] = $contractorRate->contractor_supply_rate;
                $rowData[$contractor->id . '-estimated_qty']          = $contractorRate->estimated_qty;
                $rowData[$contractor->id . '-percentage_of_wastage']  = $contractorRate->percentage_of_wastage;
                $rowData[$contractor->id . '-difference']             = $contractorRate->difference;
                $rowData[$contractor->id . '-amount']                 = $contractorRate->amount;
            }

            $success = true;

            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeGetScheduleOfRateBillElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('id'))
        );

        $elements = DoctrineQuery::create()->select('e.id, e.description, e.note')
            ->from('ScheduleOfRateBillElement e')
            ->where('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach ( $elements as $key => $element )
        {
            $elements[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($elements, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'relation_id' => $bill->id,
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetScheduleOfRateBillItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = ScheduleOfRateBillElementTable::getInstance()->find($request->getParameter('id'))
        );

        $pdo  = $element->getTable()->getConnection()->getDbh();
        $form = new BaseForm();

        $stmt = $pdo->prepare("SELECT i.id, i.description, i.type, i.lft, i.level, i.estimation_rate, i.contractor_rate,
            i.difference, uom.id AS uom_id, uom.symbol AS uom_symbol, i.note
            FROM " . ScheduleOfRateBillItemTable::getInstance()->getTableName() . " i
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON i.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE i.element_id = " . $element->id . " AND i.deleted_at IS NULL
            ORDER BY i.priority, i.lft, i.level");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $contractorRates = TenderCompanyTable::getDisplayedContractorScheduleOfRatesByElementId($element->id);

        foreach ( $items as $key => $item )
        {
            $items[$key]['type']        = (string) $item['type'];
            $items[$key]['uom_id']      = $item['uom_id'] > 0 ? (string) $item['uom_id'] : '-1';
            $items[$key]['relation_id'] = $element->id;
            $items[$key]['linked']      = false;
            $items[$key]['_csrf_token'] = $form->getCSRFToken();

            foreach ( $contractorRates as $contractorId => $rates )
            {
                if ( array_key_exists($item['id'], $rates) )
                {
                    $items[$key][$contractorId . '-contractor_rate'] = $rates[$item['id']][0]['contractor_rate'];
                    $items[$key][$contractorId . '-difference']      = $rates[$item['id']][0]['difference'];

                    unset( $rates, $contractorRates[$contractorId][$item['id']] );
                }
                else
                {
                    $items[$key][$contractorId . '-contractor_rate'] = 0;
                    $items[$key][$contractorId . '-difference']      = 0;
                }
            }

            unset($items[$key]['contractor_rate'], $items[$key]['difference']);
        }

        $defaultLastRow = array(
            'id'              => Constants::GRID_LAST_ROW,
            'description'     => '',
            'type'            => (string)ScheduleOfRateBillItem::TYPE_WORK_ITEM,
            'uom_id'          => '-1',
            'uom_symbol'      => '',
            'relation_id'     => $element->id,
            'level'           => 0,
            'estimation_rate' => '',
            'contractor_rate' => 0,
            'difference'      =>  0,
            '_csrf_token'     => $form->getCSRFToken()
        );

        foreach ( $contractorRates as $contractorId => $rates )
        {
            $defaultLastRow[$contractorId . '-contractor_supply_rate'] = 0;
            $defaultLastRow[$contractorId . '-estimated_qty']          = 0;
            $defaultLastRow[$contractorId . '-percentage_of_wastage']  = 0;
            $defaultLastRow[$contractorId . '-difference']             = 0;
            $defaultLastRow[$contractorId . '-amount']                 = 0;
        }

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeScheduleOfRateBillItemUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('pid')) and
            $item = ScheduleOfRateBillItemTable::getInstance()->find($request->getParameter('id'))
        );

        $fieldName  = $request->getParameter('attr_name');
        $fieldValue = trim($request->getParameter('val'));
        $rowData    = array();

        $con = $item->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldAttr = explode('-', $fieldName);

            if ( count($fieldAttr) > 1 &&  in_array($fieldAttr[1], array('contractor_rate')))
            {
                $this->forward404Unless(
                    $contractor = CompanyTable::getInstance()->find($fieldAttr[0]) and
                    $tenderCompany = TenderCompanyTable::getByProjectIdAndCompanyId($project->id, $contractor->id)
                );

                if ( !$contractorRate = $tenderCompany->getTenderScheduleOfRateByItemId($item->id) )
                {
                    $contractorRate                                = new TenderScheduleOfRate();
                    $contractorRate->tender_company_id             = $tenderCompany;
                    $contractorRate->estimation_rate               = $item->estimation_rate;
                    $contractorRate->schedule_of_rate_bill_item_id = $item->id;
                }

                $contractorRate->{'set' . sfInflector::camelize($fieldAttr[1])}(number_format((double) $fieldValue, 2, '.', ''));

                $contractorRate->save($con);

                $con->commit();

                $contractorRate->refresh();

                $rowData['id']                                 = $item->id;
                $rowData[$contractor->id . '-contractor_rate'] = $contractorRate->contractor_rate;
                $rowData[$contractor->id . '-difference']      = $contractorRate->difference;
            }

            $success = true;

            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    /**
     * Validates the imported rates file.
     *
     * @param $project
     * @param $xmlData
     *
     * @throws Exception
     */
    protected function validateRatesFile($project, $xmlData)
    {
        $this->validateVersion($project, $xmlData);

        $this->validateUniqueId($project, $xmlData);

        $this->validateExportType($xmlData);
    }

    /**
     * Validates unique id of the imported rates file.
     *
     * @param $project
     * @param $xmlData
     *
     * @throws Exception
     */
    protected function validateUniqueId($project, $xmlData)
    {
        if( $project->MainInformation->unique_id != $xmlData->attributes()->uniqueId )
        {
            throw new Exception(ProjectMainInformation::ERROR_MSG_WRONG_PROJECT_RATES);
        }
    }

    /**
     * Validates the export type of the imported rates file.
     *
     * @param $xmlData
     *
     * @throws Exception
     */
    protected function validateExportType($xmlData)
    {
        if( $xmlData->attributes()->exportType != ExportedFile::EXPORT_TYPE_RATES )
        {
            throw new Exception(ExportedFile::ERROR_MSG_WRONG_RATES_FILE);
        }
    }

    /**
     * Validates the version of the imported rates file.
     *
     * @param $project
     * @param $xmlData
     *
     * @throws Exception
     */
    protected function validateVersion($project, $xmlData)
    {
        $importedFileRevisions = $xmlData->{sfBuildspaceExportProjectXML::TAG_REVISIONS};

        // Rates files without revision data are not allowed to be imported.
        if( count($importedFileRevisions) <= 0 )
        {
            throw new Exception(ExportedFile::ERROR_MSG_OUTDATED_RATES_FILE);
        }

        if( ! $this->ratesFileHasCorrectVersion($project, $xmlData) )
        {
            $importRevisionName = (string)$importedFileRevisions->{sfBuildspaceExportProjectXML::TAG_VERSION}->revision;
            $currentRevision = ProjectRevisionTable::getCurrentSelectedProjectRevisionFromBillId($project->id, Doctrine_Core::HYDRATE_ARRAY);

            throw new Exception(ExportedFile::ERROR_MSG_WRONG_RATES_FILE_VERSION . '<br/>' .
                'Current project revision: "' . $currentRevision['revision'] . '"<br/>' .
                'Imported rates\' project revision: "' . $importRevisionName . '"');
        }
    }

    /**
     * Checks if the uploaded rates file is for the current project's revision.
     *
     * @param $currentProject
     * @param $xmlData
     *
     * @return bool
     */
    protected function ratesFileHasCorrectVersion($currentProject, $xmlData)
    {
        $projectId = $currentProject->id;
        $currentRevision = ProjectRevisionTable::getCurrentSelectedProjectRevisionFromBillId($projectId, Doctrine_Core::HYDRATE_ARRAY);

        $importedFileRevisions = $xmlData->{sfBuildspaceExportProjectXML::TAG_REVISIONS};
        $importRevisionVersion = (int)$importedFileRevisions->{sfBuildspaceExportProjectXML::TAG_VERSION}->version;

        $isValid = false;

        if( $currentRevision['version'] == $importRevisionVersion )
        {
            $isValid = true;
        }

        return $isValid;
    }
}