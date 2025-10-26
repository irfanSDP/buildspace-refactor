<?php

/**
 * projectBuilder actions.
 *
 * @package    buildspace
 * @subpackage projectBuilder
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class projectBuilderActions extends BaseActions {

    public function executeGetProjects(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $user = $this->getUser()->getGuardUser();

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => ProjectStructureTable::getProjectsByUser($user, ProjectUserPermission::STATUS_PROJECT_BUILDER)
        ));
    }

    public function executeProjectForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $form = new ProjectMainInformationForm();

        $projectInfo = array(
            'project_main_information[_csrf_token]' => $form->getCSRFToken(),
        );

        $workCategoryOptions = array();

        $workCategories = DoctrineQuery::create()->select('c.id, c.name')
            ->from('WorkCategory c')
            ->addOrderBy('c.name ASC')
            ->fetchArray();

        foreach($workCategories as $workCategory)
        {
            $optionArray = array(
                'value' => (string)$workCategory['id'],
                'label' => $workCategory['name']
            );

            array_push($workCategoryOptions, $optionArray);
        }

        return $this->renderJson(array(
            'projectForm'         => $projectInfo,
            'workCategoryOptions' => $workCategoryOptions,
        ));
    }

    public function executeProjectUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        if( ! $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) )
        {
            $project             = new ProjectStructure();
            $project->created_by = $this->getUser()->getGuardUser()->getId();
        }

        $errorMsg = null;
        $item     = array();

        try
        {
            $projectInfo = new ProjectMainInformation();

            $form = new ProjectMainInformationForm($projectInfo, array( 'projectStructure' => $project ));

            if( $this->isFormValid($request, $form) )
            {
                $projectInfo = $form->save();
                $baseForm    = new BaseForm();
                $user        = $this->getUser()->getGuardUser();

                $item = array(
                    'id'             => $project->id,
                    'title'          => $projectInfo->title,
                    'priority'       => $project->priority,
                    'is_admin'       => $user->isAdminForProject($project, ProjectUserPermission::STATUS_PROJECT_BUILDER),
                    'state'          => ( $projectInfo->Subregions->name && $projectInfo->Subregions->name != "NULL" ) ? $projectInfo->Subregions->name : "N/A",
                    'country'        => ( $projectInfo->Regions->country && $projectInfo->Regions->country != "NULL" ) ? $projectInfo->Regions->country : "N/A",
                    'status'         => ProjectMainInformation::getProjectStatusById($projectInfo->status),
                    'status_id'      => $projectInfo->status,
                    'created_by'     => ( $project->Creator->Profile->name ) ? $project->Creator->Profile->name : '-',
                    'created_at'     => date('d/m/Y H:i', strtotime($projectInfo->created_at)),
                    'can_be_deleted' => true,
                    '_csrf_token'    => $baseForm->getCSRFToken()
                );

                $success = true;
            }
            else
            {
                $errorMsg = $form->getErrors();
                $parentId = null;
                $success  = false;
                $title    = null;
            }
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $item,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeProjectListingAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $project = new ProjectStructure();
        $con     = $project->getTable()->getConnection();

        if( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
        {
            $prevElement = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('prev_item_id')) : null;

            $priority = $prevElement ? $prevElement->priority + 1 : 0;
        }
        else
        {
            $this->forward404Unless($nextProject = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('before_id')));

            $priority = $nextProject->priority;
        }

        $items = array();
        try
        {
            $con->beginTransaction();

            DoctrineQuery::create()
                ->update('ProjectStructure')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', $priority)
                ->execute();

            if( $request->hasParameter('attr_name') )
            {
                $fieldName  = $request->getParameter('attr_name');
                $fieldValue = $request->getParameter('val');
                $project->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
            }
            else
            {
                $count = DoctrineQuery::create()->select('p.id')
                    ->from('ProjectStructure p')
                    ->where('p.title ILIKE ?', 'New Project%')
                    ->addOrderBy('p.priority ASC')
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->count();

                $title = 'New Project ' . ( $count + 1 );

                $project->title = $title;
            }

            $project->priority = $priority;
            $project->type     = ProjectStructure::TYPE_ROOT;

            $project->save();

            $project->getTable()->getTree()->createRoot($project);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item = array();

            $form = new BaseForm();

            $item['id']          = $project->id;
            $item['title']       = $project->title;
            $item['created_at']  = date('d/m/Y H:i', strtotime($project->created_at));
            $item['_csrf_token'] = $form->getCSRFToken();

            array_push($items, $item);

            if( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $defaultLastRow = array(
                    'id'          => Constants::GRID_LAST_ROW,
                    'title'       => '',
                    'created_at'  => '-',
                    '_csrf_token' => $form->getCSRFToken()
                );
                array_push($items, $defaultLastRow);
            }
        }
        catch(Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeProjectListingUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $rowData = array();
        $con     = $project->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $project->{'set' . sfInflector::camelize($fieldName)}($fieldValue);

            $project->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $rowData = array(
                $fieldName => $project->$fieldName
            );
        }
        catch(Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }

    public function executeProjectListingDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $errorMsg = null;

        try
        {
            $item['id'] = $project->id;

            if( $project->MainInformation->status == ProjectMainInformation::STATUS_IMPORT )
            {
                ProjectStructureTable::removeProjectDataByProjectId($project->id);
            }
            else
            {
                $project->delete();
            }

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $item     = array();
            $success  = false;
        }

        // return items need to be in array since grid js expect an array of items for delete operation (delete from store)
        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => array( $item ) ));
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

    public function executeMainInfoForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $form = new ProjectMainInformationForm($structure->MainInformation);

        $data['formValues'] = array(
            'project_main_information[title]'        => $structure->title,
            'project_main_information[description]'  => $structure->MainInformation->description,
            'project_main_information[region_id]'    => $structure->MainInformation->region_id,
            'project_main_information[subregion_id]' => $structure->MainInformation->subregion_id,
            'project_main_information[site_address]' => $structure->MainInformation->site_address,
            'project_main_information[currency_id]'  => $structure->MainInformation->currency_id,
            'project_main_information[client]'       => $structure->MainInformation->client,
            'project_main_information[start_date]'   => $structure->MainInformation->start_date ? date('Y-m-d', strtotime($structure->MainInformation->start_date)) : date('Y-m-d'),
            'projectStatus'                          => ProjectMainInformation::getProjectStatusById($structure->MainInformation->status),
            'createdBy'                              => ( $structure->Creator->Profile->name ) ? $structure->Creator->Profile->name : '-',
            'project_main_information[_csrf_token]'  => $form->getCSRFToken()
        );

        if( $structure->MainInformation->currency_id )
        {
            $data['currency'] = $structure->MainInformation->Currency->currency_code;
        }

        $workCategoryOptions = array();

        $workCategories = DoctrineQuery::create()->select('c.id, c.name')
            ->from('WorkCategory c')
            ->addOrderBy('c.name ASC')
            ->fetchArray();

        foreach($workCategories as $workCategory)
        {
            $optionArray = array(
                'value' => (string)$workCategory['id'],
                'label' => $workCategory['name']
            );

            if( ! $structure->MainInformation->isNew() and $structure->MainInformation->work_category_id == $workCategory['id'] )
            {
                $optionArray['selected'] = true;
            }

            array_push($workCategoryOptions, $optionArray);
        }

        $data['eProjectReference']   = ( $structure->MainInformation->EProjectProject ) ? $structure->MainInformation->EProjectProject->reference : null;
        $data['workCategoryOptions'] = $workCategoryOptions;

        $data['isProjectOwner'] = ( $structure->created_by == $this->getUser()->getGuardUser()->getId() ) ? true : false;
        $data['isSuperAdmin']   = $this->getUser()->getGuardUser()->getIsSuperAdmin();

        return $this->renderJson($data);
    }

    public function executeMainInfoUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $form = new ProjectMainInformationForm($structure->MainInformation);

        if( $this->isFormValid($request, $form) )
        {
            $project        = $form->save();
            $title          = $project->title;
            $workCategoryId = $project->work_category_id;
            $currency       = $project->Currency->currency_code;
            $errors         = null;
            $success        = true;
        }
        else
        {
            $errors         = $form->getErrors();
            $success        = false;
            $title          = null;
            $workCategoryId = null;
            $currency       = sfConfig::get('app_default_currency_abbreviation');
        }

        return $this->renderJson(array(
            'success'          => $success,
            'errors'           => $errors,
            'title'            => $title,
            'work_category_id' => $workCategoryId,
            'currency'         => $currency
        ));
    }

    public function executeLevelForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if( $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) )
        {
            $parent = $structure->node->getParent();
            $level  = $structure->Level;
        }
        else
        {
            $parent = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('parent_id'));
            $level  = new ProjectLevel();
        }

        $form = new ProjectLevelForm($level, array( 'parent' => $parent ));

        return $this->renderJson(array(
            'project_level[title]'       => $form->getObject()->title,
            'project_level[description]' => $form->getObject()->description,
            'project_level[_csrf_token]' => $form->getCSRFToken()
        ));
    }


    public function executeLevelUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if( $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) )
        {
            $parent = $structure->node->getParent();
            $level  = $structure->Level;
        }
        else
        {
            $parent = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('parent_id'));
            $level  = new ProjectLevel();
        }

        $form = new ProjectLevelForm($level, array( 'parent' => $parent ));

        if( $this->isFormValid($request, $form) )
        {
            $level = $form->save();

            $form = new BaseForm();

            $item = array(
                'id'          => $level->ProjectStructure->id,
                'title'       => $level->ProjectStructure->title,
                'type'        => $level->ProjectStructure->type,
                'level'       => $level->ProjectStructure->level,
                '_csrf_token' => $form->getCSRFToken()
            );

            $parentId = $level->ProjectStructure->level > 1 ? $level->ProjectStructure->node->getParent()->id : null;
            $errors   = null;
            $success  = true;
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

    public function executeBillForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $settings = null;

        if( $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) )
        {
            $parent      = $structure->node->getParent();
            $billSetting = $structure->BillSetting;
        }
        else
        {
            $parent      = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('parent_id'));
            $billSetting = new BillSetting();

            $billAdminSetting = DoctrineQuery::create()->select('p.id, p.build_up_rate_rounding_type, p.build_up_quantity_rounding_type, p.unit_type')
                ->from('BillAdminSetting p')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();
        }

        $form = new BillSettingForm($billSetting, array( 'parent' => $parent ));

        return $this->renderJson(array(
            'bill_setting[title]'                           => $form->getObject()->title,
            'bill_setting[description]'                     => $form->getObject()->description,
            'bill_setting[build_up_rate_rounding_type]'     => ( $form->getObject()->build_up_rate_rounding_type ) ? $form->getObject()->build_up_rate_rounding_type : $billAdminSetting['build_up_rate_rounding_type'],
            'bill_setting[build_up_quantity_rounding_type]' => ( $form->getObject()->build_up_quantity_rounding_type ) ? $form->getObject()->build_up_quantity_rounding_type : $billAdminSetting['build_up_quantity_rounding_type'],
            'bill_setting[unit_type]'                       => ( $form->getObject()->unit_type ) ? $form->getObject()->unit_type : $billAdminSetting['unit_type'],
            'unitTypeText'                                  => ( $structure ) ? $billSetting->UnitOfMeasurementType->name : null,
            'bill_setting[_csrf_token]'                     => $form->getCSRFToken(),
            'bill_type[type]'                               => ( $structure ) ? $structure->BillType->type : BillType::TYPE_STANDARD
        ));
    }

    public function executeBillUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());
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

    public function executeProjectStructureDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        $con      = $structure->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $deletedIds = $structure->delete($con);

            $con->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $con->rollback();

            $errorMsg   = $e->getMessage();
            $deletedIds = array();
            $success    = false;
        }

        // return items need to be in array since grid js expect an array of items for delete operation (delete from store)
        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'items'    => array_reverse($deletedIds)
        ));
    }

    public function executeGetProjectBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $records = DoctrineQuery::create()
            ->select('s.id, s.title, s.type, s.level, t.type, t.status, c.id, c.quantity, c.use_original_quantity, bls.id, somls.id, sorbls.id')
            ->from('ProjectStructure s')
            ->leftJoin('s.BillType t')
            ->leftJoin('s.BillColumnSettings c')
            ->leftJoin('s.BillLayoutSetting bls')
            ->leftJoin('s.SupplyOfMaterialLayoutSetting somls')
            ->leftJoin('s.ScheduleOfRateBillLayoutSetting sorbls')
            ->where('s.lft >= ? AND s.rgt <= ?', array( $project->lft, $project->rgt ))
            ->andWhere('s.root_id = ?', $project->id)
            ->andWhere('s.type <= ?', ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL)
            ->addOrderBy('s.lft ASC')
            ->fetchArray();

        $count = 0;
        $form  = new BaseForm();

        $projectSumTotal = ProjectStructureTable::getOverallTotalForProject($project->id);
        $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($project);
        $tenderAlternativesByBills = $project->getBillsWithTenderAlternatives();

        foreach($records as $key => $record)
        {
            $records[ $key ]['billLayoutSettingId']    = ( isset( $record['BillLayoutSetting']['id'] ) ) ? $record['BillLayoutSetting']['id'] : null;
            $records[ $key ]['somBillLayoutSettingId'] = ( isset( $record['SupplyOfMaterialLayoutSetting']['id'] ) ) ? $record['SupplyOfMaterialLayoutSetting']['id'] : null;
            $records[ $key ]['sorBillLayoutSettingId'] = ( isset( $record['ScheduleOfRateBillLayoutSetting']['id'] ) ) ? $record['ScheduleOfRateBillLayoutSetting']['id'] : null;
            $count                                     = ( $record['type'] == ProjectStructure::TYPE_BILL or $record['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL or $record['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL ) ? $count + 1 : $count;

            if( $record['type'] == ProjectStructure::TYPE_BILL and is_array($records[ $key ]['BillType']) )
            {
                $records[ $key ]['bill_type']   = $record['BillType']['type'];
                $records[ $key ]['bill_status'] = $record['BillType']['status'];
            }

            $records[ $key ]['count']                      = ( $record['type'] == ProjectStructure::TYPE_BILL or $record['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL or $record['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL ) ? $count : null;
            $records[ $key ]['original_total']             = $record['type'] == ProjectStructure::TYPE_BILL ? ProjectStructureTable::getOverallOriginalTotalByBillId($record['id']) : 0;
            $records[ $key ]['overall_total_after_markup'] = ($record['type'] == ProjectStructure::TYPE_BILL && array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;
            $records[ $key ]['bill_sum_total']             = $projectSumTotal;
            $records[ $key ]['recalculate']                = $record['id'];
            $records[ $key ]['tender_alternative_count']   = array_key_exists($record['id'], $tenderAlternativesByBills) ? count($tenderAlternativesByBills[$record['id']]) : 0;
            $records[ $key ]['_csrf_token']                = $form->getCSRFToken();

            unset( $records[ $key ]['BillLayoutSetting'], $records[ $key ]['SupplyOfMaterialLayoutSetting'], $records[ $key ]['ScheduleOfRateBillLayoutSetting'], $records[ $key ]['BillType'], $records[ $key ]['BillColumnSettings'] );
        }

        array_push($records, array(
            'id'                         => Constants::GRID_LAST_ROW,
            'title'                      => "",
            'type'                       => 1,
            'level'                      => 0,
            'billLayoutSettingId'        => null,
            'count'                      => null,
            'original_total'             => 0,
            'overall_total_after_markup' => 0,
            'bill_sum_total'             => 0,
            '_csrf_token'                => $form->getCSRFToken(),
            'tender_alternative_count'   => 0,
            'recalculate'                => false
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeCreateBill(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post') and $request->hasParameter('type') and $parent = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('parent_id')));

        $structure = new ProjectStructure();
        $con       = $structure->getTable()->getConnection();

        $item = array();
        try
        {
            $con->beginTransaction();

            $count = DoctrineQuery::create()->select('p.id')
                ->from('ProjectStructure p')
                ->where('p.title ILIKE ?', 'New Bill%')
                ->andWhere('p.root_id = ?', $parent->root_id)
                ->addOrderBy('p.priority ASC')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->count();

            $title = 'New Bill ' . ( $count + 1 );

            $structure->title = $title;

            $structure->type = ProjectStructure::TYPE_BILL;

            $structure->node->insertAsLastChildOf($parent);

            $structure->save($con);

            $structure->BillType->type   = $request->getParameter('type');
            $structure->BillType->status = BillType::STATUS_OPEN;
            $structure->BillType->save();

            $con->commit();

            $success = true;

            $errorMsg = null;

            $item = array();

            $form = new BaseForm();

            $item['id']          = $structure->id;
            $item['title']       = $structure->title;
            $item['type']        = $structure->type;
            $item['level']       = $structure->level;
            $item['bill_type']   = $structure->BillType->type;
            $item['bill_status'] = $structure->BillType->status;
            $item['_csrf_token'] = $form->getCSRFToken();
        }
        catch(Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'item'     => $item,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeGetUnitTypes(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $unitTypes = DoctrineQuery::create()->select('u.id, u.name')
            ->from('UnitOfMeasurementType u')
            ->addOrderBy('u.id ASC')
            ->fetchArray();

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $unitTypes
        ));
    }

    public function executeGetCountry(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $regions = DoctrineQuery::create()->select('c.id, c.country AS name')
            ->from('Regions c')
            ->fetchArray();

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $regions
        ));
    }

    public function executePublishToTender(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $project = ProjectStructureTable::getProjectInformationByProjectId($request->getParameter('id')) and
            $projectObject = ProjectStructureTable::getInstance()->find($project['structure']['id'])
        );

        $usersAssignedManually = ( $request->getParameter('usersAssignedManually') == 'true' );

        $con = $projectObject->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $projectObject->publishToTender($con);

            if( ! $usersAssignedManually )
            {
                ProjectUserPermissionTable::automaticallyAssignUsers($projectObject, ProjectUserPermission::STATUS_TENDERING);
            }

            $con->commit();
        }
        catch(Exception $e)
        {
            $con->rollback();

            throw $e;
        }

        try
        {
            //we re query the project info in case there's no project summary created before publish to tender
            $project = ProjectStructureTable::getProjectInformationByProjectId($projectObject->id);

            $filesToZip = array();

            $count = 0;

            $projectUniqueId = $project['mainInformation']['unique_id'];

            $sfProjectExport = new sfBuildspaceExportProjectXML($count . "_" . $project['structure']['id'], $projectUniqueId, ExportedFile::EXPORT_TYPE_TENDER);

            $sfProjectExport->process($project['structure'], $project['mainInformation'], $project['breakdown'], $project['revisions'], $project['tenderAlternatives'], true);

            array_push($filesToZip, $sfProjectExport->getFileInformation());

            $breakdown = $project['breakdown'];

            foreach($breakdown as $structure)
            {
                $count++;

                $sfBillExport = null;
                $billData     = null;
                $fileName     = $count . '_' . $structure['title'];

                switch($structure['type'])
                {
                    case ProjectStructure::TYPE_BILL:
                        $billData = $this->getBillInformation($structure['id']);
                        $sfBillExport = new sfBuildspaceExportBillXML($fileName, $sfProjectExport->uploadPath, $structure['id']);
                        break;
                    case ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL:
                        $billData = $this->getSupplyOfMaterialBillInformation($structure['id']);
                        $sfBillExport = new sfBuildspaceExportSupplyOfMaterialBillXML($fileName, $sfProjectExport->uploadPath, $structure['id']);
                        break;
                    case ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL:
                        $billData = $this->getScheduleOfRateBillInformation($structure['id']);
                        $sfBillExport = new sfBuildspaceExportScheduleOfRateBillXML($fileName, $sfProjectExport->uploadPath, $structure['id']);
                        break;
                }

                if( is_object($sfBillExport) and is_array($billData) )
                {
                    $sfBillExport->process($billData, true);

                    array_push($filesToZip, $sfBillExport->getFileInformation());

                    unset( $sfBillExport, $structure, $billData );
                }
            }

            unset( $sfProjectExport );

            $filename = substr(Utilities::sanitize_file_name($project['mainInformation']['title']), 0, 200);

            $sfZipGenerator = new sfZipGenerator($filename, null, null, true, true);

            $sfZipGenerator->createZip($filesToZip);

            $fileInfo = $sfZipGenerator->getFileInfo();

            $this->uploadFileToEProject($projectObject->MainInformation, $fileInfo, 'project_push_to_tendering');
        }
        catch(Exception $e)
        {
            throw $e;
        }

        return $this->renderJson(array(
            'id'        => $projectObject->id,
            'title'     => $projectObject->title,
            'status'    => ProjectMainInformation::getProjectStatusById($projectObject->MainInformation->status),
            'status_id' => $projectObject->MainInformation->status,
        ));
    }

    public function executeGetStateByCountry(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->hasParameter('regionId'));

        $subRegions = array();

        if( $request->getParameter('regionId') > 0 )
        {
            $subRegions = DoctrineQuery::create()->select('c.id, c.name')
                ->from('Subregions c')
                ->where('c.region_id = ?', $request->getParameter('regionId'))
                ->fetchArray();
        }

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

    public function executeGetProjectGroupLists(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $projectGroups = array();

        $groupWithProjects = $projectStructure->ProjectGroups;

        foreach($groupWithProjects as $groupWithProject)
        {
            $projectGroups[ $groupWithProject->id ] = $groupWithProject->id;
        }

        $groups = Doctrine_Query::create()
            ->select('u.id, u.name')
            ->from('sfGuardGroup u')
            ->orderBy('u.name')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        return $this->renderJson(array( 'groups' => $groups ));
    }

    public function executeUpdateProjectGroupInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $form = new ProjectGroupsAssignmentForm($projectStructure);

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

    /* bill revision action starts here */
    public function executeGetProjectRevisionLists(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        // get bill revisions
        $ProjectRevisions = $structure->getProjectRevisions()->toArray();

        if( ! empty( $ProjectRevisions ) )
        {
            // return the data of bill revisions to be displayed in view
            foreach($ProjectRevisions as $ProjectRevision)
            {
                $data['projectRevisions'][] = array(
                    'id'            => $ProjectRevision['id'],
                    'revision'      => $ProjectRevision['revision'],
                    'version'       => $ProjectRevision['version'],
                    'selected'      => $ProjectRevision['current_selected_revision'],
                    'locked_status' => ( $ProjectRevision['locked_status'] ) ? 1 : 0,
                    'updated_at'    => date('d M Y', strtotime($ProjectRevision['updated_at']))
                );
            }
        }

        $billAddendumform = new ProjectRevisionForm();
        $data['form']     = array( 'csrf_token' => $billAddendumform->getCSRFToken() );

        return $this->renderJson($data);
    }

    public function executeAssignNewSelectedRevision(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and $projectRevision = Doctrine_Core::getTable('ProjectRevision')->find($request->getParameter('revisionId')));

        $projectRevisions = array();
        $form             = new ProjectRevisionForm($projectRevision, array( 'type' => 'assignSelectedRevision' ));

        if( $this->isFormValid($request, $form) )
        {
            $pdo = $projectRevision->getTable()->getConnection()->getDbh();

            // set the previous revision's current_selected_revision status to false
            $sql  = "UPDATE " . ProjectRevisionTable::getInstance()->getTableName() . " SET current_selected_revision = false WHERE (project_structure_id = :projectStructureId)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array( 'projectStructureId' => $structure->root_id ));

            // set the current posted project revision id to true
            $sql  = "UPDATE " . ProjectRevisionTable::getInstance()->getTableName() . " SET current_selected_revision = true WHERE (id = :projectStructureId)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array( 'projectStructureId' => $projectRevision->id ));

            $errors  = null;
            $success = true;

            $structure->refresh();

            $ProjectRevisions = $structure->getProjectRevisions()->toArray();

            foreach($ProjectRevisions as $ProjectRevision)
            {
                $projectRevisions[] = array(
                    'id'            => $ProjectRevision['id'],
                    'revision'      => $ProjectRevision['revision'],
                    'version'       => $ProjectRevision['version'],
                    'selected'      => $ProjectRevision['current_selected_revision'],
                    'locked_status' => ( $ProjectRevision['locked_status'] ) ? 1 : 0,
                    'updated_at'    => date('d M Y', strtotime($ProjectRevision['updated_at']))
                );
            }
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        $billAddendumform = new ProjectRevisionForm();

        $data         = array( 'success' => $success, 'errors' => $errors, 'projectRevisions' => $projectRevisions );
        $data['form'] = array( 'csrf_token' => $billAddendumform->getCSRFToken() );

        return $this->renderJson($data);
    }

    public function executeSaveProjectRevision(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $isNew            = false;
        $projectRevisions = [];

        // find for existing BQ's addendum record
        $bqAddendumRecord = Doctrine_Core::getTable('ProjectRevision')->find($request->getParameter('revisionId'));
        $bqAddendumRecord = $bqAddendumRecord ?: new ProjectRevision();
        $form             = new ProjectRevisionForm($bqAddendumRecord);

        if( $this->isFormValid($request, $form) )
        {
            if( $bqAddendumRecord->isNew() )
            {
                $isNew = true;
            }

            $revision = $form->save();
            $errors   = null;
            $success  = true;
            $form     = new BaseForm();

            $item = [
                'id'            => $revision->id,
                'locked_status' => ( $revision->locked_status ) ? 1 : 0,
                'updated_at'    => date('d M Y', strtotime($revision->updated_at)),
                '_csrf_token'   => $form->getCSRFToken()
            ];

            if( $isNew )
            {
                $project->refresh(true);

                $revisions = $project->getProjectRevisions()->toArray();

                foreach($revisions as $projectRevision)
                {
                    $projectRevisions[] = [
                        'id'            => $projectRevision['id'],
                        'revision'      => $projectRevision['revision'],
                        'version'       => $projectRevision['version'],
                        'selected'      => $projectRevision['current_selected_revision'],
                        'locked_status' => ( $projectRevision['locked_status'] ) ? 1 : 0,
                        'updated_at'    => date('d M Y', strtotime($projectRevision['updated_at']))
                    ];
                }
            }

            // will generate an export addendum's zip file and then send to eProject's module
            if( $revision->version > 0 AND $revision->locked_status )
            {
                $userOriginId = $this->getUser()->getProfile()->eproject_user_id;
                $proc = new BackgroundProcess('exec php '.sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR.'symfony bgprocess:generate_addendum_file '.$project->id.' '.$revision->id.' '.$userOriginId.' 2>&1 | tee '.sfConfig::get("sf_root_dir").DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR.'generate_addendum_file-'.$project->id.'-'.$revision->id.'.log');
                $proc->run();
            }
        }
        else
        {
            $errors  = $form->getErrors();
            $item    = [];
            $success = false;
        }

        $billAddendumForm = new ProjectRevisionForm();

        $data = [
            'success'          => $success,
            'errors'           => $errors,
            'item'             => $item,
            'projectRevisions' => $projectRevisions
        ];

        $data['form'] = ['csrf_token' => $billAddendumForm->getCSRFToken()];

        return $this->renderJson($data);
    }

    /* bill revision action ends here */

    public function executeItemPaste(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and $item = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $lastPosition = false;
        $targetItem   = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('tid'));

        if( ! $targetItem )
        {
            $this->forward404Unless($targetItem = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')));
            $lastPosition = true;
        }

        if( $targetItem->root_id == $item->root_id and $targetItem->lft >= $item->lft and $targetItem->rgt <= $item->rgt )
        {
            return $this->renderJson(array( 'success' => false, 'errorMsg' => "cannot move item into itself" ));
        }

        try
        {
            $item->moveTo($targetItem, $lastPosition);

            $success  = true;
            $errorMsg = null;
        }
        catch(Exception $e)
        {
            $success  = false;
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeItemIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();
        try
        {
            if( $item->indent() )
            {
                $data['id']    = $item->id;
                $data['level'] = $item->level;

                $children = DoctrineQuery::create()->select('s.id, s.level')
                    ->from('ProjectStructure s')
                    ->where('s.root_id = ?', $item->root_id)
                    ->andWhere('s.lft > ? AND s.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('s.lft')
                    ->fetchArray();

                $success = true;
            }
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'item'     => $data,
            'c'        => $children
        ));
    }

    public function executeItemOutdent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();
        try
        {
            if( $item->outdent() )
            {
                $data['id']    = $item->id;
                $data['level'] = $item->level;

                $children = DoctrineQuery::create()->select('s.id, s.level')
                    ->from('ProjectStructure s')
                    ->where('s.root_id = ?', $item->root_id)
                    ->andWhere('s.lft > ? AND s.rgt < ?', array( $item->lft, $item->rgt ))
                    ->addOrderBy('s.lft')
                    ->fetchArray();

                $success = true;
            }
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'item'     => $data,
            'c'        => $children
        ));
    }

    private function getBillInformation($billId, $revisionId = false)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $billStructure = array();

        $sqlExt = null;

        //Get Bill and Its information
        $bill = DoctrineQuery::create()
            ->select('p.id, s.*, bcs.*, bt.*, m.*, l.*, lh.*, lp.*')
            ->from('ProjectStructure p')
            ->leftJoin('p.BillSetting s')
            ->leftJoin('p.BillMarkupSetting m')
            ->leftJoin('p.BillColumnSettings bcs')
            ->leftJoin('p.BillType bt')
            ->leftJoin('p.BillLayoutSetting l')
            ->leftJoin('l.BillHeadSettings lh')
            ->leftJoin('l.BillPhrase lp')
            ->where('p.id = ?', $billId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        //Get Element List
        $stmt = $pdo->prepare("SELECT e.id, e.project_structure_id, e.description, e.priority FROM " . BillElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute(array(
            'project_structure_id' => $bill['id']
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if( $revisionId )
        {
            $currentRevision = DoctrineQuery::create()
                ->select('r.id, r.project_structure_id, r.revision, r.version')
                ->from('ProjectRevision r')
                ->where('r.id = ?', $revisionId)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();

            $previousRevision = DoctrineQuery::create()
                ->select('r.id, r.project_structure_id, r.revision, r.version')
                ->from('ProjectRevision r')
                ->where('r.version = ?', $currentRevision['version'] - 1)
                ->andWhere('r.project_structure_id = ?', $currentRevision['project_structure_id'])
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->fetchOne();
        }
        else
        {
            $currentRevision  = false;
            $previousRevision = false;
        }

        if( count($elements) )
        {
            foreach($elements as $element)
            {
                $result = array(
                    'id'                   => $element['id'],
                    'project_structure_id' => $element['project_structure_id'],
                    'description'          => $element['description'],
                    'priority'             => $element['priority'],
                    'items'                => array()
                );

                //Get Bill Pages Information
                $query = DoctrineQuery::create()->select('p.id, p.element_id, p.page_no, p.revision_id, p.new_revision_id, i.id, i.bill_page_id, i.bill_item_id, i.new_item_from_new_revision')
                    ->from('BillPage p')
                    ->leftJoin('p.Items i');

                if( $revisionId )
                {
                    if( $currentRevision && $previousRevision )
                    {
                        $query->where('(p.new_revision_id = ? OR p.revision_id = ?) AND p.element_id = ?',
                            array( $currentRevision['id'], $previousRevision['id'], $element['id'] ));

                        $result['billPages'] = $query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->execute();
                    }
                }
                else
                {
                    $result['billPages'] = $query->whereIn('p.element_id', $element['id'])
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();
                }

                //Get Collection Pages Information
                $query = DoctrineQuery::create()->select('c.id, c.element_id, c.revision_id, c.page_no')
                    ->from('BillCollectionPage c');

                if( $revisionId )
                {
                    if( $currentRevision && $previousRevision )
                    {
                        $query->where('c.revision_id = ? AND c.element_id = ?',
                            array( $previousRevision['id'], $element['id'] ));

                        $result['collectionPages'] = $query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->execute();
                    }
                }
                else
                {
                    $result['collectionPages'] = $query->whereIn('c.element_id', $element['id'])
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();
                }

                $query = DoctrineQuery::create()->select('c.id, c.description, c.type, c.uom_id, c.element_id, c.grand_total_after_markup, c.grand_total_quantity,
                    c.bill_ref_element_no, c.bill_ref_page_no, c.bill_ref_char, c.priority, c.root_id, c.lft, c.rgt, c.level, c.project_revision_id, c.deleted_at_project_revision_id, c.project_revision_deleted_at,
                    uom.id, uom.name, uom.symbol, uom.type, type.id, type.bill_item_id, type.bill_column_setting_id, type.include, type.total_quantity,
                    type_fc.id, type_fc.relation_id, type_fc.column_name, type_fc.final_value, type_fc.created_at, ls.*, pc.supply_rate, pc.bill_item_id')
                    ->from('BillItem c')
                    ->leftJoin('c.BillItemTypeReferences type')
                    ->leftJoin('c.LumpSumPercentage ls')
                    ->leftJoin('c.PrimeCostRate pc')
                    ->leftJoin('type.FormulatedColumns type_fc')
                    ->leftJoin('c.UnitOfMeasurement uom')
                    ->where('c.element_id = ? ', $element['id'])
                    ->andWhere('c.deleted_at IS NULL')
                    ->orderBy('c.priority, c.lft, c.level')
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

                if( $revisionId )
                {
                    $query->addWhere('c.project_revision_id = ? OR c.deleted_at_project_revision_id = ?',
                        array( $revisionId, $revisionId ));
                }


                $billItems = $query->execute();

                $result['items'] = $billItems;

                array_push($billStructure, $result);

                unset( $element );
            }
        }

        if( $bill )
        {
            return array(
                'elementsAndItems'   => ( $elements && count($elements) > 0 ) ? $billStructure : null,
                'billSetting'        => $bill['BillSetting'],
                'billMarkupSetting'  => $bill['BillMarkupSetting'],
                'billColumnSettings' => $bill['BillColumnSettings'],
                'billType'           => $bill['BillType'],
                'billLayoutSetting'  => $bill['BillLayoutSetting']
            );
        }

        return false;
    }

    private function getSupplyOfMaterialBillInformation($billId)
    {
        $pdo           = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $billStructure = array();

        //Get Bill and Its information
        $bill = DoctrineQuery::create()
            ->select('p.id, s.*, l.*, lh.*, lp.*')
            ->from('ProjectStructure p')
            ->leftJoin('p.SupplyOfMaterial s')
            ->leftJoin('p.SupplyOfMaterialLayoutSetting l')
            ->leftJoin('l.SOMBillHeadSettings lh')
            ->leftJoin('l.SOMBillPhrase lp')
            ->where('p.id = ?', $billId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        //Get Element List
        $stmt = $pdo->prepare("SELECT e.id, e.project_structure_id, e.description, e.priority
        FROM " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL
        ORDER BY e.priority");

        $stmt->execute(array(
            'project_structure_id' => $bill['id']
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($elements as $element)
        {
            $result = array(
                'id'                   => $element['id'],
                'project_structure_id' => $element['project_structure_id'],
                'description'          => $element['description'],
                'priority'             => $element['priority'],
                'items'                => array()
            );

            $result['items'] = DoctrineQuery::create()
                ->select('c.id, c.description, c.type, c.uom_id, c.element_id, c.priority, c.supply_rate,
                c.root_id, c.lft, c.rgt, c.level, uom.id, uom.name, uom.symbol, uom.type')
                ->from('SupplyOfMaterialItem c')
                ->leftJoin('c.UnitOfMeasurement uom')
                ->where('c.element_id = ? ', $element['id'])
                ->andWhere('c.deleted_at IS NULL')
                ->orderBy('c.priority, c.lft, c.level')
                ->fetchArray();

            $billStructure[] = $result;

            unset( $element );
        }

        if( ! $bill )
        {
            return false;
        }

        return array(
            'elementsAndItems'  => ( $elements && count($elements) > 0 ) ? $billStructure : null,
            'billSetting'       => $bill['SupplyOfMaterial'],
            'billLayoutSetting' => $bill['SupplyOfMaterialLayoutSetting']
        );
    }

    private function getScheduleOfRateBillInformation($billId)
    {
        $pdo           = ProjectStructureTable::getInstance()->getConnection()->getDbh();
        $billStructure = array();

        //Get Bill and Its information
        $bill = DoctrineQuery::create()
            ->select('p.id, s.*, l.*, lh.*, lp.*')
            ->from('ProjectStructure p')
            ->leftJoin('p.ScheduleOfRateBill s')
            ->leftJoin('p.ScheduleOfRateBillLayoutSetting l')
            ->leftJoin('l.ScheduleOfRateBillLayoutHeadSettings lh')
            ->leftJoin('l.ScheduleOfRateBillLayoutPhrase lp')
            ->where('p.id = ?', $billId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        //Get Element List
        $stmt = $pdo->prepare("SELECT e.id, e.project_structure_id, e.description, e.priority
        FROM " . ScheduleOfRateBillElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = :project_structure_id AND e.deleted_at IS NULL
        ORDER BY e.priority");

        $stmt->execute(array(
            'project_structure_id' => $bill['id']
        ));

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($elements as $element)
        {
            $result = array(
                'id'                   => $element['id'],
                'project_structure_id' => $element['project_structure_id'],
                'description'          => $element['description'],
                'priority'             => $element['priority'],
                'items'                => array()
            );

            $result['items'] = DoctrineQuery::create()
                ->select('c.id, c.description, c.type, c.uom_id, c.element_id, c.priority,
                c.root_id, c.lft, c.rgt, c.level, uom.id, uom.name, uom.symbol, uom.type')
                ->from('ScheduleOfRateBillItem c')
                ->leftJoin('c.UnitOfMeasurement uom')
                ->where('c.element_id = ? ', $element['id'])
                ->andWhere('c.deleted_at IS NULL')
                ->orderBy('c.priority, c.lft, c.level')
                ->fetchArray();

            $billStructure[] = $result;

            unset( $element );
        }

        if( ! $bill )
        {
            return false;
        }

        return array(
            'elementsAndItems'  => ( $elements && count($elements) > 0 ) ? $billStructure : null,
            'billSetting'       => $bill['ScheduleOfRateBill'],
            'billLayoutSetting' => $bill['ScheduleOfRateBillLayoutSetting']
        );
    }

    public function executeValidateEmptyGrandTotalQty(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $billItemIds = $project->validateEmptyGrandTotalQty();
        $data        = $this->getBillStructureFromBillItemIds($billItemIds, $project);

        return $this->renderJson([
            'has_error' => !empty($data),
            'items'     => $data
        ]);
    }

    /*
     * This validation will be use to validate certain items with zero grand total qty
     * before publish to tender
     */
    public function executeValidateZeroGrandTotalQty(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $billItemIds = $project->validateZeroGrandTotalQty();
        $data        = $this->getBillStructureFromBillItemIds($billItemIds, $project);

        return $this->renderJson([
            'has_error' => !empty($data),
            'items'     => $data
        ]);
    }

    public function executeValidateHeadsWithoutItems(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $billItemIds = $project->validateHeadsWithoutItems();
        $data        = $this->getBillStructureFromBillItemIds($billItemIds, $project);

        foreach($data as $key => $record)
        {
            if(in_array($record['id'], $billItemIds)) $data[$key]['warning'] = true;
        }

        return $this->renderJson([
            'has_error' => !empty($data),
            'items'     => $data
        ]);
    }

    private function getBillStructureFromBillItemIds(Array $billItemIds, ProjectStructure $project)
    {
        if(empty($billItemIds) or $project->type != ProjectStructure::TYPE_ROOT)
        {
            return [];
        }

        $pdo  = $project->getTable()->getConnection()->getDbh();
        $data = [];

        $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.level, p.priority,
        e.priority AS element_priority, p.lft, uom.symbol AS uom_symbol,
        pc.supply_rate AS pc_supply_rate, pc.wastage_percentage AS pc_wastage_percentage,
        pc.wastage_amount AS pc_wastage_amount, pc.labour_for_installation AS pc_labour_for_installation,
        pc.other_cost AS pc_other_cost, pc.profit_percentage AS pc_profit_percentage,
        pc.profit_amount AS pc_profit_amount, pc.total AS pc_total
        FROM " . BillItemTable::getInstance()->getTableName() . " c
        LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " AS uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
        LEFT JOIN " . BillItemPrimeCostRateTable::getInstance()->getTableName() . " pc ON c.id = pc.bill_item_id AND pc.deleted_at IS NULL
        JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
        JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON p.element_id = e.id
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " AS s ON e.project_structure_id = s.id
        WHERE s.root_id = " . $project->id . " AND c.id IN (" . implode(",", $billItemIds) . ")
        AND c.root_id = p.root_id AND c.element_id = p.element_id
        AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
        AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
        AND e.deleted_at IS NULL AND s.deleted_at IS NULL
        ORDER BY e.priority, p.priority, p.lft, p.level ASC");

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT DISTINCT s.id, s.title, s.lft
        FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
        JOIN " . BillElementTable::getInstance()->getTableName() . " AS be ON s.id = be.project_structure_id
        JOIN " . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id
        WHERE s.root_id = " . $project->id . " AND bi.id IN (" . implode(",", $billItemIds) . ")
        AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
        AND s.deleted_at IS NULL ORDER BY s.lft ASC");

        $stmt->execute();

        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($bills as $bill)
        {
            $stmt = $pdo->prepare("SELECT DISTINCT be.id, be.description, be.priority
            FROM " . BillElementTable::getInstance()->getTableName() . " AS be
            JOIN " . BillItemTable::getInstance()->getTableName() . " AS bi ON be.id = bi.element_id
            WHERE be.project_structure_id = " . $bill['id'] . " AND bi.id IN (" . implode(",", $billItemIds) . ")
            AND bi.project_revision_deleted_at IS NULL AND bi.deleted_at IS NULL AND be.deleted_at IS NULL
            ORDER BY be.priority ASC");

            $stmt->execute();

            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $elements as $element )
            {
                $result = array(
                    'id'          => 'bill-' . $bill['id'] . '-elem' . $element['id'],
                    'description' => $bill['title'] . " > " . $element['description'],
                    'type'        => - 1,
                    'level'       => 0,
                    'uom_id'      => - 1,
                    'uom_symbol'  => ''
                );

                array_push($data, $result);

                $billItem = array( 'id' => Constants::GRID_LAST_ROW );

                foreach ( $items as $k => $item )
                {
                    if ( $billItem['id'] != $item['id'] && $item['element_id'] == $element['id'] )
                    {
                        $billItem['id']                   = $item['id'];
                        $billItem['description']          = $item['description'];
                        $billItem['type']                 = $item['type'];
                        $billItem['level']                = $item['level'];
                        $billItem['uom_symbol']           = $item['uom_id'] > 0 ? $item['uom_symbol'] : '';

                        if($item['type'] == BillItem::TYPE_ITEM_PC_RATE)
                        {
                            $billItem['pc_supply_rate']             = number_format($item['pc_supply_rate'], 2, '.', '');
                            $billItem['pc_wastage_percentage']      = number_format($item['pc_wastage_percentage'], 2, '.', '');
                            $billItem['pc_wastage_amount']          = number_format($item['pc_wastage_amount'], 2, '.', '');
                            $billItem['pc_labour_for_installation'] = number_format($item['pc_labour_for_installation'], 2, '.', '');
                            $billItem['pc_other_cost']              = number_format($item['pc_other_cost'], 2, '.', '');
                            $billItem['pc_profit_percentage']       = number_format($item['pc_profit_percentage'], 2, '.', '');
                            $billItem['pc_profit_amount']           = number_format($item['pc_profit_amount'], 2, '.', '');
                            $billItem['pc_total']                   = number_format($item['pc_total'], 2, '.', '');
                        }

                        array_push($data, $billItem);

                        unset( $items[$k], $item );
                    }
                }
            }
        }

        if(!empty($data))
        {
            array_push($data, [
                'id' => Constants::GRID_LAST_ROW,
                'uom_symbol' => ''
            ]);
        }

        return $data;
    }

    public function executeUploadEBQFile(sfWebRequest $request)
    {
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

                $userId = $this->getUser()->getGuardUser()->id;

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

                            $projectInformation = $importer->getProjectInformation();

                            $projectBreakdown = $importer->getProjectBreakdown();
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
        }

        return $this->renderJson(array(
            'errorMsg'         => $errorMsg,
            'success'          => $success,
            'tempFileInfo'     => $fileInfo,
            'projectInfo'      => $projectInformation,
            'projectBreakdown' => $projectBreakdown
        ));
    }

    public function executeImportEBQFile(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $filename   = $request->getParameter('filename');
        $extension  = $request->getParameter('extension');
        $uploadPath = $request->getParameter('uploadPath');
        $withRate   = !empty($request->getParameter('withRate'));
        $withQty    = !empty($request->getParameter('withQty'));

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
            $userId       = $this->getUser()->getGuardUser()->id;

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
                $user = $this->getUser()->getGuardUser();

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
}