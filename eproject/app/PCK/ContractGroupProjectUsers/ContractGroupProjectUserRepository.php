<?php namespace PCK\ContractGroupProjectUsers;

use PCK\Companies\Company;
use PCK\Projects\Project;
use PCK\ContractGroups\Types\Role;
use PCK\ContractGroups\ContractGroup;
use PCK\Users\User;
use PCK\Buildspace\User as BsUser;
use PCK\Buildspace\ProjectUserPermission as BsProjectUserPermission;

class ContractGroupProjectUserRepository {

    /**
     * @var ContractGroupProjectUser
     */
    private $instance;

    public function __construct(ContractGroupProjectUser $instance)
    {
        $this->instance = $instance;
    }

    public function getAssignedUsersByProjectAndContractGroup(Project $project, ContractGroup $contractGroup)
    {
        $data = array();

        $results = \DB::table($this->instance->getTable())
            ->where('contract_group_id', '=', $contractGroup->id)
            ->where('project_id', '=', $project->id)
            ->get();

        foreach($results as $result)
        {
            $data[ $result->user_id ] = $result->is_contract_group_project_owner;
        }

        return $data;
    }

    public function insertByBatchRoles(Project $project, ContractGroup $contractGroup, array $inputs)
    {
        $data     = array();
        $ownerIds = array();
        // will delete existing records if available
        $this->deleteExistingRolesRecord($project, $contractGroup);

        // will get selected group project owner first, then only array unique for selected user to enter normal user
        if( isset( $inputs['is_contract_group_project_owners'] ) )
        {
            foreach($inputs['is_contract_group_project_owners'] as $userId)
            {
                $data[] = array( 'contract_group_id' => $contractGroup->id, 'project_id' => $project->id, 'user_id' => $userId, 'is_contract_group_project_owner' => true, 'created_at' => 'NOW()', 'updated_at' => 'NOW()' );

                $ownerIds[] = $userId;
            }
        }

        $companyAdmins = $project->getCompanyByGroup($contractGroup->group)->companyAdmins->filter(function($admin) {
            return $admin->isActive();
        });

        foreach($companyAdmins as $companyAdmin)
        {
            if( ! in_array($companyAdmin->id, $ownerIds) ) $inputs['selected_users'][] = $companyAdmin->id;
        }

        if( isset( $inputs['selected_users'] ) )
        {
            foreach($inputs['selected_users'] as $userId)
            {
                if( in_array($userId, $ownerIds) )
                {
                    continue;
                }

                $data[] = array(
                    'contract_group_id'               => $contractGroup->id,
                    'project_id'                      => $project->id,
                    'user_id'                         => $userId,
                    'is_contract_group_project_owner' => false,
                    'created_at'                      => 'NOW()',
                    'updated_at'                      => 'NOW()'
                );
            }
        }

        if( ! empty( $data ) )
        {
            return \DB::table($this->instance->getTable())->insert($data);
        }

        return false;
    }

    public function deleteExistingRolesRecord(Project $project, ContractGroup $contractGroup)
    {
        return \DB::table($this->instance->getTable())
            ->where('contract_group_id', '=', $contractGroup->id)
            ->where('project_id', '=', $project->id)
            ->delete();
    }

    public function assignCompanyAdmins(Project $project, Company $company)
    {
        $contractGroup = $company->getContractGroup($project);

        $this->deleteExistingRolesRecord($project, $contractGroup);

        // get all active users, excluding imported users
        foreach($company->getActiveUsers(false) as $user)
        {
            if( ! $company->isCompanyAdmin($user) ) continue;

            // set project creator as viewer and editor
            if($project->created_by == $user->id)
            {
                $this->addRole($project, $user, true);

                continue;
            }

            // set other admin users as viewers
            $this->addRole($project, $user);
        }

        return true;
    }

    /**
     * Assign all users of the company to the project.
     *
     * @param Project $project
     * @param Company $company
     * @param bool    $setEditor
     *
     * @return bool
     */
    public function assignAllUsers(Project $project, Company $company, $setEditor = false)
    {
        $contractGroup = $company->getContractGroup($project);

        $this->deleteExistingRolesRecord($project, $contractGroup);

        foreach($company->getActiveUsers() as $user)
        {
            $this->addRole($project, $user, $setEditor);
        }

        return true;
    }

    /**
     * Sets the user as either a verifier or editor for the project.
     *
     * @param Project $project
     * @param User    $user
     * @param bool    $setEditor
     *
     * @return bool
     */
    public function addRole(Project $project, User $user, $setEditor = false)
    {
        $contractGroup = $user->getAssignedCompany($project)->getContractGroup($project);

        return \DB::table($this->instance->getTable())->insert(array(
            'contract_group_id'               => $contractGroup->id,
            'project_id'                      => $project->id,
            'user_id'                         => $user->id,
            'is_contract_group_project_owner' => $setEditor,
            'created_at'                      => 'NOW()',
            'updated_at'                      => 'NOW()'
        ));
    }

    /**
     * Removes the user as an editor or verifier.
     *
     * @param Project $project
     * @param User    $user
     *
     * @return int
     */
    public function removeRole(Project $project, User $user)
    {
        $contractGroup = $user->getAssignedCompany($project)->getContractGroup($project);

        return \DB::table($this->instance->getTable())
            ->where('contract_group_id', '=', $contractGroup->id)
            ->where('project_id', '=', $project->id)
            ->where('user_id', '=', $user->id)
            ->delete();
    }

    public function syncBuildspaceProjectUserPermissions(Project $project, ContractGroup $contractGroup)
    {
        $currentUser              = \Confide::user();
        $isCurrentUserBU          = $currentUser->hasCompanyProjectRole($project, Role::PROJECT_OWNER);
        $bsUserIds                = [];
        $assignedUserIds          = array_keys($this->getAssignedUsersByProjectAndContractGroup($project, $contractGroup)); 
        $bsProjectmainInformation = $project->getBsProjectMainInformation();

        $bsProjectUserPermissionUserIds = BsProjectUserPermission::getAssignedUserIdsByProjectAndStatus($bsProjectmainInformation->projectStructure, $bsProjectmainInformation->status);

        $eprojectUserIdsInBsProjectUserPermissions = array_map(function($bsUserId) {
            $eprojectUser = BsUser::find($bsUserId)->Profile->getEProjectUser();

            if(is_null($eprojectUser)) return null;

            return $eprojectUser->id;
        }, $bsProjectUserPermissionUserIds);

        $contractGroupRepository = \App::make('PCK\ContractGroups\ContractGroupRepository');
        $contractGroup           = $contractGroupRepository->findById($contractGroup->id);

        // get all native and imported users of a company, regardless assigned or not assigned
        $company = $project->selectedCompanies()
            ->where('contract_group_id', '=', ContractGroup::getIdByGroup($contractGroup->group))
            ->first();

        $allCompanyUserIds = $company->getActiveUsers()->lists('id');

        // filter out users who do not belong to the contract group
        $eprojectUserIds = array_map(function($userId) use ($allCompanyUserIds) {
            if(in_array($userId, $allCompanyUserIds)) return $userId;
        }, $eprojectUserIdsInBsProjectUserPermissions);

        // remove null elements and re-indexing
        $eprojectUserIds = array_values(array_filter($eprojectUserIds, function($userId) {
            return ( ! is_null($userId) );
        }));

        $idsToAssign   = array_diff($assignedUserIds, $eprojectUserIds);    // ids to assign at buildspace side
        $idsToUnassign = array_diff($eprojectUserIds, $assignedUserIds);    // ids to unassign at buildspace side

        foreach($idsToAssign as $userId)
        {
            $user                = User::find($userId);
            $hasBuildspaceAccess = $isCurrentUserBU ? $user->getBuildspaceAccessFlagByStage($project, $bsProjectmainInformation->status) : true;

            if(!$hasBuildspaceAccess) continue;

            $project->grantBsProjectPermissionToUser($user, $bsProjectmainInformation->status);
        }

        foreach($idsToUnassign as $userId)
        {
            $user = User::find($userId);

            $project->revokeBsProjectPermission($user, $bsProjectmainInformation->status);
        }
    }
}