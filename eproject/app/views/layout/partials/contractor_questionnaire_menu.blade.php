@if (\PCK\Filters\TenderFilters::checkTenderQuestionnaireAccessLevelPermissionAllowed($project, $user))
    <li class="{{{ Request::is('projects/'.$currentProjectId.'/questionnaires*') ? 'active' : null }}}">
        <a href="{{ route('projects.questionnaires.index', array($currentProjectId)) }}" title="{{{ trans('general.questionnaires') }}}" class="text-truncate">
            <i class="fa fa-sm fa-fw fa-tasks"></i> {{{ trans('general.questionnaires') }}}
        </a>
    </li>
@endif