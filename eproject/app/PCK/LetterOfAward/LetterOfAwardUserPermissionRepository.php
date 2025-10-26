<?php namespace PCK\LetterOfAward;

use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Helpers\DataTables;
use PCK\ContractGroups\Types\Role;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUserRepository;


class LetterOfAwardUserPermissionRepository
{
    private $contractGroupProjectUserRepository;

    public function __construct(ContractGroupProjectUserRepository $contractGroupProjectUserRepository)
    {
        $this->contractGroupProjectUserRepository = $contractGroupProjectUserRepository;
    }

    public function getAssignableUsers(Project $project, $inputs)
    {
        $moduleId = $inputs['module_identifier'];

        $idColumn      = 'users.id';
        $selectColumns = [$idColumn, 'users.name'];

        $userColumns = [
            'name'  => 1,
            'email' => 2
        ];

        $companyColumns = [
            'name' => 3
        ];

        $allColumns = [
            'users'     => $userColumns,
            'companies' => $companyColumns
        ];

        $currentlyAssignedUserIds = LetterOfAwardUserPermission::where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->get()
            ->lists('user_id');

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
            $indexNo = ( $arrayIndex + 1 ) + ( $datatable->properties->pagingOffset );
            $record  = User::find($arrayItem->id);

            $dataArray[] = [
                'indexNo'     => $indexNo,
                'id'          => $record->id,
                'name'        => $record->name,
                'email'       => $record->email,
                'companyName' => $record->company->name,
            ];
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function getAssignedUsers(Project $project, $inputs)
    {
        $idColumn      = 'users.id';
        $selectColumns = [$idColumn, 'users.name'];

        $userColumns = [
            'name'  => 1,
            'email' => 2
        ];

        $companyColumns = [
            'name' => 3
        ];

        $allColumns = [
            'users'     => $userColumns,
            'companies' => $companyColumns
        ];

        $moduleId = \Input::get('module_identifier');

        $currentlyAssignedUserIds = LetterOfAwardUserPermission::where('module_identifier', '=', $moduleId)
            ->where('project_id', '=', $project->id)
            ->get()
            ->lists('user_id');

        $query = \DB::table("users as users");

        $datatable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

        $datatable->properties->query->join('companies', 'companies.id', '=', 'users.company_id');
        $datatable->properties->query->where('companies.confirmed', '=', true);
        $datatable->properties->query->whereIn('users.id', $currentlyAssignedUserIds);

        $datatable->addAllStatements();

        $results = $datatable->getResults();

        $dataArray = [];

        foreach($results as $arrayIndex => $arrayItem)
        {
            $indexNo = ( $arrayIndex + 1 ) + ( $datatable->properties->pagingOffset );
            $user  = User::find($arrayItem->id);

            $letterOfAwardUserPermission = $user->letterOfAwardUserPermissions->filter(function ($object) use ($moduleId, $project) {
                return ($object->module_identifier == $moduleId) && ($object->project_id == $project->id);
            })->first();

            $dataArray[] = [
                'indexNo'                  => $indexNo,
                'id'                       => $user->id,
                'name'                     => $user->name,
                'email'                    => $user->email,
                'isEditor'                 => $letterOfAwardUserPermission->is_editor,
                'route:toggleEditorStatus' => route('letterOfAward.user.permissions.editor.toggle', [$project->id, $user->id, $moduleId]),
                'route:revoke'             => route('letterOfAward.user.permissions.revoke', [$project->id, $user->id, $moduleId]),
                'route:revokePendingCheck' => route('letterOfAward.pending.check', [$project->id, $user->id, $moduleId]),
            ];
        }

        return $datatable->dataTableResponse($dataArray);
    }

    public function assign(Project $project, $inputs) {
        $selectedUserIds = $inputs['users'] ?? [];
        $moduleIdentifier = $inputs['module_identifier'];
        $success = false;

        foreach($selectedUserIds as $userId) {
            $record = new LetterOfAwardUserPermission();
            $record->project_id = $project->id;
            $record->user_id = $userId;
            $record->added_by = \Confide::user()->id;            
            $record->module_identifier = $moduleIdentifier;
            $record->save();
        }

        return true;
    }


    public function toggleEditorStatus(Project $project, $userId, $moduleId) {
        $user = User::find($userId);

        $record = $user->letterOfAwardUserPermissions->filter(function ($object) use ($moduleId, $project) {
            return ($object->module_identifier == $moduleId) && ($object->project_id == $project->id);
        })->first();

        $record->is_editor = !$record->is_editor;

        return $record->save();
    }

    public function revoke(Project $project, $userId, $moduleId) {
        $user = User::find($userId);

        $record = $user->letterOfAwardUserPermissions->filter(function ($object) use ($moduleId, $project) {
            return ($object->module_identifier == $moduleId) && ($object->project_id == $project->id);
        })->first();

        return $record->delete();
    }

    public static function isAdminUserOfProjectOwnerOrGCD($project) {
        if(!$project) return false;

        $isProjectOwnerOrGCD = \Confide::user()->hasCompanyProjectRole($project, [Role::PROJECT_OWNER, Role::GROUP_CONTRACT]);
        $isGroupAdmin = \Confide::user()->isGroupAdmin();

        return $isProjectOwnerOrGCD && $isGroupAdmin;
    }

    // check if a given user is a user assigned to any of the roles in LA for a given project
    public static function getIsUserAssignedToLetterOfAwardByProject($project, $user) {
        if(!$project) return false;

        $records = LetterOfAwardUserPermission::where('project_id', $project->id)
                        ->where('user_id', $user->id)
                        ->get();

        return $records->count() != 0;
    }
}

