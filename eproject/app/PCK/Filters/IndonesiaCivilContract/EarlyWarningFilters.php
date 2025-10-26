<?php namespace PCK\Filters\IndonesiaCivilContract;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;

class EarlyWarningFilters {

    public function isEditor(Route $route)
    {
        $project = $route->getParameter('projectId');

        $user = \Confide::user();

        if( ! ( $user->getAssignedCompany($project)->id == $project->getSelectedContractor()->id ) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

}