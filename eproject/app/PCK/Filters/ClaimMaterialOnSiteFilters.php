<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\Buildspace\ContractManagementClaimVerifier;
use PCK\Buildspace\PostContractClaim;
use PCK\ContractGroups\Types\Role;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\Projects\Project;
use PCK\Users\User;

class ClaimMaterialOnSiteFilters {

    public function isValidSubstitute(Route $route)
    {
        $project  = $route->getParameter('projectId');
        $objectId = $route->getParameter('objectId');

        if( ! self::canSubstitute($project, \Confide::user(), $objectId) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public static function canSubstitute(Project $project, User $user, $objectId)
    {
        $assignedCompany = $user->getAssignedCompany($project);

        return ContractManagementClaimVerifier::isPending($project, PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE, $objectId) && ( $assignedCompany->hasProjectRole($project, Role::PROJECT_OWNER) && $user->isGroupAdmin() );
    }

}