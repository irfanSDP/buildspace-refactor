<?php namespace PCK\Filters;

use Confide;
use Illuminate\Routing\Route;
use PCK\ContractGroups\Types\Role;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\Projects\Project;
use PCK\Tenders\Tender;

class TechnicalEvaluationFilters {

    public static function editorRoles(Project $project)
    {
        return Tender::rolesAllowedToUseModule($project);
    }

    public static function accessRoles()
    {
        return Role::getRolesExcept(Role::CONTRACTOR);
    }

    public function hasAccess(Route $route)
    {
        $project = $route->getParameter('projectId');

        $user = Confide::user();

        if( ! $user->hasCompanyProjectRole($project, self::accessRoles()) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function canUpdateEvaluationResults(Route $route)
    {
        $project = $route->getParameter('projectId');

        $user = Confide::user();

        if( ! $user->hasCompanyProjectRole($project, self::accessRoles()) || !$user->isEditor($project) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

}