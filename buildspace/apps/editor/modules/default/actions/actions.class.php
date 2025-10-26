<?php

class defaultActions extends BaseActions
{
    public function executeIndex(sfWebRequest $request)
    {
    }

    public function executeGetProjectInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $eproject = Doctrine_Core::getTable('EProjectProject')->find(intval($request->getParameter('pid')))
        );

        $buildspaceProjectMainInfo    = $eproject->BuildspaceProjectMainInfo;
        $callingTenderInformation     = $eproject->getLatestTender()->getCallingTenderInformation();
        $disableTenderRatesSubmission = (!$callingTenderInformation or $callingTenderInformation['disable_tender_rates_submission']);

        $billsWithAddendums           = $buildspaceProjectMainInfo->ProjectStructure->getBillsWithAddendums(false);
        $tenderAlternatives           = $buildspaceProjectMainInfo->ProjectStructure->getTenderAlternativesByLatestLockedRevision(true);
        
        $form = new BaseForm();

        $data = array(
            'id'                              => $buildspaceProjectMainInfo->project_structure_id,
            'title'                           => $buildspaceProjectMainInfo->title,
            'description'                     => $buildspaceProjectMainInfo->description,
            'region'                          => ProjectMainInformation::getCountryNameById($buildspaceProjectMainInfo->region_id),
            'subregion'                       => ProjectMainInformation::getStateNameById($buildspaceProjectMainInfo->subregion_id),
            'work_category'                   => ProjectMainInformation::getWorkCategoryById($buildspaceProjectMainInfo->work_category_id),
            'site_address'                    => $buildspaceProjectMainInfo->site_address,
            'client'                          => $buildspaceProjectMainInfo->client,
            'disable_tender_rates_submission' => $disableTenderRatesSubmission,
            'has_addendum'                    => (int)!empty($billsWithAddendums),
            'has_tender_alternatives'         => (int)!empty($tenderAlternatives),
            'start_date'                      => $buildspaceProjectMainInfo->start_date ? date('Y-m-d', strtotime($buildspaceProjectMainInfo->start_date)) : date('Y-m-d')
        );

        if( $buildspaceProjectMainInfo->currency_id )
        {
            $data['currency'] =$buildspaceProjectMainInfo->Currency->currency_code;
        }

        $data['eProjectReference'] = $eproject->reference;
        $data['isSuperAdmin']      = $this->getUser()->getGuardUser()->getIsSuperAdmin();
        $data['eproject_url']      = sfConfig::get('app_e_project_url')."/projects/".$eproject->id;
        $data['_csrf_token']       = $form->getCSRFToken();

        return $this->renderJson($data);
    }

    public function executeGetProjectBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        //get any deleted addendum bill items and reset the related editor bill items
        $stmt = $pdo->prepare("SELECT info.bill_item_id
            FROM ".EditorBillItemInfoTable::getInstance()->getTableName()." info
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.id = info.bill_item_id
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            JOIN ".ProjectStructureTable::getInstance()->getTableName()." bill ON e.project_structure_id = bill.id
            WHERE bill.root_id = ".$project->id." AND i.project_revision_deleted_at IS NOT NULL
            AND e.deleted_at IS NULL AND bill.deleted_at IS NULL");

        $stmt->execute();

        $addendumItemIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        EditorBillItemInfoTable::resetEditorBillItems($addendumItemIds);

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();

        $company->setInitialEditorBillItemsGrandTotalByProject($project);

        $records = DoctrineQuery::create()->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type < ?', ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL)
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        $count = 0;
        $form  = new BaseForm();

        $projectSumTotal       = $company->getEditorOverallTotalForProject($project->id);
        $billsWithAddendums    = $project->getBillsWithAddendums(false);
        $latestProjectRevision = $project->getLatestLockedProjectRevision();

        foreach($records as $key => $record)
        {
            $records[ $key ]['billLayoutSettingId'] = ( isset( $record['BillLayoutSetting']['id'] ) ) ? $record['BillLayoutSetting']['id'] : null;
            $count                                  = ($record['type'] == ProjectStructure::TYPE_BILL) ? $count + 1 : $count;

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

            $records[ $key ]['count']          = $record['type'] == ProjectStructure::TYPE_BILL ? $count : null;
            $records[ $key ]['overall_total']  = $record['type'] == ProjectStructure::TYPE_BILL ? $company->getEditorOverallTotalByBillId($record['id']) : 0;
            $records[ $key ]['bill_sum_total'] = $projectSumTotal;
            $records[ $key ]['_csrf_token']    = $form->getCSRFToken();

            unset( $records[ $key ]['BillLayoutSetting'] );
            unset( $records[ $key ]['BillType'] );
            unset( $records[ $key ]['BillColumnSettings'] );
        }

        array_push($records, array(
            'id'                  => Constants::GRID_LAST_ROW,
            'title'               => "",
            'type'                => 1,
            'level'               => 0,
            'billLayoutSettingId' => null,
            'is_add_latest_rev'   => 0,
            'addendum_version'    => null,
            'count'               => null,
            'overall_total'       => 0,
            'bill_sum_total'      => 0,
            '_csrf_token'         => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetTenderAlternatives(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();
        
        $pdo = TenderAlternativeTable::getInstance()->getConnection()->getDbh();

        $tenderAlternatives = $project->getTenderAlternativesByLatestLockedRevision(true);

        $form = new BaseForm();

        $items[] = [
            'id'            => -9999,
            'count'         => null,
            'title'         => $project->MainInformation->title,
            'overall_total' => 0,
            'level'         => 0,
            '_csrf_token'   => $form->getCSRFToken()
        ];

        $overallTotal = $company->getOverallTotalForTenderAlternatives($project);

        $count = 1;
        foreach($tenderAlternatives as $idx => $tenderAlternative)
        {
            $tenderAlternatives[$idx]['overall_total'] = (array_key_exists($tenderAlternative['id'], $overallTotal)) ? $overallTotal[$tenderAlternative['id']] : 0;
            $tenderAlternatives[$idx]['level']         = 1;
            $tenderAlternatives[$idx]['count']         = $count++;
            $tenderAlternatives[$idx]['_csrf_token']   = $form->getCSRFToken();

            $items[] = $tenderAlternatives[$idx];
        }

        $items[] = [
            'id'            => Constants::GRID_LAST_ROW,
            'count'         => null,
            'title'         => "",
            'overall_total' => 0,
            'level'         => 0,
            '_csrf_token'   => $form->getCSRFToken()
        ];

        return $this->renderJson([
            'identifier' => 'id',
            'items'      => $items
        ]);
    }

    public function executeGetTenderAlternativeBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $tenderAlternative = TenderAlternativeTable::getInstance()->find($request->getParameter('id'))
        );

        $project = $tenderAlternative->ProjectStructure;

        $pdo = $project->getTable()->getConnection()->getDbh();

        //get any deleted addendum bill items and reset the related editor bill items
        $stmt = $pdo->prepare("SELECT info.bill_item_id
            FROM ".EditorBillItemInfoTable::getInstance()->getTableName()." info
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON i.id = info.bill_item_id
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            JOIN ".ProjectStructureTable::getInstance()->getTableName()." bill ON e.project_structure_id = bill.id
            WHERE bill.root_id = ".$project->id." AND i.project_revision_deleted_at IS NOT NULL
            AND e.deleted_at IS NULL AND bill.deleted_at IS NULL");

        $stmt->execute();

        $addendumItemIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        EditorBillItemInfoTable::resetEditorBillItems($addendumItemIds);

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();

        $company->setInitialEditorBillItemsGrandTotalByProject($project);

        $tenderAlternativeProjectStructureIds = [];

        //we set default to -1 so it should return empty query if there is no assigned bill for this tender alternative;
        $tenderAlternativeProjectStructureIds = [-1];
        $tenderAlternativesBills = $tenderAlternative->getAssignedBills();

        if($tenderAlternativesBills)
        {
            $tenderAlternativeProjectStructureIds = array_column($tenderAlternativesBills, 'id');
        }

        $records = DoctrineQuery::create()->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type < ?', ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL)
            ->whereIn('s.id', $tenderAlternativeProjectStructureIds)
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        $count = 0;
        $form  = new BaseForm();

        $projectSumTotal       = $company->getEditorOverallTotalForProject($project->id);
        $billsWithAddendums    = $project->getBillsWithAddendums(false);
        $latestProjectRevision = $project->getLatestLockedProjectRevision();

        foreach($records as $key => $record)
        {
            $records[ $key ]['billLayoutSettingId'] = ( isset( $record['BillLayoutSetting']['id'] ) ) ? $record['BillLayoutSetting']['id'] : null;
            $count                                  = ($record['type'] == ProjectStructure::TYPE_BILL) ? $count + 1 : $count;

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

            $records[ $key ]['count']          = $record['type'] == ProjectStructure::TYPE_BILL ? $count : null;
            $records[ $key ]['overall_total']  = $record['type'] == ProjectStructure::TYPE_BILL ? $company->getEditorOverallTotalByBillId($record['id']) : 0;
            $records[ $key ]['bill_sum_total'] = $projectSumTotal;
            $records[ $key ]['_csrf_token']    = $form->getCSRFToken();

            unset( $records[ $key ]['BillLayoutSetting'] );
            unset( $records[ $key ]['BillType'] );
            unset( $records[ $key ]['BillColumnSettings'] );
        }

        array_push($records, array(
            'id'                  => Constants::GRID_LAST_ROW,
            'title'               => "",
            'type'                => 1,
            'level'               => 0,
            'billLayoutSettingId' => null,
            'is_add_latest_rev'   => 0,
            'addendum_version'    => null,
            'count'               => null,
            'overall_total'       => 0,
            'bill_sum_total'      => 0,
            '_csrf_token'         => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetProjectRevisions(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        $user              = $this->getUser()->getGuardUser();
        $company           = $user->Profile->getEProjectUser()->Company->getBsCompany();
        $form              = new BaseForm();
        $editorProjectInfo = $company->getEditorProjectInformationByProject($project);

        if(!$editorProjectInfo)
        {
            $firstRevision = DoctrineQuery::create()->select('r.id')
                ->from('ProjectRevision r')
                ->where('r.project_structure_id = ?', $project->id)
                ->addOrderBy('r.version ASC')
                ->limit(1)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();

            $editorProjectInfo = new EditorProjectInformation();
            $editorProjectInfo->project_structure_id = $project->id;
            $editorProjectInfo->company_id           = $company->id;
            $editorProjectInfo->printing_revision_id = $firstRevision['id'];

            $editorProjectInfo->save();
        }

        $records = DoctrineQuery::create()->select('br.id, br.revision, br.locked_status, br.version')
            ->from('ProjectRevision br')
            ->where('br.project_structure_id = ?', $project->id)
            ->andWhere('br.locked_status IS TRUE')
            ->addOrderBy('br.version ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $count = 1;
        foreach($records as $idx => $record)
        {
            $records[$idx]['count'] = $count++;
            $records[$idx]['current_print'] = ($editorProjectInfo->printing_revision_id == $record['id']);
            $records[$idx]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($records, array(
            'id'             => Constants::GRID_LAST_ROW,
            'revision'       => "",
            'version'        => 0,
            'locked_status'  => true,
            'count'          => "",
            'current_print'  => false,
            '_csrf_token'    => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeChangeCurrentPrintRevision(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $projectRevision = Doctrine_Core::getTable('ProjectRevision')->find($request->getParameter('id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $project->node->isRoot()
        );

        $user    = $this->getUser()->getGuardUser();
        $company = $user->Profile->getEProjectUser()->Company->getBsCompany();

        try
        {
            $projectInfo = $company->getEditorProjectInformationByProject($project);

            if(!$projectInfo)
            {
                throw new Exception('No EditorProjectInformation found!');
            }

            $projectInfo->printing_revision_id = $projectRevision->id;
            $projectInfo->save();

            $records = DoctrineQuery::create()->select('br.id, br.revision, br.locked_status, br.version')
                ->from('ProjectRevision br')
                ->where('br.project_structure_id = ?', $project->id)
                ->addOrderBy('br.version ASC')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            $count = 1;
            foreach($records as $idx => $record)
            {
                $records[$idx]['current_print'] = ($projectInfo->printing_revision_id == $record['id']);
            }

            $success = true;
            $errorMsg = null;
        }
        catch(Exception $e)
        {
            $item = [];
            $success = false;
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items'=>$records));
    }

    public function executeNoAccess(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);
    }
}
