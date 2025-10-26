<?php namespace PCK\Filters\IndonesiaCivilContract;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\IndonesiaCivilContract\UserPermission;

class UserPermissionFilters {

    public function isUserPermissionManager(Route $route)
    {
        $project = $route->getParameter('projectId');
        $user    = \Confide::user();

        if( ! UserPermission::isUserPermissionManager($project, $user) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

}