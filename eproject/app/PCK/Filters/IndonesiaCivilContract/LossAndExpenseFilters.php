<?php namespace PCK\Filters\IndonesiaCivilContract;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\IndonesiaCivilContract\LossAndExpense\LossAndExpense;

class LossAndExpenseFilters {

    public function isEditor(Route $route)
    {
        $project = $route->getParameter('projectId');

        $user = \Confide::user();

        if( ! ( $user->getAssignedCompany($project)->id == $project->getSelectedContractor()->id ) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function canRespond(Route $route)
    {
        $le = LossAndExpense::find($route->getParameter('leId'));

        $user = \Confide::user();

        if( ! $le->canRespond($user) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

}