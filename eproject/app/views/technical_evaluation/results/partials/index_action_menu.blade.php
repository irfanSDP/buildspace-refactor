<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
	    <li>
            <button data-route="{{ route('technicalEvaluation.results.summary.excel.export', [$project->id, $tender->id]) }}" class="btn btn-block btn-md btn-default" data-action="export-overall-report">
	    		<i class="fas fa-file-excel"></i>&nbsp;&nbsp;{{ trans('technicalEvaluation.exportSummaryReportToExcel') }}
	    	</button>
	    </li>
    </ul>
</div>