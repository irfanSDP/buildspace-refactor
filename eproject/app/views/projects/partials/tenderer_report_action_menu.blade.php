<div class="dropdown {{{ $classes ?? '' }}}">
    <a role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        <li>
            <a href="{{ route('projects.openTender.report.export', [$project->id]) }}" class="btn btn-sm btn-success">
                <i class="fa fa-file-excel"></i>
                {{ trans('general.exportToExcel') }}
            </a>
        </li>
    </ul>
</div>