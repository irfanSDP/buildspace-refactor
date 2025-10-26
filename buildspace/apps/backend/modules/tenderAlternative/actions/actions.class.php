<?php

/**
 * tenderAlternative actions.
 *
 * @package    buildspace
 * @subpackage tenderAlternative
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class tenderAlternativeActions extends BaseActions
{
    public function executeGetTenderAlternatives(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $pdo = TenderAlternativeTable::getInstance()->getConnection()->getDbh();

        $excludeRevisionDeletedAtSql = "";
        if(($request->hasParameter('exclude') && (int)$request->getParameter('exclude')) or $project->MainInformation->tender_type_id == ProjectMainInformation::TENDER_TYPE_PARTICIPATED)
        {
            $excludeRevisionDeletedAtSql = " AND ta.project_revision_deleted_at IS NULL";
        }

        $stmt = $pdo->prepare("SELECT DISTINCT ta.id, ta.title, ta.description, ta.project_structure_id, ta.is_awarded, ta.project_revision_id, ta.created_at, ta.deleted_at_project_revision_id, ta.project_revision_deleted_at
            FROM " . TenderAlternativeTable::getInstance()->getTableName() . " ta
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON ta.project_structure_id = p.id
            WHERE p.id = " . $project->id . "
            AND p.deleted_at IS NULL AND ta.deleted_at IS NULL ".$excludeRevisionDeletedAtSql."
            ORDER BY ta.created_at ASC");
        
        $stmt->execute();
        $tenderAlternatives = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        $items[] = [
            'id'                         => -9999,
            'count'                      => null,
            'title'                      => $project->MainInformation->title,
            'original_total'             => 0,
            'overall_total_after_markup' => 0,
            'level'                      => 0,
            '_csrf_token'                => $form->getCSRFToken()
        ];

        $overallTotalAfterMarkupRecords = TenderAlternativeTable::getOverallTotalForTenderAlternatives($project);
        $totalBillOriginalAmount = TenderAlternativeTable::getBillsOriginalAmountByTenderAlternatives($project);

        $count = 1;
        foreach($tenderAlternatives as $idx => $tenderAlternative)
        {
            $tenderAlternatives[$idx]['original_total']             = (array_key_exists($tenderAlternative['id'], $totalBillOriginalAmount)) ? $totalBillOriginalAmount[$tenderAlternative['id']] : 0;
            $tenderAlternatives[$idx]['bill_sum_total']             = (array_key_exists($tenderAlternative['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$tenderAlternative['id']] : 0;
            $tenderAlternatives[$idx]['overall_total_after_markup'] = (array_key_exists($tenderAlternative['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$tenderAlternative['id']] : 0;
            $tenderAlternatives[$idx]['level']                      = 1;
            $tenderAlternatives[$idx]['count']                      = $count++;
            $tenderAlternatives[$idx]['_csrf_token']                = $form->getCSRFToken();

            $items[] = $tenderAlternatives[$idx];
        }

        $items[] = [
            'id'                         => Constants::GRID_LAST_ROW,
            'count'                      => null,
            'title'                      => "",
            'original_total'             => 0,
            'overall_total_after_markup' => 0,
            'level'                      => 0,
            '_csrf_token'                => $form->getCSRFToken()
        ];

        return $this->renderJson([
            'identifier' => 'id',
            'items'      => $items
        ]);
    }

    public function executeGetRationalizeRateTenderAlternatives(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $pdo = TenderAlternativeTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT DISTINCT ta.id, ta.title, ta.description, ta.project_structure_id, ta.is_awarded, ta.project_revision_id, ta.created_at, ta.deleted_at_project_revision_id, ta.project_revision_deleted_at
            FROM " . TenderAlternativeTable::getInstance()->getTableName() . " ta
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON ta.project_structure_id = p.id
            WHERE p.id = " . $project->id . "
            AND p.deleted_at IS NULL AND ta.deleted_at IS NULL AND ta.project_revision_deleted_at IS NULL
            ORDER BY ta.created_at ASC");
        
        $stmt->execute();
        $tenderAlternatives = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        $items[] = [
            'id'                         => -9999,
            'count'                      => null,
            'title'                      => $project->MainInformation->title,
            'original_total'             => 0,
            'overall_total_after_markup' => 0,
            'level'                      => 0,
            '_csrf_token'                => $form->getCSRFToken()
        ];

        $printRationalizeRate = ($request->hasParameter('printRationalizeRate') && $request->getParameter('printRationalizeRate'));

        if($printRationalizeRate)
        {
            $overallTotalAfterMarkupRecords = TenderBillItemRationalizedRatesTable::getTenderAlternativeOverallBillTotalByProject($project);
        }
        else
        {
            $overallTotalAfterMarkupRecords = TenderAlternativeTable::getOverallTotalForTenderAlternatives($project);
        }

        $totalBillOriginalAmount = TenderAlternativeTable::getBillsOriginalAmountByTenderAlternatives($project);

        $count = 1;
        foreach($tenderAlternatives as $idx => $tenderAlternative)
        {
            $tenderAlternatives[$idx]['original_total']             = (array_key_exists($tenderAlternative['id'], $totalBillOriginalAmount)) ? $totalBillOriginalAmount[$tenderAlternative['id']] : 0;
            $tenderAlternatives[$idx]['bill_sum_total']             = (array_key_exists($tenderAlternative['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$tenderAlternative['id']] : 0;
            $tenderAlternatives[$idx]['overall_total_after_markup'] = (array_key_exists($tenderAlternative['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$tenderAlternative['id']] : 0;
            $tenderAlternatives[$idx]['level']                      = 1;
            $tenderAlternatives[$idx]['count']                      = $count++;
            $tenderAlternatives[$idx]['_csrf_token']                = $form->getCSRFToken();

            $items[] = $tenderAlternatives[$idx];
        }

        $items[] = [
            'id'                         => Constants::GRID_LAST_ROW,
            'count'                      => null,
            'title'                      => "",
            'original_total'             => 0,
            'overall_total_after_markup' => 0,
            'level'                      => 0,
            '_csrf_token'                => $form->getCSRFToken()
        ];

        return $this->renderJson([
            'identifier' => 'id',
            'items'      => $items
        ]);
    }

    public function executeGetTenderAlternativeProject(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );

        $user = $this->getUser()->getGuardUser();

        switch($project->MainInformation->status)
        {
            case ProjectMainInformation::STATUS_POSTCONTRACT:
                $projectUserPermissionStatus = ProjectUserPermission::STATUS_POST_CONTRACT;
                break;
            case ProjectMainInformation::STATUS_TENDERING:
                $projectUserPermissionStatus = ProjectUserPermission::STATUS_TENDERING;
                break;
            default:
                $projectUserPermissionStatus = ProjectUserPermission::STATUS_PROJECT_BUILDER;
                break;
        }

        $projectCreator = ($project->created_by) ? sfGuardUserTable::getInstance()->find($project->created_by) : null;

        $projectsWithOpenAddendum       = ProjectStructureTable::getProjectsWithOpenAddendum();
        $visibleTendererRatesProjectIds = EProjectProjectTable::getVisibleTendererRatesProjectIds();

        $form = new BaseForm();

        $project->refresh();

        $data = [
            'id'                           => $project->id,
            'title'                        => $project->title,
            'priority'                     => $project->priority,
            'is_admin'                     => $user->isAdminForProject($project, $projectUserPermissionStatus),
            'reference'                    => ( $project->MainInformation->eproject_origin_id ) ? $project->MainInformation->getEProjectProject()->reference : "",
            'status'                       => ProjectMainInformation::getProjectStatusById($project->MainInformation->status),
            'status_id'                    => $project->MainInformation->status,
            'tender_type_id'               => $project->MainInformation->tender_type_id,
            'post_contract_type_id'        => ($project->PostContract) ? $project->PostContract->published_type : null,
            'state'                        => ( $project->MainInformation->Subregions ) ? $project->MainInformation->Subregions->name : "N/A",
            'country'                      => ( $project->MainInformation->Regions )? $project->MainInformation->Regions ->country : "N/A",
            'created_by'                   => ( $projectCreator && $projectCreator->Profile) ? $projectCreator->Profile->name : '-',
            'start_date'                   => ( $project->MainInformation->start_date ) ? date('Y-m-d', strtotime($project->MainInformation->start_date)) : '-',
            'created_at'                   => date('d/m/Y H:i', strtotime($project->created_at)),
            'has_addendum'                 => (int)(!empty($project->getBillsWithAddendums())),
            'has_tender_alternative'       => (int)(count($project->TenderAlternatives)),
            'can_be_deleted'               => ProjectStructureTable::canBeDeletedById($project->id),
            'tendering_module_locked'      => ProjectStructureTable::tenderingModuleLocked($project->id),
            'can_publish_to_post_contract' => (intval($project->MainInformation->status) == ProjectMainInformation::STATUS_TENDERING && !array_key_exists($project->id, $projectsWithOpenAddendum)), 
            'show_contractor_rates'        => (int)in_array($project->MainInformation->eproject_origin_id, $visibleTendererRatesProjectIds),
            '_csrf_token'                  => $form->getCSRFToken(),
        ];
    
        return $this->renderJson($data);
    }

    public function executeGetTenderAlternativeInfoByBill(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id'))
        );

        $tenderAlternatives = $bill->getTenderAlternativesInfo();

        return $this->renderJson($tenderAlternatives);
    }

    public function executeTenderAlternativeForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if( $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('id')) )
        {
            $project = $tenderAlternative->ProjectStructure;
        }
        else
        {
            $tenderAlternative = new TenderAlternative();
            $project           = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'));
        }

        $form = new TenderAlternativeForm($tenderAlternative);

        return $this->renderJson(array(
            'tender_alternative[title]'                => $form->getObject()->title,
            'tender_alternative[description]'          => $form->getObject()->description,
            'tender_alternative[project_structure_id]' => $project->id,
            'tender_alternative[_csrf_token]'          => $form->getCSRFToken(),
        ));
    }

    public function executeTenderAlternativeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));
        
        if( $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('id')) )
        {
            $project = $tenderAlternative->ProjectStructure;
            $rebuildGrid = false;
        }
        else
        {
            $params = $request->getParameter("tender_alternative");

            $this->forward404Unless(
                array_key_exists('project_structure_id', $params) and
                $project = Doctrine_Core::getTable('ProjectStructure')->find($params['project_structure_id']) and
                $project->type == ProjectStructure::TYPE_ROOT
            );

            $tenderAlternative = new TenderAlternative();

            $count = count($project->TenderAlternatives);
            $rebuildGrid = ($count == 0);
        }

        $form = new TenderAlternativeForm($tenderAlternative);

        if( $this->isFormValid($request, $form) )
        {
            try
            {
                $tenderAlternative = $form->save();

                $item = [
                    'id'                     => $tenderAlternative->id,
                    'title'                  => $tenderAlternative->title,
                    'description'            => $tenderAlternative->description,
                    'project_structure_id'   => $project->id,
                    'rebuild_breakdown_grid' => $rebuildGrid,
                    '_csrf_token'            => $form->getCSRFToken()
                ];

                $errors   = null;
                $success  = true;
            }
            catch(Exception $e)
            {
                $errors   = $e->getMessage();
                $item     = [];
                $success  = false;
            }
        }
        else
        {
            $errors   = $form->getErrors();
            $item     = [];
            $success  = false;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'errors'    => $errors,
            'item'      => $item
        ));
    }

    public function executeTenderAlternativeDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('id'))
        );
        
        $project = $tenderAlternative->ProjectStructure;

        try
        {
            $tenderAlternative->delete();

            $errors   = null;
            $success  = true;
        }
        catch(Exception $e)
        {
            $errors   = $e->getMessage();
            $success  = false;
        }

        $count = DoctrineQuery::create()
        ->select('ta.project_structure_id')
        ->from('TenderAlternative ta')
        ->where('ta.project_structure_id = ?', $project->id)
        ->execute()
        ->count();

        return $this->renderJson(array(
            'success'         => $success,
            'has_alternative' => ($count > 0),
            'errors'          => $errors
        ));
    }

    public function executeGetTenderAlternativeUnlinkBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $tenderAlternative = TenderAlternativeTable::getInstance()->find($request->getParameter('id'))
        );

        $project = $tenderAlternative->ProjectStructure;

        $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($project);

        $pdo = TenderAlternativeTable::getInstance()->getConnection()->getDbh();
        
        $sqlLinkedBills = "";
        $linkedBillIds = [];
        
        foreach($tenderAlternative->Bills as $bill)
        {
            $linkedBillIds[] = $bill->project_structure_id;
        }

        if(!empty($linkedBillIds))
        {
            $sqlLinkedBills = " AND c.id NOT IN (" . implode(',', $linkedBillIds) . ") ";
        }

        $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.title, p.type, t.type AS bill_type, t.status AS bill_status, p.level, p.lft
        FROM " . ProjectStructureTable::getInstance()->getTableName() . " c
        JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p
        ON (c.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
        LEFT JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON (t.project_structure_id = p.id AND t.deleted_at IS NULL)
        WHERE c.lft >= ".$project->lft." AND c.rgt <= ".$project->rgt." ".$sqlLinkedBills."
        AND p.root_id = ".$project->id." AND p.type <= ".ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL."
        AND c.root_id = p.root_id AND c.type <> " . ProjectStructure::TYPE_ROOT . "
        AND p.deleted_at IS NULL AND c.deleted_at IS NULL
        ORDER BY p.lft ASC");

        $stmt->execute([]);
        $projectStructures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;
        foreach($projectStructures as $projectStructure)
        {
            $count = ( $projectStructure['type'] == ProjectStructure::TYPE_BILL or $projectStructure['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL or $projectStructure['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL ) ? $count + 1 : $count;

            $items[] = [
                'id'                         => $projectStructure['id'],
                'count'                      => ( $projectStructure['type'] == ProjectStructure::TYPE_BILL or $projectStructure['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL or $projectStructure['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL ) ? $count : null,
                'title'                      => $projectStructure['title'],
                'type'                       => $projectStructure['type'],
                'bill_type'                  => ( $projectStructure['bill_type'] == ProjectStructure::TYPE_BILL) ? $projectStructure['bill_type'] : -1,
                'bill_status'                => ( $projectStructure['bill_type'] == ProjectStructure::TYPE_BILL) ? $projectStructure['bill_status'] : -1,
                'overall_total_after_markup' => ($projectStructure['type'] == ProjectStructure::TYPE_BILL && array_key_exists($projectStructure['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$projectStructure['id']] : 0,
                'level'                      => $projectStructure['level']
            ];
        }

        unset($projectStructures);

        $items[] = [
            'id' => Constants::GRID_LAST_ROW,
            'count'                      => null,
            'title'                      => "",
            'overall_total_after_markup' => 0,
            'level'                      => 0
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

        $user    = $this->getUser()->getGuardUser();
        $project = $tenderAlternative->ProjectStructure;
        $records = $tenderAlternative->getAssignedBills();

        $count = 0;
        $form  = new BaseForm();

        $overallTotalAfterMarkupRecords = ProjectStructureTable::getOverallTotalAfterMarkupByProject($project);
        $tenderAlternativesSumTotal = TenderAlternativeTable::getOverallTotalForTenderAlternatives($project);

        $projectSumTotal = 0;

        if(array_key_exists($tenderAlternative->id, $tenderAlternativesSumTotal))
        {
            $projectSumTotal = $tenderAlternativesSumTotal[$tenderAlternative->id];
        }

        $items = [];

        foreach($records as $key => $record)
        {
            $count = ( $record['type'] == ProjectStructure::TYPE_BILL or $record['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL or $record['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL ) ? $count + 1 : $count;

            $record['billLayoutSettingId']    = ( isset( $record['bill_layout_setting_id'] ) ) ? $record['bill_layout_setting_id'] : null;
            $record['somBillLayoutSettingId'] = ( isset( $record['som_bill_layout_setting_id'] ) ) ? $record['som_bill_layout_setting_id'] : null;
            $record['sorBillLayoutSettingId'] = ( isset( $record['sor_bill_layout_setting_id'] ) ) ? $record['sor_bill_layout_setting_id'] : null;

            unset($record['bill_layout_setting_id'], $record['som_bill_layout_setting_id'], $record['sor_bill_layout_setting_id']);

            $record['count']                      = ( $record['type'] == ProjectStructure::TYPE_BILL or $record['type'] == ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL or $record['type'] == ProjectStructure::TYPE_SCHEDULE_OF_RATE_BILL ) ? $count : null;
            $record['original_total']             = $record['type'] == ProjectStructure::TYPE_BILL ? ProjectStructureTable::getOverallOriginalTotalByBillId($record['id']) : 0;
            $record['overall_total_after_markup'] = ($record['type'] == ProjectStructure::TYPE_BILL && array_key_exists($record['id'], $overallTotalAfterMarkupRecords)) ? $overallTotalAfterMarkupRecords[$record['id']] : 0;
            $record['bill_sum_total']             = $projectSumTotal;
            $record['unlink']                     = $record['id'];
            $record['recalculate']                = $record['id'];
            $record['_csrf_token']                = $form->getCSRFToken();

            $items[] = $record;
        }
        
        $items[] = [
            'id'                         => Constants::GRID_LAST_ROW,
            'count'                      => null,
            'title'                      => "",
            'type'                       => ProjectStructure::TYPE_BILL,
            'bill_type'                  => -1,
            'original_total'             => 0,
            'overall_total_after_markup' => 0,
            'bill_sum_total'             => 0,
            'level'                      => 0
        ];

        return $this->renderJson([
            'identifier' => 'id',
            'items'      => $items
        ]);
    }

    public function executeLinkBills(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('id'))
        );

        $con = $tenderAlternative->getTable()->getConnection();

        try
        {
            $billIds = explode(',', $request->getParameter('bids'));

            $con->beginTransaction();

            $tenderAlternative->assignBills($billIds, $con);

            $con->commit();

            $success = true;
            $errorMsg = null;
        }
        catch(Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson([
            'success' => $success,
            'errorMsg' => $errorMsg
        ]);
    }

    public function executeUnlinkBill(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $tenderAlternative = Doctrine_Core::getTable('TenderAlternative')->find($request->getParameter('id')) and
            $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('bid'))
        );

        $con = $tenderAlternative->getTable()->getConnection();

        try
        {
            $con = $tenderAlternative->getTable()->getConnection();

            $con->beginTransaction();

            $tenderAlternativeBillXref = DoctrineQuery::create()
            ->from('TenderAlternativeBill x')
            ->where('x.tender_alternative_id = ?', $tenderAlternative->id)
            ->andWhere('x.project_structure_id = ?', $bill->id)
            ->limit(1)
            ->fetchOne();

            if($tenderAlternativeBillXref)
            {
                $tenderAlternativeBillXref->delete();
            }

            $con->commit();

            $success = true;
            $errorMsg = null;
        }
        catch(Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson([
            'success' => $success,
            'errorMsg' => $errorMsg
        ]);
    }

    public function executeGetTenderAlternativeUntagProjectBills(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $project->type == ProjectStructure::TYPE_ROOT
        );
        
        $untagBillIds = $project->getUntagTenderAlternativeBillIds();
        
        $countUntagBill = count($untagBillIds);

        $success = !($countUntagBill > 0);

        return $this->renderJson([
            'success'    => $success,
            'bill_count' => $countUntagBill
        ]);
    }

    public function executeGetTenderAlternative(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest()
        );
        
        $tenderAlternative = TenderAlternativeTable::getInstance()->find($request->getParameter('id'));

        if($tenderAlternative)
        {
            $tenderAlternative = $tenderAlternative->toArray();
        }else
        {
            $tenderAlternative = null;
        }

        return $this->renderJson($tenderAlternative);
    }
}
