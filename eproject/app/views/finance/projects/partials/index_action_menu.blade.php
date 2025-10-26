<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
		<li>
	    	<a href="{{ route('finance.claim-certificate.projects') }}" class="btn btn-block btn-md btn-warning">
	    		<i class="fas fa-list"></i> {{ trans('finance.claimCertificatesReport') }}
	    	</a>
	    </li>
		<li class="divider"></li>
    	<li>
	    	<a href="{{ route('finance.claim-certificate') }}" class="btn btn-block btn-md btn-info">
	    		<i class="fas fa-list"></i> {{ trans('finance.claimCertificates') }}
	    	</a>
	    </li>
    </ul>
</div>