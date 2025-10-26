<?php namespace PCK\RequestForVariation;

use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Helpers\DataTables;
use PCK\ContractGroups\Types\Role;
use PCK\RequestForVariation\RequestForVariationActionLog;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUserRepository;

class RequestForVariationUserPermissionRepository
{
    private $contractGroupProjectUserRepository;

    public function __construct(ContractGroupProjectUserRepository $contractGroupProjectUserRepository)
    {
        $this->contractGroupProjectUserRepository = $contractGroupProjectUserRepository;
    }

    public function getAssignableUsers(Project $project, $inputs)
    {
        $roleId                = isset($inputs['role']) ? $inputs['role'] : 0;
        $userPermissionGroupId = isset($inputs['gid']) ? $inputs['gid'] : null;

        $idColumn      = 'users.id';
        $selectColumns = [$idColumn, 'users.name'];

        $userColumns = [
            'name'  => 1,
            'email' => 2
        ];

        $allColumns = [
            'users' => $userColumns,
        ];

        $currentlyAssignedUserIds = [];

        if($userPermissionGroupId)
        {
            $currentlyAssignedUserIds = \DB::table('request_for_variation_user_permission_groups AS g')
                                            ->join('request_for_variation_user_permissions AS p', 'g.id', '=', 'p.request_for_variation_user_permission_group_id')
                                            ->where('g.id', $userPermissionGroupId)
                                            ->where('g.project_id', $project->id)
                                            ->where('p.module_id', $roleId)
                                            ->lists('p.user_id');
        }

        $userIds = $project->contractGroupProjectUsers->lists('user_id');

        $query = \DB::table("users as users");

        $datatable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

        $query->where('users.confirmed', '=', true);
        $query->where('users.account_blocked_status', '=', false);
        $query->whereIn('users.id', $userIds);
        $datatable->properties->query->whereNotIn('users.id', $currentlyAssignedUserIds);

        $datatable->addAllStatements();

        $results = $datatable->getResults();

        $dataArray = [];

        foreach($results as $arrayIndex => $arrayItem)
        {
            $user    = User::find($arrayItem->id);
            $company = $user->getAssignedCompany($project);

            if(is_null($company)) continue;

            $indexNo = ( $arrayIndex + 1 ) + ( $datatable->properties->pagingOffset );

            $dataArray[] = [
                'indexNo'     => $indexNo,
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'companyName' => $user->getAssignedCompany($project)->name,
            ];
        }

        return $datatable->dataTableResponse($dataArray);
    }
    
    public static function isAdminUserOfProjectOwnerOrGCD(Project $project=null)
    {
        if(!$project) return false;

        $isProjectOwnerOrGCD = \Confide::user()->hasCompanyProjectRole($project, [Role::PROJECT_OWNER, Role::GROUP_CONTRACT]);
        $isGroupAdmin = \Confide::user()->isGroupAdmin();

        return $isProjectOwnerOrGCD && $isGroupAdmin;
    }

    public static function isProjectOwnnerOrConsultant(Project $project=null)
    {
        if(!$project) return false;

        return \Confide::user()->hasCompanyProjectRole($project, Role::getRolesExcept(Role::CONSULTANT_17));
    }

    // check if a given user is a user assigned to any of the roles in RFV
    public static function getIsUserAssignedToRfvByProject(Project $project=null, User $user)
    {
        $query = \DB::table('request_for_variation_user_permission_groups AS g')
                    ->join('request_for_variation_user_permissions AS p', 'g.id', '=', 'p.request_for_variation_user_permission_group_id');

        if($project)
        {
            $query->where('g.project_id', $project->id);
        }

        $count = $query->where('p.user_id', $user->id)->count();

        return ($count != 0);
    }
}


