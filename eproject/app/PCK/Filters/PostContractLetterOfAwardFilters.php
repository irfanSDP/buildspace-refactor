<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\Buildspace\ContractManagementVerifier;
use PCK\Buildspace\PostContractClaim;
use PCK\ContractGroups\Types\Role;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\Projects\Project;
use PCK\Users\User;

class PostContractLetterOfAwardFilters {

    public function isValidSubstitute(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! self::canSubstitute($project, \Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public static function canSubstitute(Project $project, User $user)
    {
        $assignedCompany = $user->getAssignedCompany($project);

        return ContractManagementVerifier::isPending($project, PostContractClaim::TYPE_LETTER_OF_AWARD) && ( $assignedCompany->hasProjectRole($project, Role::PROJECT_OWNER) && $user->isGroupAdmin() );
    }

}