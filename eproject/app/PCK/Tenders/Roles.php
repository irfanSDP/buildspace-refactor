<?php namespace PCK\Tenders;

use PCK\Projects\Project;
use PCK\ContractGroups\Types\Role;

trait Roles {

    public static function viewAllRoles()
    {
        return array(
            Role::PROJECT_OWNER,
            Role::GROUP_CONTRACT,
        );
    }

    public static function rolesAllowedToUseModule(Project $project)
    {
        return array(
            Role::PROJECT_OWNER,
            Role::GROUP_CONTRACT,
            $project->getCallingTenderRole()
        );
    }

}