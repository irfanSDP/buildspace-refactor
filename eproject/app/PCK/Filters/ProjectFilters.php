<?php namespace PCK\Filters;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PCK\Exceptions\InvalidAccessLevelException;

class ProjectFilters {

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function checkValidStatusForPostContract(Route $route)
    {
        $project      = $route->getParameter('projectId');
        $tender       = $project->latestTender;
        $contractorId = $tender->currently_selected_tenderer_id;
        

        if( $project->onPostContractStages() ) throw new InvalidAccessLevelException("[{$project->title}] " . trans('filter.alreadyPostContract'));

        if( ! $project->onLastTenderingStage() ) throw new InvalidAccessLevelException(trans('filter.invalidStatus'));

        $selectedContractors = $project->latestTender->selectedFinalContractors->fetch('id');

        if( ! in_array($contractorId, $selectedContractors->toArray()) ) throw new InvalidAccessLevelException(trans('filter.invalidSelectedContractor'));
    }

    public function checkValidStatusForCompletion(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( $project->isCompleted() ) throw new InvalidAccessLevelException("[{$project->title}] " . trans('filter.projectAlreadyCompleted'));
    }

    public function canManuallySkipToPostContract(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! $project->canManuallySkipToPostContract() ) throw new InvalidAccessLevelException("[{$project->title}] " . trans('filter.projectCannotSkipToPostContract'));
    }

    public function canAddSubProject(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! $project->isMainProject() ) throw new InvalidAccessLevelException("[{$project->title}] " . trans('filter.subPackageCannotBeAdded'));
    }

    public function isInCallingTenderStage(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! $project->inCallingTender() ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function technicalEvaluationIsOpen(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! $project->latestTender->technicalEvaluationIsOpen() ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function isCurrentTenderStatusClosed(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! $project->isCurrentTenderStatusClosed() ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function canSyncBuildspaceContractorRates(Route $route)
    {
        $project = $route->getParameter('projectId');

        if( ! $project->canSyncBuildSpaceContractorRates() )
        {
            throw new InvalidAccessLevelException(trans('filter.canOnlySyncRatesAfterOpenTender'));
        }
    }
}