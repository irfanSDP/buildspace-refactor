<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-info" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        <li>
            <button type="button" class="btn btn-block btn-md btn-default" data-action="overview">
                <i class="fa fa-lg fa-fw fa-th-list"></i> {{ trans('folders.overview') }}
            </button>
        </li>
    </ul>
</div>