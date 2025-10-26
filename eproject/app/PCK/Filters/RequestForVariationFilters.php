<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\RequestForVariation\RequestForVariation;

class RequestForVariationFilters
{
    public function requestForVariationStatusApprovedCheck(Route $route)
    {
        $requestForVariation = $route->getParameter('rfvId');

        if( ! $requestForVariation->isApproved() )
        {
            throw new InvalidAccessLevelException(trans('requestForVariation.invalidOperation'));
        }
    }
}