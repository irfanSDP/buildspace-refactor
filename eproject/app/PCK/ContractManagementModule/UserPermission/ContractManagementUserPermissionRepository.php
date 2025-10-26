<?php namespace PCK\ContractManagementModule\UserPermission;

use PCK\Buildspace\ContractManagementClaimVerifier;
use PCK\Buildspace\ContractManagementVerifier;
use PCK\Helpers\DataTables;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\ModulePermission\ModulePermission;
use PCK\Buildspace\PostContractClaim;

class ContractManagementUserPermissionRepository
{
    public function getAssignableUsers(Project $project, array $inputs)
    {
        $moduleId = $inputs['moduleId'];
        $currentPage = $inputs['page'];
        $pageSize = $inputs['size'];
        $filters =  isset($inputs['filters']) ? $inputs['filters'] : [];

        $currentlyAssignedUserIds = ContractManagementUserPermission::where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->get()
            ->lists('user_id');

        $userIds = $project->contractGroupProjectUsers->lists('user_id');

        $query = \DB::table('users AS u')->select('u.id', 'u.name', 'u.email', 'c.name AS company_name');
        $query->join('companies AS c', 'c.id', '=', 'u.company_id');
        $query->where('c.confirmed', true);
        $query->where('u.confirmed', true);
        $query->where('u.account_blocked_status', false);
        $query->whereIn('u.id', $userIds);
        $query->whereNotIn('u.id', $currentlyAssignedUserIds);

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

    public function getAssignedUsers(Project $project, array $inputs)
    {
        $moduleId = $inputs['moduleId'];
        $currentPage = $inputs['page'];
        $pageSize = $inputs['size'];
        $filters =  isset($inputs['filters']) ? $inputs['filters'] : [];

        $query = \DB::table('contract_management_user_permissions AS m')->select('u.id', 'm.project_id', 'm.id AS module_permission_id', 'm.module_identifier', 'u.name', 'u.email', 'c.name AS company_name', 'm.is_verifier');
        $query->join('users AS u', 'u.id', '=', 'm.user_id');
        $query->join('companies AS c', 'c.id', '=', 'u.company_id');
        $query->where('c.confirmed', true);
        $query->where('m.module_identifier', $moduleId);
        $query->where('m.project_id', $project->id);

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
                'isVerifier'            => $result->is_verifier,
                'toggleVerifierUrl'     => route('contractManagement.permissions.verifier.toggle', [$result->project_id, $result->id, $result->module_identifier]),
                'revokeUrl'             => route('contractManagement.permissions.revoke', [$result->project_id, $result->id, $result->module_identifier]),
            ]);
        }

        return [
            'last_page' => ceil((float) ($rowCount / $pageSize)),
            'data'      => $data,
        ];
    }

    public function assign(array $userIds, $moduleId, Project $project)
    {
        foreach($userIds as $userId)
        {
            ContractManagementUserPermission::assign($moduleId, User::find($userId), $project);
        }

        return true;
    }

    public function revoke($userId, $moduleId, Project $project)
    {
        $user = User::find($userId);

        if( array_key_exists($moduleId, ContractManagementClaimVerifier::getPendingRecordsByModule($user, false, $project)) ) return false;

        if( array_key_exists($moduleId, ContractManagementVerifier::getPendingRecordsByModule($user, false, $project)) ) return false;

        return ContractManagementUserPermission::unAssign($moduleId, $user, $project);
    }

    public function toggleVerifierStatus($userId, $moduleId, $project)
    {
        $record = ContractManagementUserPermission::where('user_id', '=', $userId)
            ->where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->first();

        $record->is_verifier = ( ! $record->is_verifier );

        return $record->save();
    }

    public function getVerifierList(Project $project, $moduleId)
    {
        $userIds = ContractManagementUserPermission::where('project_id', '=', $project->id)
            ->where('module_identifier', '=', $moduleId)
            ->where('is_verifier', '=', true)
            ->orderBy('id', 'desc')
            ->get()
            ->lists('user_id');

        return User::whereIn('id', $userIds)->get();
    }

}