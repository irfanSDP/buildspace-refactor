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

class ProjectReportFilters
{
    private $user;

    public function __construct()
    {
        $this->user = \Confide::user();
    }

    public function templatePermissionCheck(Route $route)
    {
        if(!ModulePermission::hasPermission($this->user, ModulePermission::MODULE_ID_PROJECT_REPORT_TEMPLATE))
        {
            throw new InvalidAccessLevelException(trans('projectReport.operationNotAllowed'));
        }
    }

    public function hasProjectReportPermission(Route $route)
    {
        $project = $route->getParameter('projectId');
        
        if(!ProjectReportUserPermission::hasProjectReportPermission($project, $this->user))
        {
            throw new InvalidAccessLevelException(trans('projectReport.operationNotAllowed'));
        }
    }

    public function hasProjectTypePermission(Route $route)
    {
        $project   = $route->getParameter('projectId');
        $mappingId = $route->getParameter('mappingId');
        $mapping   = ProjectReportTypeMapping::find($mappingId);

        if(!ProjectReportUserPermission::hasProjectReportTypePermission($project, $this->user, $mapping->projectReportType->id))
        {
            throw new InvalidAccessLevelException(trans('projectReport.operationNotAllowed'));
        }
    }

    public function isTemplateCheck(Route $route)
    {
        $projectReport = ProjectReport::find($route->getParameter('projectReportId'));

        if(!$projectReport->isTemplate())
        {
            throw new InvalidAccessLevelException(trans('projectReport.operationNotAllowed'));
        }
    }

    public function isDraftCheck(Route $route)
    {
        $projectReport = ProjectReport::find($route->getParameter('projectReportId'));

        if(!$projectReport->isDraft())
        {
            throw new InvalidAccessLevelException(trans('projectReport.operationNotAllowed'));
        }
    }

    public function isCompletedCheck(Route $route)
    {
        $projectReport = ProjectReport::find($route->getParameter('projectReportId'));

        if($projectReport && !$projectReport->isCompleted())
        {
            throw new InvalidAccessLevelException(trans('projectReport.operationNotAllowed'));
        }
    }

    public function userPermissionAccessCheck(Route $route)
    {
        $project = $route->getParameter('projectId');

        $isProjectUser = \PCK\ContractGroupProjectUsers\ContractGroupProjectUser::where('project_id', '=', $project->id)->where('user_id', '=', $this->user->id)->exists();

        if(!$isProjectUser) {
            throw new InvalidAccessLevelException(trans('projectReport.operationNotAllowed'));
        }
    }

    public function canCreateNewRevisionCheck(Route $route)
    {
        $project   = $route->getParameter('projectId');
        $mappingId = $route->getParameter('mappingId');
        $mapping   = ProjectReportTypeMapping::find($mappingId);
        
        $latestProjectReport = ProjectReport::getLatestProjectReport($project, $mapping);
        
        if($latestProjectReport && !$latestProjectReport->isCompleted())
        {
            throw new InvalidAccessLevelException(trans('projectReport.operationNotAllowed'));
        }
    }

    public function canDeleteTemplateCheck(Route $route)
    {
        $projectReport = ProjectReport::find($route->getParameter('projectReportId'));

        if(! $projectReport->isTemplate() || ProjectReport::hasProjectReports($projectReport->id))
        {
            throw new InvalidAccessLevelException(trans('projectReport.operationNotAllowed'));
        }
    }

    public function isCurrentVerifier(Route $route)
    {
        $project   = $route->getParameter('projectId');
        $mappingId = $route->getParameter('mappingId');
        $mapping   = ProjectReportTypeMapping::find($mappingId);

        $latestProjectReport = ProjectReport::getLatestProjectReport($project, $mapping);
        
        if(!Verifier::isCurrentVerifier($this->user, $latestProjectReport))
        {
            throw new InvalidAccessLevelException(trans('projectReport.operationNotAllowed'));
        }
    }

    public function dashboardPermissionCheck()
    {
        if(!ModulePermission::hasPermission($this->user, ModulePermission::MODULE_ID_PROJECT_REPORT_DASHBOARD))
        {
            throw new InvalidAccessLevelException(trans('projectReport.operationNotAllowed'));
        }
    }
}