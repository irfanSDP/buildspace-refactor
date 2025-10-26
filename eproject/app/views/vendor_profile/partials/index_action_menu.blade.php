<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
	    <li>
	    	<button type="button" class="btn btn-block btn-success btn-mg header-btn" data-action="vendor-profile-export" data-url="{{ route('vendorProfile.export.excel', ['default']) }}">
                <i class="fa fa-file-excel"></i> {{ trans('general.exportToExcel') . ' (' . trans('vendorProfile.vendorProfileInfo') . ')' }}
	    	</button>
	    </li>
		<li class="divider"></li>
		<li>
	    	<button type="button" class="btn btn-block btn-success btn-mg header-btn" data-action="vendor-profile-export" data-url="{{ route('vendorProfile.export.excel', ['projectTrackRecord']) }}">
                <i class="fa fa-file-excel"></i> {{ trans('general.exportToExcel') . ' (' . trans('vendorManagement.projectTrackRecord') . ')' }}
	    	</button>
	    </li>
        <li class="divider"></li>
        <li>
	    	<button type="button" class="btn btn-block btn-success btn-mg header-btn" data-action="vendor-profile-export" data-url="{{ route('vendorProfile.export.excel', ['supplierCreditFacilities']) }}">
                <i class="fa fa-file-excel"></i> {{ trans('general.exportToExcel') . ' (' . trans('vendorManagement.supplierCreditFacilities') . ')' }}
	    	</button>
	    </li>
        <li class="divider"></li>
        <li>
	    	<button type="button" class="btn btn-block btn-success btn-mg header-btn" data-action="vendor-profile-export" data-url="{{ route('vendorProfile.export.excel', ['vendorRegistrationForm']) }}">
                <i class="fa fa-file-excel"></i> {{ trans('general.exportToExcel') . ' (' . trans('vendorProfile.vendorRegistrationForm') . ')' }}
	    	</button>
	    </li>
    </ul>
</div>