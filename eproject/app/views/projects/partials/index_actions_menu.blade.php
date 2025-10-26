<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <button data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:;"> {{ trans('general.actions') }} <span class="caret"></span> </button>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        <li>
            <a href="{{ route('projectsOverview.excel.export') }}" target="_blank" class="btn btn-block btn-md btn-success"><i class="fa fa-file-excel" aria-hidden="true"></i> {{ trans('projects.exportProjectOverview') }}</a>
        </li>
    </ul>
</div>