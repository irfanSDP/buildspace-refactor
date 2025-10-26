<?php

/**
 * projectManagement actions.
 *
 * @package    buildspace
 * @subpackage projectManagement
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class projectManagementActions extends BaseActions {

    public function executeGetProjectScheduleList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $records = DoctrineQuery::create()->select('s.id, s.title, s.project_structure_id, s.start_date, s.exclude_saturdays, s.exclude_sundays, s.sub_package_id, s.type')
            ->from('ProjectSchedule s')
            ->andWhere('s.project_structure_id = ?', $project->id)
            ->addOrderBy('s.id DESC')
            ->execute();

        $form = new BaseForm();

        $items = array();

        foreach ( $records as $key => $record )
        {
            $items[$key]['id']                   = $record->id;
            $items[$key]['title']                = $record->title;
            $items[$key]['project_structure_id'] = $record->project_structure_id;
            $items[$key]['type']                 = $record->type;
            $items[$key]['exclude_saturdays']    = $record->exclude_saturdays;
            $items[$key]['exclude_sundays']      = $record->exclude_sundays;
            $items[$key]['start_date']           = date('Y-n-j', strtotime($record->start_date));
            $items[$key]['currency']             = $project->MainInformation->Currency->currency_code;

            $items[$key]['sub_package_id']   = - 1;
            $items[$key]['sub_package_name'] = "";

            if ( $record->sub_package_id > 0 )
            {
                $items[$key]['sub_package_id']   = $record->sub_package_id;
                $items[$key]['sub_package_name'] = $record->SubPackage->name;
            }

            $items[$key]['_csrf_token'] = $form->getCSRFToken();
        }

        array_push($items, array(
            'id'                   => Constants::GRID_LAST_ROW,
            'title'                => '',
            'description'          => '',
            'project_structure_id' => - 1,
            'sub_package_id'       => - 1,
            'sub_package_name'     => '',
            'type'                 => 999999,
            '_csrf_token'          => $form->getCSRFToken()
        ));

        unset( $records );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeProjectScheduleForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->hasParameter('id')
        );

        $startDate = date('Y-m-d');

        $companyProfile = Doctrine_Query::create()
            ->select('c.timezone')
            ->from('myCompanyProfile c')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        $timezone = $companyProfile['timezone'];

        if ( !$projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id')) )
        {
            $this->forward404Unless(
                $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
            );

            $startDate = date('Y-m-d', strtotime($project->MainInformation->start_date));
        }
        else
        {
            $timezone = $projectSchedule->timezone;
        }

        $form = new ProjectScheduleForm($projectSchedule);

        $data = array();

        $data['formValues'] = array(
            'project_schedule[title]'                => $form->getObject()->title,
            'project_schedule[description]'          => $form->getObject()->description,
            'project_schedule[start_date]'           => $form->getObject()->start_date ? date('Y-m-d', strtotime($form->getObject()->start_date)) : $startDate,
            'project_schedule[exclude_saturdays]'    => $form->getObject()->exclude_saturdays,
            'project_schedule[exclude_sundays]'      => $form->getObject()->exclude_sundays,
            'project_schedule[timezone]'             => $timezone,
            'project_schedule[project_structure_id]' => !$projectSchedule ? $project->id : $form->getObject()->project_structure_id,
            'project_schedule[_csrf_token]'          => $form->getCSRFToken()
        );

        $data['timezones'] = Utilities::getTimezones();

        return $this->renderJson($data);
    }

    public function executeProjectScheduleUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $request->hasParameter('id')
        );

        $options = array();

        if ( $request->hasParameter('sid') and $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sid')) )
        {
            $options = array( 'sub_package_id' => $subPackage->id );
        }

        $form = new ProjectScheduleForm($projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id')), $options);

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

    public function executeProjectScheduleDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        $con      = $projectSchedule->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $projectSchedule->delete($con);

            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeGetProjects(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $user = $this->getUser()->getGuardUser();

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => ProjectStructureTable::getProjectsByUser($user, ProjectUserPermission::STATUS_PROJECT_MANAGEMENT)
        ));
    }

    public function executeGetProjectManagementGroupList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $form = new ProjectManagementGroupsAssignmentForm($projectStructure);

        $groupWithProjects = $projectStructure->ProjectManagementGroups;

        $projectGroups = array();

        if ( count($groupWithProjects) > 0 )
        {
            foreach ( $groupWithProjects as $groupWithProject )
            {
                $projectGroups[$groupWithProject->id] = $groupWithProject->id;
            }
        }

        $groups = Doctrine_Query::create()
            ->from('sfGuardGroup u')
            ->orderBy('u.id')
            ->execute();

        // get available user list
        $data = array();

        foreach ( $groups as $group )
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

    public function executeGetNonWorkingDays(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id'))
        );

        $nonWorkingDays = array();

        $c = $projectSchedule->getNonWorkingDays()->count();

        foreach($projectSchedule->getNonWorkingDays() as $i => $nonWorkingDay)
        {
            if($nonWorkingDay)
            {
                if($i == 0)
                    $nonWorkingDay = '#'.date('Y_m_d', strtotime($nonWorkingDay));
                elseif($i == $c-1)
                    $nonWorkingDay = date('Y_m_d', strtotime($nonWorkingDay))."#";
                else
                    $nonWorkingDay = date('Y_m_d', strtotime($nonWorkingDay));

                $nonWorkingDays[$nonWorkingDay] = $nonWorkingDay;
            }
        }

        return $this->renderJson(array(
            'sat_is_holy' => $projectSchedule->exclude_saturdays,
            'sun_is_holy' => $projectSchedule->exclude_sundays,
            'holidays'    => !empty($nonWorkingDays) ? implode("#", $nonWorkingDays) : ""
        ));
    }

    public function executeGetTaskItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id'))
        );

        $pdo  = $projectSchedule->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare('SELECT DISTINCT t.id, t.id AS "idFromDB", t.title AS name, t.description, t.code, t.status, t.duration, t.completed_date AS "completedDate", t.start_date AS start, t.end_date AS end,
        t.progress, t.hours_per_day AS "hoursPerDay", t.total_cost AS "totalCost", t.start_is_milestone AS "startIsMilestone", t.end_is_milestone AS "endIsMilestone", t.depends, t.lft, t.rgt, t.priority, t.level
        FROM ' . ScheduleTaskItemTable::getInstance()->getTableName() . ' t
        WHERE t.project_schedule_id = ' . $projectSchedule->id . ' AND t.temp_deleted IS FALSE
        ORDER BY t.priority, t.lft, t.level');

        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach($items as $key => $item)
        {
            $items[ $key ]['totalCost']     = number_format($item['totalCost'], 2, '.', '');
            $items[ $key ]['completedDate'] = !empty($item['completedDate']) ? strtotime($item['completedDate']) * 1000 : strtotime(date('d-m-Y')) * 1000;
            $items[ $key ]['start']         = strtotime($item['start']) * 1000;
            $items[ $key ]['end']           = strtotime($item['end']) * 1000;
            $items[ $key ]['status']        = ScheduleTaskItem::getStatusTextByStatus($item['status']);
            $items[ $key ]['hasChild']      = ( $item['rgt'] - $item['lft'] > 1 ) ? true : false;
            $items[ $key ]['_csrf_token']   = $form->getCSRFToken();
        }

        return $this->renderJson(array(
            'tasks'            => $items,
            'excludeSun'       => $projectSchedule->exclude_sundays,
            'excludeSat'       => $projectSchedule->exclude_saturdays,
            'selectedRow'      => 0,
            'canWrite'         => true,
            'canWriteOnParent' => true,
            'zoom'             => $projectSchedule->zoom
        ));
    }

    public function executeGetPrintTaskItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id'))
        );

        $pdo  = $projectSchedule->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare('SELECT DISTINCT t.id, t.id AS "idFromDB", t.title AS name, t.description, t.code, t.status, t.duration, t.start_date AS start, t.end_date AS end,
        t.progress, t.hours_per_day AS "hoursPerDay", t.total_cost AS "totalCost", t.start_is_milestone AS "startIsMilestone", t.end_is_milestone AS "endIsMilestone", t.depends, t.lft, t.rgt, t.priority, t.level
        FROM ' . ScheduleTaskItemTable::getInstance()->getTableName() . ' t
        WHERE t.project_schedule_id = ' . $projectSchedule->id . ' AND t.temp_deleted IS FALSE
        ORDER BY t.priority, t.lft, t.level');

        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach($items as $key => $item)
        {
            $items[ $key ]['totalCost']   = number_format($item['totalCost'], 2, '.', '');
            $items[ $key ]['start']       = strtotime($item['start']) * 1000;
            $items[ $key ]['end']         = strtotime($item['end']) * 1000;
            $items[ $key ]['status']      = ScheduleTaskItem::getStatusTextByStatus($item['status']);
            $items[ $key ]['hasChild']    = ( $item['rgt'] - $item['lft'] > 1 ) ? true : false;
            $items[ $key ]['_csrf_token'] = $form->getCSRFToken();
        }

        return $this->renderJson(array(
            'tasks'            => $items,
            'excludeSun'       => $projectSchedule->exclude_sundays,
            'excludeSat'       => $projectSchedule->exclude_saturdays,
            'selectedRow'      => 0,
            'canWrite'         => false,
            'canWriteOnParent' => false,
            'zoom'             => $projectSchedule->zoom
        ));
    }

    public function executeGetActualProgressTaskItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id'))
        );

        $pdo  = $projectSchedule->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare('SELECT DISTINCT t.id, t.id AS "idFromDB", t.title AS name, t.description, t.code, t.status, t.duration, t.completed_date, t.start_date AS start, t.end_date AS end,
        t.progress, t.hours_per_day AS "hoursPerDay", t.total_cost AS "totalCost", t.start_is_milestone AS "startIsMilestone", t.end_is_milestone AS "endIsMilestone", t.depends, t.lft, t.rgt, t.priority, t.level
        FROM ' . ScheduleTaskItemTable::getInstance()->getTableName() . ' t
        WHERE t.project_schedule_id = ' . $projectSchedule->id . ' AND t.temp_deleted IS FALSE
        ORDER BY t.priority, t.lft, t.level');

        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        $nonWorkingDays = $projectSchedule->getNonWorkingDays();
        $excludeSaturdays = $projectSchedule->exclude_saturdays;
        $excludeSundays = $projectSchedule->exclude_sundays;

        $parentsDuration = array();

        $curParentId = null;

        foreach($items as $key => $item)
        {
            $hasChild = ( $item['rgt'] - $item['lft'] > 1 ) ? true : false;

            if(($hasChild and $item['level'] > 0) or (!$hasChild and $item['level'] == 1))
            {
                $curParentId = $item['id'];
            }

            $actualDuration = ScheduleTaskItemTable::getActualTaskDuration($item['start'],$item['completed_date'],$item['progress'], $nonWorkingDays, $excludeSaturdays, $excludeSundays);

            $actualDuration = ($item['progress'] != 0 && $actualDuration != 0) ? $actualDuration : $item['duration'];

            $duration = $actualDuration;

            if((($hasChild and $item['level'] > 0) or (!$hasChild and $item['level'] == 1) ) and !isset($parentsDuration[$item['id']]))
            {
                $parentsDuration[$item['id']] = array(
                    'start_date'        => $item['start'],
                    'duration'          => $duration,
                    'children_duration' => (!$hasChild and $item['level'] == 1) ? $duration : 0
                );

            }

            if(!$hasChild and $item['level'] > 1)
            {
                $parentsDuration[$curParentId]['children_duration'] += $duration;
            }

            $items[ $key ]['totalCost']   = number_format($item['totalCost'], 2, '.', '');
            $items[ $key ]['start']       = strtotime($item['start']) * 1000;
            $items[ $key ]['end']         = strtotime($item['end']) * 1000;
            $items[ $key ]['duration']    = $duration;
            $items[ $key ]['status']      = ScheduleTaskItem::getStatusTextByStatus($item['status']);
            $items[ $key ]['hasChild']    = $hasChild;
            $items[ $key ]['_csrf_token'] = $form->getCSRFToken();
        }

        $parentsLatestDuration = array();

        $totalDuration = 0;

        foreach($parentsDuration as $id => $data)
        {
            $duration = $data['children_duration'] > $data['duration'] ? $data['children_duration'] : $data['duration'];

            $parentsLatestDuration[$id]['duration'] = $duration;
            $parentsLatestDuration[$id]['end_date'] = Utilities::computeEndDateByDuration($data['start_date'], $duration, $nonWorkingDays, $excludeSaturdays, $excludeSundays);

            $totalDuration += $duration;
        }

        unset($parentsDuration);

        foreach($items as $key => $item)
        {
            if($item['level'] == 0 && $totalDuration > $item['duration'])
            {
                $items[$key]['duration'] = $totalDuration;
                $endDate = Utilities::computeEndDateByDuration($item['start'], $totalDuration, $nonWorkingDays, $excludeSaturdays, $excludeSundays);
                $items[$key]['end'] = strtotime($endDate) * 1000;
            }

            if(array_key_exists($item['id'], $parentsLatestDuration))
            {
                $items[$key]['duration'] = $parentsLatestDuration[$item['id']]['duration'];
                $endDate = $parentsLatestDuration[$item['id']]['end_date'];
                $items[$key]['end'] = strtotime($endDate) * 1000;

            }
        }

        return $this->renderJson(array(
            'tasks'            => $items,
            'excludeSun'       => $projectSchedule->exclude_sundays,
            'excludeSat'       => $projectSchedule->exclude_saturdays,
            'selectedRow'      => 0,
            'canWrite'         => false,
            'canWriteOnParent' => false,
            'zoom'             => $projectSchedule->zoom
        ));
    }

    public function executeTaskItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $projectSchedule = ProjectScheduleTable::getInstance()->find($request->getParameter('id'))
        );

        $con = $projectSchedule->getTable()->getConnection();
        $items = array();

        try
        {
            $pdo = $con->getDbh();

            $con->beginTransaction();

            $data = json_decode($request->getParameter('prj'));

            ScheduleTaskItemTable::saveFromGantt($data->tasks, $projectSchedule, $con);

            $con->commit();

            $stmt = $pdo->prepare('SELECT DISTINCT t.id, t.id AS "idFromDB", t.title AS name, t.description, t.code, t.status, t.duration, t.completed_date AS "completedDate", t.start_date AS start, t.end_date AS end,
            t.progress, t.hours_per_day AS "hoursPerDay", t.total_cost AS "totalCost", t.start_is_milestone AS "startIsMilestone", t.end_is_milestone AS "endIsMilestone", t.depends, t.lft, t.rgt, t.priority, t.level
            FROM ' . ScheduleTaskItemTable::getInstance()->getTableName() . ' t
            WHERE t.project_schedule_id = ' . $projectSchedule->id . ' AND t.temp_deleted IS FALSE
            ORDER BY t.priority, t.lft, t.level');

            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $form = new BaseForm();

            foreach($items as $key => $item)
            {
                $items[ $key ]['totalCost']     = number_format($item['totalCost'], 2, '.', '');
                $items[ $key ]['completedDate'] = !empty($item['completedDate']) ? strtotime($item['completedDate']) * 1000 : strtotime(date('d-m-Y')) * 1000;
                $items[ $key ]['start']         = strtotime($item['start']) * 1000;
                $items[ $key ]['end']           = strtotime($item['end']) * 1000;
                $items[ $key ]['status']        = ScheduleTaskItem::getStatusTextByStatus($item['status']);
                $items[ $key ]['hasChild']      = ( $item['rgt'] - $item['lft'] > 1 ) ? true : false;
                $items[ $key ]['_csrf_token']   = $form->getCSRFToken();
            }

            if(property_exists($data, 'zoom'))
            {
                $projectSchedule->zoom = strtolower($data->zoom);
                $projectSchedule->save();
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

        return $this->renderJson(array(
            'success'    => $success,
            'errorMsg'   => $errorMsg,
            'data' => array(
                'tasks'            => $items,
                'selectedRow'      => (property_exists($data, "selectedRow")) ? $data->selectedRow : null,
                'splitterPosition' => $data->splitterPosition,
                'zoom'             => $projectSchedule->zoom,
                'canWrite'         => $data->canWrite,
                'canWriteOnParent' => $data->canWriteOnParent
            )
        ));
    }

    public function executeDateCompletedForm(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $item = Doctrine_Core::getTable('ScheduleTaskItem')->find($request->getParameter('id'))
        );

        if ( $item->date_completed )
        {
            $isCompleted   = true;
            $dateCompleted = date('Y-m-d', strtotime($item->date_completed));
        }
        else
        {
            $isCompleted   = false;
            $dateCompleted = date('Y-m-d');
        }

        $form = new DateCompletedForm($item);

        $data = array();

        $data['formValues'] = array(
            'date_completed[is_completed]'   => $isCompleted,
            'date_completed[date_completed]' => $dateCompleted,
            'date_completed[_csrf_token]'    => $form->getCSRFToken()
        );

        return $this->renderJson($data);
    }

    public function executeDateCompletedUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $item = Doctrine_Core::getTable('ScheduleTaskItem')->find($request->getParameter('id'))
        );

        $form = new DateCompletedForm($item);

        if ( $this->isFormValid($request, $form) )
        {
            $scheduleTaskItem = $form->save();
            $data             = array(
                'id'             => $scheduleTaskItem->id,
                'date_completed' => $scheduleTaskItem->date_completed,
                'a_dt'           => $scheduleTaskItem->getActualTaskDuration()
            );
            $errors           = null;
            $success          = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
            $data    = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'data' => $data ));
    }

    public function executeGetTaggedBillItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $taskItem = Doctrine_Core::getTable('ScheduleTaskItem')->find($request->getParameter('id'))
        );

        $pdo = $taskItem->getTable()->getConnection()->getDbh();

        $projectId      = $taskItem->ProjectSchedule->project_structure_id;
        $postContractId = $taskItem->ProjectSchedule->ProjectStructure->PostContract->id;
        $subPackageId   = $taskItem->ProjectSchedule->sub_package_id;

        if ( $subPackageId > 0 )
        {
            $revision = SubPackagePostContractClaimRevisionTable::getCurrentProjectRevision($taskItem->ProjectSchedule->SubPackage);

            $itemSql = "SELECT DISTINCT t.bill_column_setting_id,
            CASE p.type
                WHEN " . BillItem::TYPE_HEADER . " THEN NULL
                WHEN " . BillItem::TYPE_HEADER_N . " THEN NULL
                WHEN " . BillItem::TYPE_NOID . " THEN NULL
                ELSE t.id
            END AS schedule_task_item_bill_item_id,
            CASE p.type
                WHEN " . BillItem::TYPE_HEADER . " THEN NULL
                WHEN " . BillItem::TYPE_HEADER_N . " THEN NULL
                WHEN " . BillItem::TYPE_NOID . " THEN NULL
                ELSE ROUND(COALESCE(pt.qty_per_unit, 0) * COALESCE(si.rate, 0), 2)
            END AS total_per_unit,
            p.id AS bill_item_id, p.element_id, p.root_id, p.description, p.type,
            p.uom_id, uom.symbol AS uom_symbol, p.level, p.priority, p.lft
            FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.sub_package_type_reference_id = r.id AND x.standard_type_reference_id IS NULL
            JOIN " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " AS t ON x.schedule_task_item_bill_item_id = t.id AND r.bill_column_setting_id = t.bill_column_setting_id
            LEFT JOIN " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " si ON t.bill_item_id = si.bill_item_id AND si.sub_package_id = r.sub_package_id
            LEFT JOIN " . PostContractBillItemTypeTable::getInstance()->getTableName() . " pt ON pt.post_contract_id = " . $postContractId . " AND pt.bill_item_id = si.bill_item_id AND pt.bill_column_setting_id = t.bill_column_setting_id
            LEFT JOIN " . ScheduleBillItemProductivityTable::getInstance()->getTableName() . " AS prod ON prod.schedule_task_item_bill_item_id = t.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " c ON t.bill_item_id = c.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON p.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
            WHERE r.sub_package_id = " . $subPackageId . " AND s.root_id = " . $projectId . " AND c.root_id = p.root_id AND c.element_id = p.element_id
            AND t.schedule_task_item_id = " . $taskItem->id . "
            AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
            AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
            AND e.deleted_at IS NULL AND s.deleted_at IS NULL
            ORDER BY t.bill_column_setting_id, p.element_id, p.priority, p.lft, p.level ASC";

            $productivitySql = "SELECT DISTINCT t.id, COALESCE(prod.productivity, 0) AS productivity, COALESCE(prod.productivity_type, 1) AS productivity_type,
            COALESCE(prod.number_of_gang, 0) AS number_of_gang, COALESCE(prod.duration_days, 0) AS duration_days,
            COALESCE(prod.duration_hours, 0) AS duration_hours
            FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.sub_package_type_reference_id = r.id AND x.standard_type_reference_id IS NULL
            JOIN " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " AS t ON x.schedule_task_item_bill_item_id = t.id AND r.bill_column_setting_id = t.bill_column_setting_id
            JOIN " . ScheduleBillItemProductivityTable::getInstance()->getTableName() . " AS prod ON prod.schedule_task_item_bill_item_id = t.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON t.bill_item_id = i.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
            WHERE r.sub_package_id = " . $subPackageId . " AND s.root_id = " . $projectId . "
            AND t.schedule_task_item_id = " . $taskItem->id . "
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND e.deleted_at IS NULL AND s.deleted_at IS NULL
            ORDER BY t.id ASC";

            $billSql = "SELECT DISTINCT r.bill_column_setting_id, bc.name AS bill_column_setting_name, s.id, s.title, s.lft, bt.type
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
            JOIN " .BillTypeTable::getInstance()->getTableName(). " AS bt ON bt.project_structure_id = s.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON e.project_structure_id = s.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " AS i ON i.element_id = e.id
            JOIN " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " AS t ON t.bill_item_id = i.id
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.schedule_task_item_bill_item_id = t.id
            JOIN " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r ON x.sub_package_type_reference_id = r.id AND x.standard_type_reference_id IS NULL
            JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bc ON r.bill_column_setting_id = bc.id AND t.bill_column_setting_id = r.bill_column_setting_id
            WHERE s.root_id = " . $projectId . " AND t.schedule_task_item_id = " . $taskItem->id . " AND r.sub_package_id = " . $subPackageId . "
            AND s.deleted_at IS NULL AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            AND i.deleted_at IS NULL AND bc.deleted_at IS NULL AND bt.deleted_at IS NULL
            GROUP BY r.bill_column_setting_id, bc.name, s.id, s.title, s.lft, bt.type ORDER BY s.lft ASC";

            $totalUnitSql = "SELECT t.id, COALESCE(COUNT(x.id), 0) AS total_unit
            FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.sub_package_type_reference_id = r.id AND x.standard_type_reference_id IS NULL
            JOIN " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " AS t ON x.schedule_task_item_bill_item_id = t.id AND t.bill_column_setting_id = r.bill_column_setting_id
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON t.bill_item_id = i.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
            WHERE s.root_id = " . $projectId . " AND r.sub_package_id = " . $subPackageId . "
            AND t.schedule_task_item_id = " . $taskItem->id . "
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND e.deleted_at IS NULL AND s.deleted_at IS NULL GROUP BY t.id";

            $upToDateClaimSql = "SELECT t.id,
            ROUND(COALESCE(cr.up_to_date_percentage,0),2) AS up_to_date_percentage,
            ROUND(COALESCE(cr.up_to_date_amount,0),2) AS up_to_date_amount
            FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r
            JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " pcr ON r.bill_column_setting_id = r.bill_column_setting_id AND pcr.counter = r.counter
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.sub_package_type_reference_id = r.id AND x.standard_type_reference_id IS NULL
            JOIN " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " AS t ON x.schedule_task_item_bill_item_id = t.id AND t.bill_column_setting_id = r.bill_column_setting_id
            LEFT JOIN " . SubPackagePostContractStandardClaimTable::getInstance()->getTableName() . " cr ON cr.bill_item_id = t.bill_item_id AND cr.claim_type_ref_id = pcr.id AND cr.revision_id = " . $revision['id'] . "
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON t.bill_item_id = i.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
            WHERE s.root_id = " . $projectId . " AND r.sub_package_id = " . $subPackageId . " AND pcr.post_contract_id = " . $postContractId . "
            AND t.schedule_task_item_id = " . $taskItem->id . " AND cr.up_to_date_percentage <> 0 AND cr.up_to_date_amount <> 0
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND e.deleted_at IS NULL AND s.deleted_at IS NULL GROUP BY t.id, cr.up_to_date_percentage, cr.up_to_date_amount";
        }
        else
        {
            $revision = PostContractClaimRevisionTable::getCurrentProjectRevision($taskItem->ProjectSchedule->ProjectStructure->PostContract);

            $itemSql = "SELECT DISTINCT t.bill_column_setting_id,
            CASE p.type
                WHEN " . BillItem::TYPE_HEADER . " THEN NULL
                WHEN " . BillItem::TYPE_HEADER_N . " THEN NULL
                WHEN " . BillItem::TYPE_NOID . " THEN NULL
                ELSE t.id
            END AS schedule_task_item_bill_item_id,
            CASE p.type
                WHEN " . BillItem::TYPE_HEADER . " THEN NULL
                WHEN " . BillItem::TYPE_HEADER_N . " THEN NULL
                WHEN " . BillItem::TYPE_NOID . " THEN NULL
                ELSE pt.total_per_unit
            END AS total_per_unit,
            p.id AS bill_item_id, p.element_id, p.root_id, p.description, p.type,
            p.uom_id, uom.symbol AS uom_symbol, p.level, p.priority, p.lft
            FROM " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " r
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.standard_type_reference_id = r.id AND x.sub_package_type_reference_id IS NULL
            JOIN " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " AS t ON x.schedule_task_item_bill_item_id = t.id AND r.bill_column_setting_id = t.bill_column_setting_id
            LEFT JOIN " . PostContractBillItemTypeTable::getInstance()->getTableName() . " pt ON pt.post_contract_id = r.post_contract_id AND pt.bill_item_id = t.bill_item_id AND pt.bill_column_setting_id = t.bill_column_setting_id
            LEFT JOIN " . ScheduleBillItemProductivityTable::getInstance()->getTableName() . " AS prod ON prod.schedule_task_item_bill_item_id = t.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " c ON t.bill_item_id = c.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON p.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
            WHERE r.post_contract_id = " . $postContractId . " AND s.root_id = " . $projectId . " AND c.root_id = p.root_id AND c.element_id = p.element_id
            AND t.schedule_task_item_id = " . $taskItem->id . "
            AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
            AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
            AND e.deleted_at IS NULL AND s.deleted_at IS NULL
            ORDER BY t.bill_column_setting_id, p.element_id, p.priority, p.lft, p.level ASC";

            $productivitySql = "SELECT DISTINCT t.id, COALESCE(prod.productivity, 0) AS productivity, COALESCE(prod.productivity_type, 1) AS productivity_type,
            COALESCE(prod.number_of_gang, 0) AS number_of_gang, COALESCE(prod.duration_days, 0) AS duration_days,
            COALESCE(prod.duration_hours, 0) AS duration_hours
            FROM " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " r
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.standard_type_reference_id = r.id AND x.sub_package_type_reference_id IS NULL
            JOIN " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " AS t ON x.schedule_task_item_bill_item_id = t.id AND r.bill_column_setting_id = t.bill_column_setting_id
            JOIN " . ScheduleBillItemProductivityTable::getInstance()->getTableName() . " AS prod ON prod.schedule_task_item_bill_item_id = t.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON t.bill_item_id = i.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
            WHERE r.post_contract_id = " . $postContractId . " AND s.root_id = " . $projectId . "
            AND t.schedule_task_item_id = " . $taskItem->id . "
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND e.deleted_at IS NULL AND s.deleted_at IS NULL
            ORDER BY t.id ASC";

            $billSql = "SELECT DISTINCT r.bill_column_setting_id, bc.name AS bill_column_setting_name, s.id, s.title, s.lft, bt.type
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " AS s
            JOIN " .BillTypeTable::getInstance()->getTableName(). " AS bt ON bt.project_structure_id = s.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " AS e ON e.project_structure_id = s.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " AS i ON i.element_id = e.id
            JOIN " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " AS t ON t.bill_item_id = i.id
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.schedule_task_item_bill_item_id = t.id
            JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " r ON x.standard_type_reference_id = r.id AND x.sub_package_type_reference_id IS NULL
            JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " bc ON r.bill_column_setting_id = bc.id AND t.bill_column_setting_id = r.bill_column_setting_id
            WHERE s.root_id = " . $projectId . " AND t.schedule_task_item_id = " . $taskItem->id . " AND r.post_contract_id = " . $postContractId . "
            AND s.deleted_at IS NULL AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
            AND i.deleted_at IS NULL AND bc.deleted_at IS NULL AND bt.deleted_at IS NULL
            GROUP BY r.bill_column_setting_id, bc.name, s.id, s.title, s.lft, bt.type ORDER BY s.lft ASC";

            $totalUnitSql = "SELECT t.id, COALESCE(COUNT(x.id), 0) AS total_unit
            FROM " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " r
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.standard_type_reference_id = r.id AND x.sub_package_type_reference_id IS NULL
            JOIN " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " AS t ON x.schedule_task_item_bill_item_id = t.id AND t.bill_column_setting_id = r.bill_column_setting_id
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON t.bill_item_id = i.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
            WHERE s.root_id = " . $projectId . " AND r.post_contract_id = " . $postContractId . "
            AND t.schedule_task_item_id = " . $taskItem->id . "
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND e.deleted_at IS NULL AND s.deleted_at IS NULL GROUP BY t.id";

            $upToDateClaimSql = "SELECT t.id,
            ROUND(COALESCE(cr.up_to_date_percentage,0),2) AS up_to_date_percentage,
            ROUND(COALESCE(cr.up_to_date_amount,0),2) AS up_to_date_amount
            FROM " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " r
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.standard_type_reference_id = r.id AND x.sub_package_type_reference_id IS NULL
            JOIN " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " AS t ON x.schedule_task_item_bill_item_id = t.id AND t.bill_column_setting_id = r.bill_column_setting_id
            LEFT JOIN " . PostContractStandardClaimTable::getInstance()->getTableName() . " cr ON cr.bill_item_id = t.bill_item_id AND cr.claim_type_ref_id = r.id AND cr.revision_id = " . $revision['id'] . "
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON t.bill_item_id = i.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id
            WHERE s.root_id = " . $projectId . " AND r.post_contract_id = " . $postContractId . "
            AND t.schedule_task_item_id = " . $taskItem->id . " AND cr.up_to_date_percentage <> 0 AND cr.up_to_date_amount <> 0
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL
            AND e.deleted_at IS NULL AND s.deleted_at IS NULL GROUP BY t.id, cr.up_to_date_percentage, cr.up_to_date_amount";
        }

        $stmt = $pdo->prepare($itemSql);

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare($productivitySql);

        $stmt->execute();

        $productivities = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        /*
         * select bills
         */
        $stmt = $pdo->prepare($billSql);

        $stmt->execute();

        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /*
         * select elements
         */
        $selectDistinctElementSql = "SELECT DISTINCT e.id, e.description, e.priority
            FROM " . BillElementTable::getInstance()->getTableName() . " AS e
            JOIN " . BillItemTable::getInstance()->getTableName() . " AS i ON i.element_id = e.id
            JOIN " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " AS t ON t.bill_item_id = i.id
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.schedule_task_item_bill_item_id = t.id
            WHERE e.project_structure_id = :bill_id AND t.schedule_task_item_id = " . $taskItem->id . "
            AND e.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL ORDER BY e.priority ASC";

        //get total units
        $stmt = $pdo->prepare($totalUnitSql);

        $stmt->execute();

        $totalUnits = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        //get up to date claims
        $stmt = $pdo->prepare($upToDateClaimSql);

        $stmt->execute();

        $upToDateClaims = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        $form    = new BaseForm();
        $results = array();

        foreach ( $bills as $bill )
        {
            $stmt = $pdo->prepare($selectDistinctElementSql);

            $stmt->execute(array(
                'bill_id' => $bill['id']
            ));

            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $elements as $element )
            {
                $result = array(
                    'id'                      => 'bill-' . $bill['id'] . '-' . $bill['bill_column_setting_id'] . '-elem' . $element['id'],
                    'description'             => $bill['title'] . " > " . $bill['bill_column_setting_name'] . " > " . $element['description'],
                    'type'                    => - 1,
                    'level'                   => 0,
                    'uom_id'                  => - 1,
                    'uom_symbol'              => '',
                    'qty_per_unit'            => 0,
                    'total_unit'              => 0,
                    'total_qty'               => 0,
                    'up_to_date_claim_amount' => 0
                );

                array_push($results, $result);

                $billItem = array( 'id' => - 1, 'bill_column_setting_id' => $bill['bill_column_setting_id'] );

                foreach ( $items as $key => $item )
                {
                    if ( $item['element_id'] == $element['id'] && $item['bill_column_setting_id'] == $bill['bill_column_setting_id'] )
                    {
                        $billItem['bill_column_setting_id'] = $bill['bill_column_setting_id'];
                        $billItem['description']            = $item['description'];
                        $billItem['type']                   = $item['type'];
                        $billItem['bill_type']              = $bill['type'];
                        $billItem['level']                  = $item['level'];
                        $billItem['uom_symbol']             = $item['uom_symbol'];
                        $billItem['_csrf_token']            = $form->getCSRFToken();

                        $billItem['contract_amt']      = 0;
                        $billItem['productivity']      = 0;
                        $billItem['productivity_type'] = 0;
                        $billItem['number_of_gang']    = 0;
                        $billItem['duration_hours']    = 0;
                        $billItem['duration_days']     = 0;

                        $billItem['up_to_date_claim_amount']     = 0;
                        $billItem['up_to_date_claim_percentage'] = 0;

                        $billItem['qty_per_unit'] = 0;
                        $billItem['total_unit']   = 0;
                        $billItem['total_qty']    = 0;

                        if ( $item['type'] != BillItem::TYPE_HEADER && $item['type'] != BillItem::TYPE_HEADER_N && $item['type'] != BillItem::TYPE_NOID )
                        {
                            $billItem['id'] = $item['schedule_task_item_bill_item_id'];
                            $qtyPerUnit     = ScheduleTaskItemBillItemTable::getQuantityPerUnitById($item['schedule_task_item_bill_item_id'], $subPackageId);
                            $totalUnit      = array_key_exists($item['schedule_task_item_bill_item_id'], $totalUnits) ? $totalUnits[$item['schedule_task_item_bill_item_id']] : 0;
                            $totalQty       = $qtyPerUnit * $totalUnit;

                            $billItem['qty_per_unit'] = $qtyPerUnit;
                            $billItem['total_unit']   = $totalUnit;
                            $billItem['total_qty']    = $totalQty;

                            $billItem['contract_amt']      = $totalUnit * $item['total_per_unit'];
                            $billItem['productivity']      = array_key_exists($billItem['id'], $productivities) ? $productivities[$billItem['id']][0]['productivity'] : 0;
                            $billItem['productivity_type'] = array_key_exists($billItem['id'], $productivities) ? $productivities[$billItem['id']][0]['productivity_type'] : ScheduleBillItemProductivity::TYPE_UNIT_PER_HOUR;
                            $billItem['number_of_gang']    = array_key_exists($billItem['id'], $productivities) ? (string) $productivities[$billItem['id']][0]['number_of_gang'] : 0;
                            $billItem['duration_hours']    = array_key_exists($billItem['id'], $productivities) ? $productivities[$billItem['id']][0]['duration_hours'] : 0;
                            $billItem['duration_days']     = array_key_exists($billItem['id'], $productivities) ? $productivities[$billItem['id']][0]['duration_days'] : 0;

                            if( array_key_exists($billItem['id'], $upToDateClaims) )
                            {
                                $billItem['up_to_date_claim_percentage'] = count($upToDateClaims[ $billItem['id'] ]) > 1 ? 'MULTI' : $upToDateClaims[ $billItem['id'] ][0]['up_to_date_percentage'];

                                $billItem['up_to_date_claim_amount'] = 0;
                                $scheduleTaskItemBillItem            = Doctrine_Core::getTable('ScheduleTaskItemBillItem')->find($billItem['id']);

                                foreach(ScheduleTaskItemBillItemTable::getTaggedUnits($scheduleTaskItemBillItem, 'up_to_date_amount') as $typeReference)
                                {
                                    $billItem['up_to_date_claim_amount'] += $typeReference['value'];
                                }
                            }
                        }
                        else
                        {
                            $billItem['id'] = 'head-' . $item['bill_column_setting_id'] . "-" . $item['bill_item_id'];
                        }

                        array_push($results, $billItem);

                        unset( $item, $items[$key] );
                    }
                }
            }
        }

        array_push($results, array(
            'id'                      => Constants::GRID_LAST_ROW,
            'description'             => '',
            'level'                   => 0,
            'uom_id'                  => - 1,
            'uom_symbol'              => '',
            'qty_per_unit'            => 0,
            'total_unit'              => 0,
            'total_qty'               => 0,
            'up_to_date_claim_amount' => 0,
            '_csrf_token'             => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $results
        ));
    }

    public function executeTaggedBillItemDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $scheduleTaskItemBillItem = Doctrine_Core::getTable('ScheduleTaskItemBillItem')->find($request->getParameter('id'))
        );

        $errorMsg = null;
        $con      = $scheduleTaskItemBillItem->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $scheduleTaskItemBillItem->delete($con);

            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();

            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeGetBillList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id'))
        );

        $pdo = $projectSchedule->getTable()->getConnection()->getDbh();

        if ( $projectSchedule->sub_package_id )
        {
            $sql = "SELECT bill.id, bill.title, t.type AS bill_type, bill.lft, bill.level
            FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON e.project_structure_id = bill.id AND bill.deleted_at IS NULL
            JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = bill.id
            JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = bill.id
            WHERE rate.sub_package_id = " . $projectSchedule->sub_package_id . " AND t.deleted_at IS NULL
            AND bill.root_id = " . $projectSchedule->project_structure_id . "
            AND bill.type = " . ProjectStructure::TYPE_BILL . "
            AND bls.deleted_at IS NULL GROUP BY bill.id, bill.title, bill.type, bill.level, t.type,
            t.status, bls.id ORDER BY bill.id ASC";
        }
        else
        {
            $project = $projectSchedule->ProjectStructure;
            $tenderAlternative = $project->getAwardedTenderAlternative();

            $tenderAlternativeJoinSql = "";
            $tenderAlternativeWhereSql = "";

            if($tenderAlternative)
            {
                $tenderAlternativeJoinSql = " JOIN ".TenderAlternativeTable::getInstance()->getTableName()." ta ON ta.project_structure_id = project.id
                    JOIN ".TenderAlternativeBillTable::getInstance()->getTableName()." tax ON tax.tender_alternative_id = ta.id AND tax.project_structure_id = bill.id ";

                $tenderAlternativeWhereSql = " AND ta.id = ".$tenderAlternative->id." AND ta.deleted_at IS NULL AND ta.project_revision_deleted_at IS NULL ";
            }

            $sql = "SELECT DISTINCT bill.id, bill.title, t.type AS bill_type, bill.lft, bill.level
                FROM " . ProjectStructureTable::getInstance()->getTableName() . " project
                JOIN " . ProjectMainInformationTable::getInstance()->getTableName() . " m ON m.project_structure_id = project.id
                JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON bill.root_id = m.project_structure_id
                ".$tenderAlternativeJoinSql."
                LEFT JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = bill.id
                WHERE project.id = " . $projectSchedule->project_structure_id . " AND m.status = " . ProjectMainInformation::STATUS_POSTCONTRACT . "
                AND bill.type = " . ProjectStructure::TYPE_BILL . "
                ".$tenderAlternativeWhereSql."
                AND project.deleted_at IS NULL AND m.deleted_at IS NULL
                AND bill.deleted_at IS NULL AND t.deleted_at IS NULL ORDER BY bill.lft, bill.level ASC";
        }

        $stmt = $pdo->prepare($sql);

        $stmt->execute();

        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        array_push($bills, array(
            'id'    => Constants::GRID_LAST_ROW,
            'title' => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $bills
        ));
    }

    public function executeGetTypeReferenceList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $scheduleTaskItem = Doctrine_Core::getTable('ScheduleTaskItem')->find($request->getParameter('id')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid'))
        );

        $billColumnSettingItems = array();
        $records                = array();
        $form                   = new BaseForm();

        $subPackageId = $scheduleTaskItem->ProjectSchedule->sub_package_id;

        if ( $subPackageId )
        {
            $pdo = $scheduleTaskItem->getTable()->getConnection()->getDbh();

            $stmt = $pdo->prepare("SELECT stype.bill_column_setting_id AS id, type_ref.id as type_ref_id, type_ref.new_name, stype.sub_package_id, stype.bill_column_setting_id, stype.counter
            FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
            LEFT JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " type_ref ON type_ref.bill_column_setting_id = stype.bill_column_setting_id AND type_ref.counter = stype.counter
            LEFT JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = stype.bill_column_setting_id
            WHERE cs.project_structure_id = " . $bill->id . " AND stype.sub_package_id = " . $subPackageId . " ORDER BY stype.bill_column_setting_id, stype.counter ASC");

            $stmt->execute();

            $columnSettingTypeRefs = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT cs.id, cs.name
            FROM " . BillColumnSettingTable::getInstance()->getTableName() . " cs
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON bill.id = cs.project_structure_id AND bill.deleted_at IS NULL
            JOIN " . SubPackageTable::getInstance()->getTableName() . " sp ON bill.root_id = sp.project_structure_id
            JOIN " . PostContractTable::getInstance()->getTableName() . " pc ON pc.project_structure_id = sp.project_structure_id
            LEFT JOIN " . PostContractBillItemTypeTable::getInstance()->getTableName() . " type ON type.post_contract_id = pc.id AND type.bill_column_setting_id = cs.id
            WHERE bill.id = " . $bill->id . " AND cs.deleted_at IS NULL AND sp.deleted_at IS NULL AND sp.id = " . $subPackageId . " GROUP BY cs.id ORDER BY cs.id ASC");

            $stmt->execute();

            $billColumnSettings = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

            foreach ( $columnSettingTypeRefs as $columnId => $typeRefs )
            {
                $column = ( array_key_exists($columnId, $billColumnSettings) ) ? $billColumnSettings[$columnId][0] : false;

                if ( $column )
                {
                    array_push($records, array(
                        'id'          => 'type' . '-' . $columnId,
                        'description' => $column['name'],
                        'level'       => 0
                    ));

                    if ( count($typeRefs) )
                    {
                        foreach ( $typeRefs as $typeRef )
                        {
                            array_push($records, array(
                                'id'            => $columnId . '-' . $typeRef['counter'],
                                'description'   => ( strlen($typeRef['new_name']) ) ? $typeRef['new_name'] : 'Unit ' . $typeRef['counter'],
                                'level'         => 1,
                                'relation_name' => $column['name'],
                                'relation_id'   => $columnId,
                                '_csrf_token'   => $form->getCSRFToken()
                            ));
                        }
                    }

                }
            }
        }
        else
        {
            $typeItems = DoctrineQuery::create()->select('t.id, t.post_contract_id, t.bill_column_setting_id, t.counter, t.new_name')
                ->from('PostContractStandardClaimTypeReference t')
                ->leftJoin('t.BillColumnSetting cs')
                ->where('t.post_contract_id = ? AND cs.project_structure_id = ?', array( $bill->getRoot()->PostContract->id, $bill->id ))
                ->fetchArray();

            foreach ( $typeItems as $typeItem )
            {
                $billColumnSettingItems[$typeItem['bill_column_setting_id']][$typeItem['counter']] = array(
                    'id'       => $typeItem['id'],
                    'new_name' => $typeItem['new_name']
                );
            }

            $billColumnSettings = DoctrineQuery::create()->select('cs.*')
                ->from('BillColumnSetting cs')
                ->where('cs.project_structure_id = ? ', $bill->id)
                ->fetchArray();

            foreach ( $billColumnSettings as $column )
            {
                $count = $column['quantity'];

                array_push($records, array(
                    'id'          => 'type' . '-' . $column['id'],
                    'description' => $column['name'],
                    'level'       => 0
                ));

                for ( $i = 1; $i <= $count; $i ++ )
                {
                    $record['id']            = $column['id'] . '-' . $i;
                    $record['description']   = 'Unit ' . $i;
                    $record['relation_id']   = $column['id'];
                    $record['relation_name'] = $column['name'];
                    $record['level']         = 1;
                    $record['_csrf_token']   = $form->getCSRFToken();

                    if ( array_key_exists($column['id'], $billColumnSettingItems) and array_key_exists($i, $billColumnSettingItems[$column['id']]) )
                    {
                        if ( $billColumnSettingItems[$column['id']][$i]['new_name'] != null and strlen($billColumnSettingItems[$column['id']][$i]['new_name']) > 0 )
                        {
                            $record['description'] = $billColumnSettingItems[$column['id']][$i]['new_name'];
                        }
                    }

                    array_push($records, $record);

                    unset( $record );
                }
            }

            unset( $billColumnSettings );
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'level'       => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetBillElementList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $scheduleTaskItem = Doctrine_Core::getTable('ScheduleTaskItem')->find($request->getParameter('id')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid')) and
            $request->hasParameter("tid") and strlen($request->getParameter("tid")) > 0
        );

        $typeUnitData = explode("-", $request->getParameter("tid"));

        $this->forward404Unless($billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($typeUnitData[0]));

        $typeReference = DoctrineQuery::create()->select('t.id')
            ->from('PostContractStandardClaimTypeReference t')
            ->where('t.bill_column_setting_id = ?', array( $billColumnSetting->id ))
            ->andWhere('t.post_contract_id = ?', array( $bill->getRoot()->PostContract->id ))
            ->andWhere('t.counter = ? ', array( $typeUnitData[1] ))
            ->limit(1)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        if ( !$typeReference )
        {
            $typeReference                         = new PostContractStandardClaimTypeReference();
            $typeReference->bill_column_setting_id = $billColumnSetting->id;
            $typeReference->post_contract_id       = $bill->getRoot()->PostContract->id;
            $typeReference->counter                = $typeUnitData[1];

            $typeReference->save();
        }

        if ( $scheduleTaskItem->ProjectSchedule->sub_package_id )
        {
            $pdo = $scheduleTaskItem->getTable()->getConnection()->getDbh();

            $stmt = $pdo->prepare("SELECT e.id, e.description
            FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " b ON b.id =  e.project_structure_id AND b.deleted_at IS NULL
            WHERE b.id = " . $bill->id . " AND rate.sub_package_id = " . $scheduleTaskItem->ProjectSchedule->sub_package_id . " GROUP BY e.id ORDER BY e.id ASC");

            $stmt->execute();

            $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            $elements = DoctrineQuery::create()->select('e.id, e.description')
                ->from('BillElement e')
                ->where('e.project_structure_id = ?', $bill->id)
                ->addOrderBy('e.priority ASC')
                ->fetchArray();
        }

        array_push($elements, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'priority'    => - 1
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetBillItemList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('eid')) and
            $scheduleTaskItem = Doctrine_Core::getTable('ScheduleTaskItem')->find($request->getParameter('id')) and
            $request->hasParameter("tid") and strlen($request->getParameter("tid")) > 0
        );

        $typeUnitData = explode("-", $request->getParameter("tid"));

        $this->forward404Unless($billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($typeUnitData[0]));

        $pdo = $element->getTable()->getConnection()->getDbh();

        $subPackageId   = $scheduleTaskItem->ProjectSchedule->sub_package_id;
        $postContractId = $element->ProjectStructure->getRoot()->PostContract->id;

        if ( $subPackageId )
        {
            // get process post contract latest bill ref
            $billRefSelector       = 'pcbir.bill_ref_element_no, pcbir.bill_ref_page_no, pcbir.bill_ref_char';
            $postContractJoinTable = 'JOIN ' . PostContractBillItemRateTable::getInstance()->getTableName() . " pcbir ON pcbir.post_contract_id = postc.id AND pcbir.bill_item_id = p.id";

            $excludeTaggedItemsSql = "SELECT DISTINCT t.bill_item_id FROM " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " t
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.schedule_task_item_bill_item_id = t.id
            JOIN " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r ON x.sub_package_type_reference_id = r.id
            WHERE t.schedule_task_item_id <> " . $scheduleTaskItem->id . " AND r.sub_package_id =" . $subPackageId . " AND r.bill_column_setting_id =" . $billColumnSetting->id . "
            AND r.counter =" . $typeUnitData['1'] . " AND x.standard_type_reference_id IS NULL";

            $sql = "SELECT DISTINCT postc.id as post_contract_id, p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, uom.symbol AS uom_symbol,
            p.level, p.priority, p.lft, COALESCE(si.grand_total, 0) AS grand_total,
            pc.supply_rate AS pc_supply_rate, pc.wastage_percentage AS pc_wastage_percentage,
            pc.wastage_amount AS pc_wastage_amount, pc.labour_for_installation AS pc_labour_for_installation,
            pc.other_cost AS pc_other_cost, pc.profit_percentage AS pc_profit_percentage,
            pc.profit_amount AS pc_profit_amount, pc.total AS pc_total,
            {$billRefSelector}
            FROM " . PostContractTable::getInstance()->getTableName() . " postc
            JOIN " . SubPackageTable::getInstance()->getTableName() . " sp ON postc.project_structure_id = sp.project_structure_id
            JOIN " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " si ON si.sub_package_id = sp.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " c ON c.id = si.bill_item_id
            JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt AND c.element_id = p.element_id {$postContractJoinTable}
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            LEFT JOIN " . BillItemPrimeCostRateTable::getInstance()->getTableName() . " pc ON p.id = pc.bill_item_id AND pc.deleted_at IS NULL
            LEFT JOIN " . PostContractBillItemRateTable::getInstance()->getTableName() . " r ON p.id = r.bill_item_id AND r.post_contract_id = " . $postContractId . "
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON p.element_id = e.id
            WHERE postc.id = ".$postContractId." AND sp.id = " . $subPackageId . " AND e.id = " . $element->id . " AND c.root_id = p.root_id
            AND c.id NOT IN (SELECT DISTINCT i.id FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON r.bill_item_id = i.id
            WHERE r.bill_column_setting_id =" . $billColumnSetting->id . " AND i.element_id = " . $element->id . " AND include IS FALSE
            AND i.type <> " . BillItem::TYPE_ITEM_NOT_LISTED . "
            AND r.deleted_at IS NULL AND i.deleted_at IS NULL and i.project_revision_deleted_at IS NULL)
            AND c.id NOT IN (" . $excludeTaggedItemsSql . ")
            AND c.element_id = " . $element->id . "
            AND si.bill_item_id = p.id
            AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
            AND e.deleted_at IS NULL ORDER BY p.element_id, p.priority, p.lft, p.level";

            $taggedItemsSql = "SELECT DISTINCT t.bill_item_id FROM " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " t
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.schedule_task_item_bill_item_id = t.id
            JOIN " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r ON x.sub_package_type_reference_id = r.id
            WHERE t.schedule_task_item_id = " . $scheduleTaskItem->id . " AND t.bill_column_setting_id = " . $billColumnSetting->id . " AND r.sub_package_id =" . $subPackageId . " AND r.bill_column_setting_id =" . $billColumnSetting->id . "
            AND r.counter =" . $typeUnitData['1'] . " AND x.standard_type_reference_id IS NULL";
        }
        else
        {
            $excludeTaggedItemsSql = "SELECT DISTINCT t.bill_item_id FROM " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " t
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.schedule_task_item_bill_item_id = t.id
            JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " r ON x.standard_type_reference_id = r.id
            WHERE t.schedule_task_item_id <> " . $scheduleTaskItem->id . " AND r.post_contract_id =" . $postContractId . "
            AND r.bill_column_setting_id =" . $billColumnSetting->id . "
            AND r.counter =" . $typeUnitData['1'] . " AND x.sub_package_type_reference_id IS NULL";

            $sql = "SELECT DISTINCT p.id, p.element_id, p.root_id, p.description, p.type, p.uom_id, p.level, p.priority, p.lft,
            uom.symbol AS uom_symbol, r.bill_ref_element_no, r.bill_ref_page_no, r.bill_ref_char, COALESCE(r.rate, 0) AS rate, COALESCE(r.grand_total, 0) AS grand_total,
            pc.supply_rate AS pc_supply_rate, pc.wastage_percentage AS pc_wastage_percentage,
            pc.wastage_amount AS pc_wastage_amount, pc.labour_for_installation AS pc_labour_for_installation,
            pc.other_cost AS pc_other_cost, pc.profit_percentage AS pc_profit_percentage,
            pc.profit_amount AS pc_profit_amount, pc.total AS pc_total
            FROM " . BillItemTable::getInstance()->getTableName() . " c
            JOIN " . BillItemTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt AND c.element_id = p.element_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON p.uom_id = uom.id AND uom.deleted_at IS NULL
            LEFT JOIN " . BillItemPrimeCostRateTable::getInstance()->getTableName() . " pc ON p.id = pc.bill_item_id AND pc.deleted_at IS NULL
            LEFT JOIN " . PostContractBillItemRateTable::getInstance()->getTableName() . " r ON p.id = r.bill_item_id AND r.post_contract_id = " . $postContractId . "
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON p.element_id = e.id
            WHERE e.id = " . $element->id . " AND c.root_id = p.root_id
            AND c.id NOT IN (SELECT DISTINCT i.id FROM " . BillItemTable::getInstance()->getTableName() . " i
            JOIN " . BillItemTypeReferenceTable::getInstance()->getTableName() . " r ON r.bill_item_id = i.id
            WHERE r.bill_column_setting_id =" . $billColumnSetting->id . " AND i.element_id = " . $element->id . "
            AND i.type <> " . BillItem::TYPE_ITEM_NOT_LISTED . " AND include IS FALSE
            AND r.deleted_at IS NULL AND i.deleted_at IS NULL and i.project_revision_deleted_at IS NULL)
            AND c.id NOT IN (" . $excludeTaggedItemsSql . ")
            AND c.project_revision_deleted_at IS NULL AND c.deleted_at IS NULL
            AND p.project_revision_deleted_at IS NULL AND p.deleted_at IS NULL
            AND e.deleted_at IS NULL ORDER BY p.priority, p.lft, p.level ASC";

            $taggedItemsSql = "SELECT DISTINCT t.bill_item_id FROM " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " t
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.schedule_task_item_bill_item_id = t.id
            JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " r ON x.standard_type_reference_id = r.id
            WHERE t.schedule_task_item_id = " . $scheduleTaskItem->id . " AND r.post_contract_id =" . $postContractId . "
            AND r.bill_column_setting_id =" . $billColumnSetting->id . "
            AND r.counter =" . $typeUnitData['1'] . " AND x.sub_package_type_reference_id IS NULL";
        }

        $stmt = $pdo->prepare($sql);

        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare($taggedItemsSql);

        $stmt->execute();

        $taggedItems = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $pageNoPrefix = $element->ProjectStructure->BillLayoutSetting->page_no_prefix;

        foreach ( $items as $key => $item )
        {
            $items[$key]['bill_ref'] = BillItemTable::generateBillRef($pageNoPrefix, $item['bill_ref_element_no'], $item['bill_ref_page_no'], $item['bill_ref_char']);
            $items[$key]['type']     = (string) $item['type'];

            $items[$key]['selected'] = in_array($item['id'], $taggedItems) ? true : false;

            unset( $item );
        }

        unset( $billItemIds, $variationOrderItemUnitReferences );

        array_push($items, array(
            'id'          => Constants::GRID_LAST_ROW,
            'bill_ref'    => "",
            'priority'    => 0,
            'type'        => (string) ProjectStructure::getDefaultItemType($element->ProjectStructure->BillType->type),
            'element_id'  => $element->id,
            'level'       => 0,
            'grand_total' => 0,
            'uom_id'      => null,
            'uom_symbol'  => null
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeTaggedBillItemUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $itemXref = Doctrine_Core::getTable('ScheduleTaskItemBillItem')->find($request->getParameter('id'))
        );

        $rowData = array();

        $con = $itemXref->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->getParameter('attr_name');
            $fieldValue = $request->getParameter('val');

            $itemXref->{'set' . sfInflector::camelize($fieldName)}($fieldValue, $con);

            $itemXref->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $itemXref->refresh();

            $productivityInfo = $itemXref->ProductivityInfo;

            $rowData['productivity']      = number_format($productivityInfo->productivity, 2, '.', '');
            $rowData['productivity_type'] = $productivityInfo->productivity_type;
            $rowData['number_of_gang']    = (string) $productivityInfo->number_of_gang;
            $rowData['duration_hours']    = number_format($productivityInfo->duration_hours, 2, '.', '');
            $rowData['duration_days']     = number_format($productivityInfo->duration_days, 2, '.', '');

        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => $rowData
        ));
    }

    public function executeHoursPerDayUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $taskItem = Doctrine_Core::getTable('ScheduleTaskItem')->find($request->getParameter('id')) and
            $request->hasParameter('val')
        );

        $con = $taskItem->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $taskItem->updateHoursPerDay($request->getParameter('val'), $con);

            $con->commit();

            $taskItem->refresh();

            $taskItem->recalculateProductivities();

            $success = true;

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
            'errorMsg' => $errorMsg,
            'val'      => number_format($taskItem->hours_per_day, 2, '.', '')
        ));
    }

    public function executeGetScheduleTaskItemBillItemUnitList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $scheduleTaskItemBillItem = Doctrine_Core::getTable('ScheduleTaskItemBillItem')->find($request->getParameter('id'))
        );

        $billColumnSettingItems = array();
        $records                = array();
        $form                   = new BaseForm();

        $subPackageId = $scheduleTaskItemBillItem->ScheduleTaskItem->ProjectSchedule->sub_package_id;
        $bill         = $scheduleTaskItemBillItem->BillItem->Element->ProjectStructure;

        $pdo = $scheduleTaskItemBillItem->getTable()->getConnection()->getDbh();

        if ( $subPackageId )
        {
            $stmt = $pdo->prepare("SELECT DISTINCT r.bill_column_setting_id, r.counter FROM ".ScheduleTaskItemBillItemTable::getInstance()->getTableName()." t
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.schedule_task_item_bill_item_id = t.id
            JOIN " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " r ON x.sub_package_type_reference_id = r.id
            JOIN ".BillColumnSettingTable::getInstance()->getTableName()." cs ON r.bill_column_setting_id = cs.id
            WHERE x.schedule_task_item_bill_item_id <> " . $scheduleTaskItemBillItem->id . " AND t.bill_item_id = " . $scheduleTaskItemBillItem->bill_item_id . "
            AND r.sub_package_id = " . $subPackageId . "
            AND cs.project_structure_id =" . $bill->id . " AND cs.deleted_at IS NULL AND x.standard_type_reference_id IS NULL");

            $stmt->execute();

            $excludeBillColumnSettingUnits = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT t.sub_package_type_reference_id
                FROM " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " t
                WHERE t.schedule_task_item_bill_item_id = " . $scheduleTaskItemBillItem->id . " AND t.standard_type_reference_id IS NULL
                ORDER BY t.id ASC");

            $stmt->execute();

            $taggedTypeReferences = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            $stmt = $pdo->prepare("SELECT stype.bill_column_setting_id AS id, type_ref.id as type_ref_id, stype.id AS type_ref_id, type_ref.new_name, stype.sub_package_id, stype.bill_column_setting_id, stype.counter
            FROM " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " stype
            LEFT JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " type_ref ON type_ref.bill_column_setting_id = stype.bill_column_setting_id AND type_ref.counter = stype.counter
            LEFT JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = stype.bill_column_setting_id
            WHERE cs.project_structure_id = " . $bill->id . " AND stype.sub_package_id = " . $subPackageId . " ORDER BY stype.bill_column_setting_id, stype.counter ASC");

            $stmt->execute();

            $columnSettingTypeRefs = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT DISTINCT cs.id, cs.name
            FROM " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " t
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON t.bill_item_id = i.id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id = e.id
            JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON e.project_structure_id = cs.project_structure_id AND t.bill_column_setting_id = cs.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON bill.id = cs.project_structure_id AND bill.deleted_at IS NULL
            JOIN " . SubPackageTable::getInstance()->getTableName() . " sp ON bill.root_id = sp.project_structure_id
            JOIN " . PostContractTable::getInstance()->getTableName() . " pc ON pc.project_structure_id = sp.project_structure_id
            LEFT JOIN " . PostContractBillItemTypeTable::getInstance()->getTableName() . " type ON type.post_contract_id = pc.id AND type.bill_column_setting_id = cs.id
            WHERE t.id = " . $scheduleTaskItemBillItem->id . " AND cs.id = " . $scheduleTaskItemBillItem->bill_column_setting_id . " AND bill.id = " . $bill->id . "
            AND e.deleted_at IS NULL AND cs.deleted_at IS NULL AND sp.deleted_at IS NULL
            AND sp.id = " . $subPackageId . " GROUP BY cs.id ORDER BY cs.id ASC");

            $stmt->execute();

            $billColumnSettings = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

            foreach ( $columnSettingTypeRefs as $columnId => $typeRefs )
            {
                $column = ( array_key_exists($columnId, $billColumnSettings) ) ? $billColumnSettings[$columnId][0] : false;

                if ( $column )
                {
                    array_push($records, array(
                        'id'          => 'type' . '-' . $columnId,
                        'description' => $column['name'],
                        'level'       => 0,
                        'type'        => BillItem::TYPE_WORK_ITEM
                    ));

                    if ( count($typeRefs) )
                    {
                        foreach ( $typeRefs as $typeRef )
                        {
                            foreach($excludeBillColumnSettingUnits as $excludeBillColumnSettingUnit)
                            {
                                if($excludeBillColumnSettingUnit['bill_column_setting_id'] == $columnId && $excludeBillColumnSettingUnit['counter'] == $typeRef['counter'])
                                {
                                    continue 2;
                                }
                            }

                            array_push($records, array(
                                'id'            => $columnId . '-' . $typeRef['counter'],
                                'description'   => ( strlen($typeRef['new_name']) ) ? $typeRef['new_name'] : 'Unit ' . $typeRef['counter'],
                                'level'         => 1,
                                'type'          => BillItem::TYPE_WORK_ITEM,
                                'relation_name' => $column['name'],
                                'relation_id'   => $columnId,
                                'selected'      => in_array($typeRef['type_ref_id'], $taggedTypeReferences) ? true : false,
                                '_csrf_token'   => $form->getCSRFToken()
                            ));
                        }
                    }

                }
            }
        }
        else
        {
            $stmt = $pdo->prepare("SELECT DISTINCT r.bill_column_setting_id, r.counter FROM ".ScheduleTaskItemBillItemTable::getInstance()->getTableName()." t
            JOIN " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " x ON x.schedule_task_item_bill_item_id = t.id
            JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " r ON x.standard_type_reference_id = r.id
            JOIN ".BillColumnSettingTable::getInstance()->getTableName()." cs ON r.bill_column_setting_id = cs.id
            WHERE x.schedule_task_item_bill_item_id <> " . $scheduleTaskItemBillItem->id . " AND t.bill_item_id = " . $scheduleTaskItemBillItem->bill_item_id . "
            AND r.post_contract_id =" . $bill->getRoot()->PostContract->id . "
            AND cs.project_structure_id =" . $bill->id . " AND cs.deleted_at IS NULL AND x.sub_package_type_reference_id IS NULL");

            $stmt->execute();

            $excludeBillColumnSettingUnits = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT t.standard_type_reference_id
                FROM " . ScheduleTaggedBillItemTypeReferenceTable::getInstance()->getTableName() . " t
                WHERE t.schedule_task_item_bill_item_id = " . $scheduleTaskItemBillItem->id . " AND t.sub_package_type_reference_id IS NULL
                ORDER BY t.id ASC");

            $stmt->execute();

            $taggedTypeReferences = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            $typeItems = DoctrineQuery::create()->select('t.id, t.post_contract_id, t.bill_column_setting_id, t.counter, t.new_name')
                ->from('PostContractStandardClaimTypeReference t')
                ->leftJoin('t.BillColumnSetting cs')
                ->where('t.post_contract_id = ? AND cs.project_structure_id = ?', array( $bill->getRoot()->PostContract->id, $bill->id ))
                ->fetchArray();

            foreach ( $typeItems as $typeItem )
            {
                $billColumnSettingItems[$typeItem['bill_column_setting_id']][$typeItem['counter']] = array(
                    'id'       => $typeItem['id'],
                    'new_name' => $typeItem['new_name']
                );
            }

            $stmt = $pdo->prepare("SELECT DISTINCT cs.id, cs.name, cs.quantity
                FROM " . ScheduleTaskItemBillItemTable::getInstance()->getTableName() . " t
                JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON t.bill_column_setting_id = cs.id
                WHERE t.id = " . $scheduleTaskItemBillItem->id . " AND cs.id = " . $scheduleTaskItemBillItem->bill_column_setting_id . "
                AND cs.deleted_at IS NULL ORDER BY cs.id ASC");

            $stmt->execute();

            $billColumnSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ( $billColumnSettings as $column )
            {
                $count = $column['quantity'];

                array_push($records, array(
                    'id'          => 'type' . '-' . $column['id'],
                    'description' => $column['name'],
                    'level'       => 0,
                    'type'        => BillItem::TYPE_WORK_ITEM
                ));

                for ( $i = 1; $i <= $count; $i ++ )
                {
                    foreach($excludeBillColumnSettingUnits as $excludeBillColumnSettingUnit)
                    {
                        if($excludeBillColumnSettingUnit['bill_column_setting_id'] == $column['id'] && $excludeBillColumnSettingUnit['counter'] == $i)
                        {
                            continue 2;
                        }
                    }

                    $record['id']            = $column['id'] . '-' . $i;
                    $record['description']   = 'Unit ' . $i;
                    $record['relation_id']   = $column['id'];
                    $record['relation_name'] = $column['name'];
                    $record['level']         = 1;
                    $record['type']          = BillItem::TYPE_WORK_ITEM;
                    $record['_csrf_token']   = $form->getCSRFToken();

                    if ( array_key_exists($column['id'], $billColumnSettingItems) and array_key_exists($i, $billColumnSettingItems[$column['id']]) )
                    {
                        if ( $billColumnSettingItems[$column['id']][$i]['new_name'] != null and strlen($billColumnSettingItems[$column['id']][$i]['new_name']) > 0 )
                        {
                            $record['description'] = $billColumnSettingItems[$column['id']][$i]['new_name'];
                        }

                        $record['selected'] = in_array($billColumnSettingItems[$column['id']][$i]['id'], $taggedTypeReferences) ? true : false;
                    }

                    array_push($records, $record);

                    unset( $record );
                }
            }

            unset( $billColumnSettings );
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'level'       => 0,
            'type'        => BillItem::TYPE_WORK_ITEM
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetTaggedUnitList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $scheduleTaskItemBillItem = Doctrine_Core::getTable('ScheduleTaskItemBillItem')->find($request->getParameter('id')) and
            $request->hasParameter('t')
        );

        $records = array();
        $form    = new BaseForm();

        $column = $request->getParameter('t') == 'p' ? 'up_to_date_percentage' : 'up_to_date_amount';

        array_push($records, array(
            'id'          => - 2,
            'description' => $scheduleTaskItemBillItem->BillColumnSetting->name,
            'value'       => 0,
            'level'       => 0
        ));

        foreach ( ScheduleTaskItemBillItemTable::getTaggedUnits($scheduleTaskItemBillItem, $column) as $typeReference )
        {
            $record['id']          = $typeReference['id'];
            $record['description'] = ( strlen($typeReference['new_name']) ) ? $typeReference['new_name'] : 'Unit ' . $typeReference['counter'];
            $record['level']       = 1;
            $record['value']       = $typeReference['value'];
            $record['_csrf_token'] = $form->getCSRFToken();

            array_push($records, $record);

            unset( $record );
        }

        array_push($records, array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            'value'       => 0,
            'level'       => 0
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeTotalUnitsUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $taskItemBilItem = Doctrine_Core::getTable('ScheduleTaskItemBillItem')->find($request->getParameter('id')) and
            $request->hasParameter('sid') and $request->hasParameter('usid')
        );

        try
        {
            $taskItemBilItem->updateTotalUnits(
                Utilities::array_filter_integer(explode(",", $request->getParameter("sid"))),
                Utilities::array_filter_integer(explode(",", $request->getParameter("usid"))));

            $taskItemBilItem->recalculateProductivity();

            $success = true;

            $errorMsg = null;
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

    public function executeUpdateClaimByUnit(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $scheduleTaggedBillItemTypeReference = Doctrine_Core::getTable('ScheduleTaggedBillItemTypeReference')->find($request->getParameter('id')) and
            $request->hasParameter('t')
        );

        try
        {
            $fieldValue = ( is_numeric($request->getParameter('val')) ) ? $request->getParameter('val') : 0;

            $fieldName = $request->getParameter('t') == 'p' ? 'up_to_date_percentage' : 'up_to_date_amount';

            $subPackageId = $scheduleTaggedBillItemTypeReference->ScheduleTaskItemBillItem->ScheduleTaskItem->ProjectSchedule->sub_package_id;

            $claimItem = ScheduleTaggedBillItemTypeReferenceTable::getClaimItem($scheduleTaggedBillItemTypeReference);

            $claimItem->save();

            if( $subPackageId > 0 )
            {
                $revision = SubPackagePostContractClaimRevisionTable::getCurrentProjectRevision($scheduleTaggedBillItemTypeReference->ScheduleTaskItemBillItem->ScheduleTaskItem->ProjectSchedule->SubPackage, false);

                $claimItem->calculateClaimColumn($fieldName, $fieldValue, $revision, $subPackageId);
            }
            else
            {
                $revision = PostContractClaimRevisionTable::getCurrentProjectRevision($scheduleTaggedBillItemTypeReference->ScheduleTaskItemBillItem->ScheduleTaskItem->ProjectSchedule->ProjectStructure->PostContract, false);

                $claimItem->calculateClaimColumn($fieldName, $fieldValue, $revision);
            }

            $success = true;

            $errorMsg = null;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeUpdateClaim(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $scheduleTaskItemBillItem = Doctrine_Core::getTable('ScheduleTaskItemBillItem')->find($request->getParameter('id')) and
            $request->hasParameter('t')
        );

        $fieldValue = ( is_numeric($request->getParameter('val')) ) ? $request->getParameter('val') : 0;

        $fieldName = $request->getParameter('t') == 'p' ? 'up_to_date_percentage' : 'up_to_date_amount';

        $taggedUnits = ScheduleTaskItemBillItemTable::getTaggedUnits($scheduleTaskItemBillItem, $fieldName);

        if( $fieldName == 'up_to_date_amount' )
        {
            $fieldValue = $fieldValue / count($taggedUnits);
        }

        try
        {
            foreach($taggedUnits as $unit)
            {
                $scheduleTaggedBillItemTypeReference = Doctrine_Core::getTable('ScheduleTaggedBillItemTypeReference')->find($unit['id']);
                $subPackageId                        = $scheduleTaggedBillItemTypeReference->ScheduleTaskItemBillItem->ScheduleTaskItem->ProjectSchedule->sub_package_id;

                $claimItem = ScheduleTaggedBillItemTypeReferenceTable::getClaimItem($scheduleTaggedBillItemTypeReference);

                $claimItem->save();

                if( $subPackageId > 0 )
                {
                    $revision = SubPackagePostContractClaimRevisionTable::getCurrentProjectRevision($scheduleTaggedBillItemTypeReference->ScheduleTaskItemBillItem->ScheduleTaskItem->ProjectSchedule->SubPackage, false);

                    $claimItem->calculateClaimColumn($fieldName, $fieldValue, $revision, $subPackageId);
                }
                else
                {
                    $revision = PostContractClaimRevisionTable::getCurrentProjectRevision($scheduleTaggedBillItemTypeReference->ScheduleTaskItemBillItem->ScheduleTaskItem->ProjectSchedule->ProjectStructure->PostContract, false);

                    $claimItem->calculateClaimColumn($fieldName, $fieldValue, $revision);
                }
            }

            $success = true;

            $errorMsg = null;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    public function executeTagBillItems(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $scheduleTaskItem = Doctrine_Core::getTable('ScheduleTaskItem')->find($request->getParameter('id')) and
            $request->hasParameter("sid") and
            $request->hasParameter("usid") and
            $request->hasParameter("uid") and strlen($request->getParameter("uid")) > 0
        );

        try
        {
            $typeUnitData = explode("-", $request->getParameter("uid"));

            $this->forward404Unless($billColumnSetting = Doctrine_Core::getTable('BillColumnSetting')->find($typeUnitData[0]));

            $scheduleTaskItem->tagBillItems(
                Utilities::array_filter_integer(explode(",", $request->getParameter("sid"))),
                Utilities::array_filter_integer(explode(",", $request->getParameter("usid"))),
                $billColumnSetting, $typeUnitData[1]);

            $scheduleTaskItem->recalculateProductivities();

            $errorMsg = null;
            $success  = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeUpdateProjectManagementGroupInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $projectStructure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $form = new ProjectManagementGroupsAssignmentForm($projectStructure);

        if ( $this->isFormValid($request, $form) )
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

    //printing
    public function executeGantt(sfWebRequest $request)
    {
        $this->forward404Unless(
            !$request->isXmlHttpRequest() and
            $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id'))
        );

        $this->type = $request->getParameter('type');

        $this->projectSchedule = $projectSchedule;
    }

    public function executeChart(sfWebRequest $request)
    {
        $this->forward404Unless(
            $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id'))
        );

        $this->projectSchedule = $projectSchedule;

        $data = array();
        $chartLabels = array();

        $noClaim = false;
        foreach($projectSchedule->getCostVersusTimeData() as $key => $cost)
        {
            $noClaim = $cost == 0 ? true : false;
            $data[] = $cost;
            $chartLabels[] = date('M Y', strtotime($key.'-01'));
        }

        $this->chartLabels = $chartLabels;
        $this->noClaim = $noClaim;
        $this->chartData = json_encode($data);
        $this->chartTitle = "Cost vs Time";

        $this->getResponse()->setSlot("chartTitle", $this->chartTitle);
    }

    public function executeChartAccumulativeCost(sfWebRequest $request)
    {
        $this->forward404Unless(
            $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id'))
        );

        $this->projectSchedule = $projectSchedule;

        $data = array();
        $chartLabels = array();

        $noClaim = false;
        $accumulativeCost = 0;

        foreach($projectSchedule->getCostVersusTimeData() as $key => $cost)
        {
            $noClaim = $cost == 0 ? true : false;
            $accumulativeCost += $cost;
            $data[] = $accumulativeCost;
            $chartLabels[] = date('M Y', strtotime($key.'-01'));
        }

        $this->chartLabels = $chartLabels;
        $this->noClaim = $noClaim;
        $this->chartData = json_encode($data);
        $this->chartTitle = "Accumulative Cost";

        $this->getResponse()->setSlot("chartTitle", $this->chartTitle);

        $this->setTemplate('chart');
    }

    public function executeProjectScheduleExport(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            strlen($request->getParameter('filename')) > 0 and
            $projectSchedule = Doctrine_Core::getTable('ProjectSchedule')->find($request->getParameter('id'))
        );

        $errorMsg   = null;
        $filePrefix = null;

        try
        {
            $sfXMLWriter = new sfBuildspaceProjectScheduleXMLWriter('project_schedule-' . $projectSchedule->id, $projectSchedule, false);

            $sfXMLWriter->process();

            /* Generate Zip File */
            $sfZipGenerator = new sfZipGenerator("ProjectSchedule_" . $projectSchedule->id, null, sfBuildspaceProjectScheduleXMLWriter::FILE_EXT, true, true);

            $sfZipGenerator->createZip(array( $sfXMLWriter->getFileInformation() ));

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

            ob_end_flush();

            return $this->renderText($fileContents);
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => false, 'errorMsg' => $errorMsg ));
    }

    public function executeProjectScheduleUpload(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $tempUploadPath = sfConfig::get('sf_sys_temp_dir');

        $projectInformation  = null;
        $projectBreakdown    = null;
        $errorMsg            = null;
        $pathToFile          = null;
        $fileToUnzip         = array();
        $projectScheduleInfo = array(
            'title'             => "",
            'description'       => "",
            'exclude_saturdays' => "",
            'exclude_sundays'   => "",
            'timezone'          => "",
            'start_date'        => ""
        );

        try
        {
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
                    throw new Exception('The file is unreadable');
                }
            }

            if ( count($fileToUnzip) )
            {
                if (strtolower($fileToUnzip['ext']) != sfBuildspaceProjectScheduleXMLWriter::FILE_EXT )
                {
                    throw new Exception('Invalid file type');
                }

                $sfZipGenerator = new sfZipGenerator($fileToUnzip['name'], $fileToUnzip['path'], $fileToUnzip['ext'], true, true);

                $extractedFiles = $sfZipGenerator->unzip();

                $extractDir = $sfZipGenerator->extractDir;

                if ( count($extractedFiles) )
                {
                    foreach ( $extractedFiles as $file )
                    {
                        $xmlParser = new sfBuildspaceXMLParser($file['filename'], $extractDir);

                        $xmlParser->read();

                        if ( $xmlParser->xml->attributes()->exportType == ExportedFile::EXPORT_TYPE_PROJECT_SCHEDULE )
                        {
                            $xmlData = $xmlParser->getProcessedData();

                            $projectScheduleInfo['title'] = $xmlData->attributes()->title;
                            $projectScheduleInfo['description'] = $xmlData->attributes()->description;
                            $projectScheduleInfo['exclude_saturdays'] = $xmlData->attributes()->exclude_saturdays;
                            $projectScheduleInfo['exclude_sundays'] = $xmlData->attributes()->exclude_sundays;
                            $projectScheduleInfo['timezone'] = $xmlData->attributes()->timezone;
                            $projectScheduleInfo['start_date'] = $xmlData->attributes()->start_date;
                        }
                    }
                }
            }

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'projectScheduleInfo' => $projectScheduleInfo,
            'filename'            => $file['filename'],
            'filePath'            => (!empty($fileToUnzip)) ? $tempUploadPath.$fileToUnzip['name'].DIRECTORY_SEPARATOR : null,
            'errorMsg'            => $errorMsg,
            'success'             => $success
        ));
    }

    public function executeProjectScheduleImport(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
            $request->hasParameter('filename') and
            $request->hasParameter('path') and
            $request->hasParameter('psd')
        );

        $subPackage = null;

        if($request->hasParameter('spid') && !empty($request->getParameter('spid')))
        {
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('spid'));
        }

        $filename = $request->getParameter('filename');
        $filePath = $request->getParameter('path');

        $errorMsg = null;

        $con = Doctrine_Manager::getInstance()->getCurrentConnection();

        try
        {
            $con->beginTransaction();

            $userId = $this->getUser()->getGuardUser()->id;

            $useProjectStartDate = ($request->getParameter('psd')=='true') ? true : false;

            $importBackup = new sfBuildspaceProjectScheduleXMLReader($userId, $project, $subPackage, $filename, $request->hasParameter('exclude_saturdays'), $request->hasParameter('exclude_sundays'), $useProjectStartDate, $filePath, true, $con);

            $importBackup->process();

            $con->commit();

            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'errorMsg' => $errorMsg,
            'success'  => $success
        ));
    }
}