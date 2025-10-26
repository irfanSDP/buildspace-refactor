<?php $canViewLogs               = isset($canViewLogs) ? $canViewLogs : false; ?>
<?php $canEditVendorProfile      = isset($canEditVendorProfile) ? $canEditVendorProfile : false; ?>
<?php $canPrintVendorCertificate = isset($canPrintVendorCertificate) ? $canPrintVendorCertificate : false; ?>

<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
	    @if($canViewLogs)
        <li>
            <a href="#" data-toggle="modal" data-target="#remarkLogsModal" class="btn btn-primary btn-md header-btn">{{ trans('vendorManagement.remarkLogs') }}</a>
	    </li>
        <li>
            <a href="#" data-toggle="modal" data-target="#submissionLogsModal" class="btn btn-primary btn-md header-btn">{{ trans('vendorManagement.submissionLogs') }}</a>
	    </li>
        <li>
            <a href="#" data-toggle="modal" data-target="#verifierStatusOverviewModal" class="btn btn-primary btn-md header-btn">{{ trans('verifiers.verifierLogs') }}</a>
	    </li>
        <li>
            <a href="#" id="viewActionLogsButton" class="btn btn-primary btn-md header-btn">{{ trans('general.editLogs') }}</a>
	    </li>
        @endif
        @if($canEditVendorProfile)
		<li>
            <a href="{{ route('vendorProfile.edit', [$company->id]) }}" class="btn btn-warning btn-md header-btn">
                <i class="far fa-edit"></i> {{{ trans('forms.edit') }}}
            </a>
	    </li>
        @endif
        @if($canPrintVendorCertificate)
        <li>
            <a href="{{ route('vendorProfile.registrationCertificate', [$company->id]) }}" target="_blank" class="btn btn-success btn-md header-btn">
                <i class="fa fa-print"></i> {{{ trans('vendorManagement.registrationCertificate') }}}
            </a>
	    </li>
        @endif
    </ul>
</div>