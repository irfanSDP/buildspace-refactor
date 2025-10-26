<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
    	<li>
	    	<a href="{{ route('finance.claim-certificate') }}" class="btn btn-block btn-md btn-warning">
	    		<i class="fas fa-list"></i> {{ trans('finance.claimCertificates') }}
	    	</a>
	    </li>
	    <li class="divider"></li>
		<li>
	    	<a href="{{ route('finance.account.code.settings.index') }}" class="btn btn-block btn-md btn-primary">
	    		<i class="fas fa-list"></i> {{ trans('finance.accountCodeSettings') }}
	    	</a>
	    </li>
		<li class="divider"></li>
	    <li>
	        <button data-route="{{ route('finance.claim-certificate.projects.exportReport') }}" class="btn btn-block btn-md btn-success" data-action="export-claim-cert-reports">
	            <i class="fas fa-download"></i> {{ trans('general.export') }}
	        </button>
	    </li>
		<li class="divider"></li>
		<li>
	        <button data-route="{{ route('finance.claim-certificate.projects.exportReport.with.creditDebitNotes') }}" class="btn btn-block btn-md btn-info" data-action="export-claim-cert-reports">
	            <i class="fas fa-download"></i> {{ trans('finance.exportWithDebitCreditNotes') }}
	        </button>
	    </li>
    </ul>
</div>