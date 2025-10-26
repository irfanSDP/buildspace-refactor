<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
	    <li>
            <button data-route="{{ route('users.all.export') }}" class="btn btn-block btn-md btn-success" data-action="export-all-users">
	    		<i class="fas fa-file-excel"></i>&nbsp;&nbsp;{{ trans('users.exportAllUsers') }}
	    	</button>
	    </li>
    </ul>
</div>