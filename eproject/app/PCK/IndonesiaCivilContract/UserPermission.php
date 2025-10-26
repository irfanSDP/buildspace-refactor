<?php namespace PCK\IndonesiaCivilContract;

use PCK\ContractGroups\Types\Role;
use PCK\Contracts\Contract;
use PCK\Projects\Project;
use PCK\Users\User;

class UserPermission {

    public static function isUserPermissionManager(Project $project, User $user)
    {
        if( ! $project->contractIs(Contract::TYPE_INDONESIA_CIVIL_CONTRACT) ) return false;

        return ( $user->hasCompanyProjectRole($project, Role::PROJECT_OWNER) && $user->isGroupAdmin() );
    }

}