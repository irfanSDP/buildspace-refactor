<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\Buildspace\ProjectMainInformation;
use PCK\ContractManagementModule\UserPermission\ContractManagementUserPermission;
use PCK\Exceptions\InvalidAccessLevelException;

class ContractManagementFilters {

    public function hasContractManagement(Route $route)
    {
        $project = $route->getParameter('projectId');

        $mainInfo = $project->getBsProjectMainInformation();

        if( $mainInfo->post_contract_type != ProjectMainInformation::POST_CONTRACT_TYPE_NEW ) throw new InvalidAccessLevelException(trans('filter.moduleNonExistent'));
    }

    public function isUserManager(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! ContractManagementUserPermission::isUserManager(\Confide::user(), $project) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }
}