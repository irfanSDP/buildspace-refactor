<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;

class RolesFilter {

    public function checkCurrentRoles(Route $route)
    {
        $roles = func_get_args();

        // unset unused additional parameters
        array_shift($roles);
        array_shift($roles);

        $user = \Confide::user();

        if( ! $user->hasCompanyRoles($roles) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function checkCurrentProjectRoles(Route $route)
    {
        $project = $route->getParameter('projectId');
        $roles   = func_get_args();

        // unset unused additional parameters
        array_shift($roles);
        array_shift($roles);

        $user = \Confide::user();

        if( ! $user->hasCompanyProjectRole($project, $roles) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function doesNotHaveProjectRoles(Route $route)
    {
        $project = $route->getParameter('projectId');
        $roles   = func_get_args();

        // unset unused additional parameters
        array_shift($roles);
        array_shift($roles);

        $user = \Confide::user();

        if( $user->hasCompanyProjectRole($project, $roles) )
        {
            throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
        }
    }

    public function checkIsEditor(Route $route)
    {
        $user = \Confide::user();

        if( ! $user->isEditor($route->getParameter('projectId')) )
        {
            throw new InvalidAccessLevelException(trans('filter.notProjectEditorPermissionDenied'));
        }
    }

    public function canAddUser(Route $route)
    {
        $user = \Confide::user();

        if( ! $user->canAddUser() ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
}