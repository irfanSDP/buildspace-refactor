<?php namespace PCK\ModulePermission;

use PCK\Helpers\DataTables;
use PCK\Users\User;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultant;
use PCK\ConsultantManagement\ApprovalDocument;

class ModulePermissionRepository
{
    public function getModuleId($moduleIdentifier)
    {
        switch ($moduleIdentifier)
        {
            case 'tender_documents_template':
                return ModulePermission::MODULE_ID_TENDER_DOCUMENTS_TEMPLATE;
            case 'form_of_tender_template':
                return ModulePermission::MODULE_ID_FORM_OF_TENDER_TEMPLATE;
            case 'technical_evaluation_template':
                return ModulePermission::MODULE_ID_TECHNICAL_EVALUATION_TEMPLATE;
            case 'contractor_listing':
                return ModulePermission::MODULE_ID_CONTRACTOR_LISTING;
            case 'defects':
                return ModulePermission::MODULE_ID_DEFECTS;
            case 'weathers':
                return ModulePermission::MODULE_ID_WEATHERS;
            case 'finance':
                return ModulePermission::MODULE_ID_FINANCE;
            case 'projects_overview':
                return ModulePermission::MODULE_ID_PROJECTS_OVERVIEW;
            case 'master_cost_data':
                return ModulePermission::MODULE_ID_MASTER_COST_DATA;
            case 'cost_data':
                return ModulePermission::MODULE_ID_COST_DATA;
            case 'letter_of_award':
                return ModulePermission::MODULE_ID_LETTER_OF_AWARD;
            case 'rfv_category':
                return ModulePermission::MODULE_ID_RFV_CATEGORY;
            case 'inspection_template':
                return ModulePermission::MODULE_ID_INSPECTION_TEMPLATE;
            case 'inspection':
                return ModulePermission::MODULE_ID_INSPECTION;
            case 'top_management_verifiers':
                return ModulePermission::MODULE_ID_TOP_MANAGEMENT_VERIFIERS;
            case 'consultant_payment':
                return ModulePermission::MODULE_ID_CONSULTANT_PAYMENT;
            case 'project_report_template':
                return ModulePermission::MODULE_ID_PROJECT_REPORT_TEMPLATE;
            case 'project_report_dashboard':
                return ModulePermission::MODULE_ID_PROJECT_REPORT_DASHBOARD;
            case 'rejected_material':
                return ModulePermission::MODULE_ID_REJECTED_MATERIAL;
            case 'labour':
                return ModulePermission::MODULE_ID_LABOUR;
            case 'machinery':
                return ModulePermission::MODULE_ID_MACHINERY;
            case 'site_diary_maintenance':
                return ModulePermission::MODULE_ID_SITE_DIARY_MAINTENANCE;
            case 'project_report_chart_template':
                return ModulePermission::MODULE_ID_PROJECT_REPORT_CHART_TEMPLATE;
            case 'project_report_charts':
                return ModulePermission::MODULE_ID_PROJECT_REPORT_CHARTS;
            case 'payment_gateway':
                return ModulePermission::MODULE_ID_PAYMENT_GATEWAY;
            case 'orders':
                return ModulePermission::MODULE_ID_ORDERS;
        }
        return null;
    }

    public function getAssignableUsers($inputs)
    {
        $moduleId = $inputs['moduleId'];
        $currentPage = $inputs['page'];
        $pageSize = $inputs['size'];
        $filters =  isset($inputs['filters']) ? $inputs['filters'] : [];

        $currentlyAssignedUserIds = ModulePermission::where('module_identifier', '=', $moduleId)->get()->lists('user_id');

        $query = \DB::table('users AS u')->select('u.id', 'u.name', 'u.email', 'c.name AS company_name');
        $query->join('companies AS c', 'c.id', '=', 'u.company_id');
        $query->where('c.confirmed', true);
        $query->whereNotIn('u.id', $currentlyAssignedUserIds);
        $query->where('u.confirmed', '=', true);
        $query->where('u.is_super_admin', '=', false);
        $query->where('u.account_blocked_status', '=', false);

        if(!empty($filters))
        {
            foreach($filters as $filter)
            {
                $field = $filter['field'];
                $value = trim($filter['value']);

                switch($field)
                {
                    case 'name':
                        $query->where('u.name', 'ilike', '%' . $value . '%');
                        break;
                    case 'company':
                        $query->where('c.name', 'ilike', '%' . $value . '%');
                        break;
                    case 'email':
                        $query->where('u.email', 'ilike', '%' . $value . '%');
                        break;
                }
            }
        }

        $rowCount = $query->count();

        $query->limit($pageSize);
        $query->offset(($currentPage * $pageSize) - $pageSize);
        $query->orderBy('u.id', 'DESC');
        
        $queryResults = $query->get();

        $count = ($currentPage * $pageSize) - $pageSize;
        $data = [];

        foreach($queryResults as $result)
        {
            array_push($data, [
                'indexNo'               => ++$count,
                'id'                    => $result->id,
                'name'                  => $result->name,
                'email'                 => $result->email,
                'company'               => $result->company_name,
            ]);
        }

        return [
            'last_page' => ceil((float) ($rowCount / $pageSize)),
            'data'      => $data,
        ];
    }

    public function getAssignedUsers($inputs)
    {
        $moduleId = $inputs['moduleId'];
        $currentPage = $inputs['page'];
        $pageSize = $inputs['size'];
        $filters =  isset($inputs['filters']) ? $inputs['filters'] : [];

        $query = \DB::table('module_permissions AS m')->select('u.id', 'm.id AS module_permission_id','m.module_identifier', 'u.name', 'u.email', 'c.name AS company_name', 'm.is_editor');
        $query->join('users AS u', 'u.id', '=', 'm.user_id');
        $query->join('companies AS c', 'c.id', '=', 'u.company_id');
        $query->where('c.confirmed', true);
        $query->where('m.module_identifier', $moduleId);

        if(!empty($filters))
        {
            foreach($filters as $filter)
            {
                $field = $filter['field'];
                $value = trim($filter['value']);

                switch($field)
                {
                    case 'name':
                        $query->where('u.name', 'ilike', '%' . $value . '%');
                        break;
                    case 'company':
                        $query->where('c.name', 'ilike', '%' . $value . '%');
                        break;
                    case 'email':
                        $query->where('u.email', 'ilike', '%' . $value . '%');
                        break;
                }
            }
        }

        $rowCount = $query->count();

        $query->limit($pageSize);
        $query->offset(($currentPage * $pageSize) - $pageSize);
        $query->orderBy('u.id', 'DESC');

        $queryResults = $query->get();

        $count = ($currentPage * $pageSize) - $pageSize;
        $data = [];

        foreach($queryResults as $result)
        {
            array_push($data, [
                'indexNo'               => ++$count,
                'id'                    => $result->id,
                'module_permission_id'  => $result->module_permission_id,
                'module_identifier'     => $result->module_identifier,
                'name'                  => $result->name,
                'email'                 => $result->email,
                'company'               => $result->company_name,
                'isEditor'              => $result->is_editor,
                'toggleEditorUrl'       => route('module.permissions.editor.toggle', [$result->id, $result->module_identifier]),
                'revokeUrl'             => route('module.permissions.revoke', [$result->id, $result->module_identifier]),
            ]);
        }

        return [
            'last_page' => ceil((float) ($rowCount / $pageSize)),
            'data'      => $data,
        ];
    }

    public function assign(array $userIds, $moduleId)
    {
        foreach($userIds as $userId)
        {
            ModulePermission::grant(User::find($userId), $moduleId);
        }

        return true;
    }

    public function revoke($userId, $moduleId)
    {
        return ModulePermission::revoke(User::find($userId), $moduleId);
    }

    public function checkForPendingTopManagementVerifierTasks($userId)
    {
        $user    = User::find($userId);
        $rotRepo = \App::make('PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformationRepository');
        $lotRepo = \App::make('PCK\TenderListOfTendererInformation\TenderListOfTendererInformationRepository');
        $ctRepo  = \App::make('PCK\TenderCallingTenderInformation\TenderCallingTenderInformationRepository');

        $pendingRot = $rotRepo->getPendingRecOfTenderersByUser($user, true);
        $pendingLot = $lotRepo->getPendingLotOfTenderersByUser($user, true);
        $pendingCt  = $ctRepo->getPendingCallingTendersByUser($user, true);

        $userHasInProgressListOfConsultantRecords = ConsultantManagementListOfConsultant::hasInProgressRecords($user);
        $userHasInProgressApprovalDocumentRecords = ApprovalDocument::hasInProgressRecords($user);

        $canDelete = true;
        $errors    = null;

        if(!empty($pendingRot) || 
            !empty($pendingLot) || 
            !empty($pendingCt) ||
            !empty($userHasInProgressListOfConsultantRecords) ||
            !empty($userHasInProgressApprovalDocumentRecords))
        {
            $canDelete = false;
            $errors    = trans('general.userHasPendingTasks');
        }

        return [$canDelete, $errors];
    }

    public function getUserSubsidiaryIds($moduleId)
    {
        // Get the current user and module permission
        $user = \Confide::user();
        $modulePermission = $user->modulePermission($moduleId)->first();

        // Retrieve user's subsidiary IDs or empty array if no permission
        return $modulePermission ? $modulePermission->getSubsidiaryIds() : array();
    }
}