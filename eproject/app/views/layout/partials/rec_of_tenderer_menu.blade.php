@if (\PCK\Filters\TenderFilters::checkTenderAccessLevelPermissionAllowed($project, $user) && ((!$project->skippedToPostContract()) || $project->stageSequenceCompare('>=', \PCK\Projects\Project::STATUS_TYPE_CLOSED_TENDER)))
    <li class="{{{ Request::is('projects/'.$currentProjectId.'/tenders*') ? 'active' : null }}}">
        <a href="{{ route('projects.tender.index', array($currentProjectId)) }}" title="{{{ PCK\Projects\Project::getStatusById($project->current_tender_status) }}}" class="text-truncate">
            <i class="fa fa-sm fa-fw fa-trophy"></i>
            {{{ PCK\Projects\Project::getStatusById($project->current_tender_status) }}}
        </a>
    </li>
@endif