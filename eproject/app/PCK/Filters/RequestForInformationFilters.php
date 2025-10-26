<?php namespace PCK\Filters;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\RequestForInformation\RequestForInformation;

class RequestForInformationFilters {

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function canCreateRfiMessage(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! RequestForInformation::canCreateRfiMessage(\Confide::user(), $project) ) throw new InvalidAccessLevelException(trans('filter.addToRfiPermissionDenied'));
    }

    public function canPushMessage(Route $route)
    {
        $rfi = RequestForInformation::find($route->getParameter('requestForInformationId'));

        if( ! $rfi->canPushMessage(\Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.addToRfiPermissionDenied'));
    }

}