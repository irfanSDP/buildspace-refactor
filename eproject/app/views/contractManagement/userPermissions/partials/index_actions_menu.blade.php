<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <button data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:;"> {{ trans('general.actions') }} <span class="caret"></span> </button>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        <li>
            <a data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers" class="btn btn-block btn-md btn-info"><i class="fa fa-check-square"></i> {{{ trans('modulePermissions.assignUsers') }}}</a>
        </li>
        <li class="divider"></li>
        <li>
            <a id="btnAssignVerifiers" class="btn btn-block btn-md btn-warning "><i class="fa fa-eye"></i></i> {{{ trans('contractManagement.assignVerifiers') }}}</a>
        </li>
    </ul>
</div>