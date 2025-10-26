<?php namespace PCK\Filters\IndonesiaCivilContract;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTime;

class ExtensionOfTimeFilters {

    public function isEditor(Route $route)
    {
        $project = $route->getParameter('projectId');

        $user = \Confide::user();

        if( ! ( $user->getAssignedCompany($project)->id == $project->getSelectedContractor()->id ) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function canRespond(Route $route)
    {
        $eot = ExtensionOfTime::find($route->getParameter('eotId'));

        $user = \Confide::user();

        if( ! $eot->canRespond($user) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

}