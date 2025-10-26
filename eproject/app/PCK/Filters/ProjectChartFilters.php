<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\Users\User;
use PCK\ModulePermission\ModulePermission;
use PCK\ProjectReport\ProjectReport;
use PCK\ProjectReport\ProjectReportColumn;
use PCK\ProjectReport\ProjectReportTypeMapping;
use PCK\ProjectReport\ProjectReportUserPermission;
use PCK\ContractGroups\Types\Role;
use PCK\Verifier\Verifier;

class ProjectChartFilters
{
    private $user;

    public function __construct()
    {
        $this->user = \Confide::user();
    }

    public function templatePermission(Route $route)
    {
        if (! ModulePermission::hasPermission($this->user, ModulePermission::MODULE_ID_PROJECT_REPORT_CHART_TEMPLATE))
        {
            throw new InvalidAccessLevelException(trans('projectReportChart.operationIsNotAllowed'));
        }
    }

    public function chartPermission(Route $route)
    {
        if (! ModulePermission::hasPermission($this->user, ModulePermission::MODULE_ID_PROJECT_REPORT_CHARTS))
        {
            throw new InvalidAccessLevelException(trans('projectReportChart.operationIsNotAllowed'));
        }
    }
}