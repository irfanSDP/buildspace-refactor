<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        @if ( $project->latestTender->isTenderOpen() )
            <li>
                <a href="{{route('projects.openTender.report', array($project->id))}}"
                   class="btn btn-block btn-info btn-md">Tenderer's Report</a>
            </li>
        @endif
    </ul>
</div>