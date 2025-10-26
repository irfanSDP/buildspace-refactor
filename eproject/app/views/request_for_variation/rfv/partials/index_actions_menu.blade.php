<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        @if ($canCreateRfv && $contractAndContingencySumFilled)
            <li>
                <a href="{{ route('requestForVariation.new.create', [$project->id]) }}" class="btn btn-block btn-md btn-success {{{ !$contractAndContingencySumFilled ? 'disabled' : null }}} ">
                    <i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;{{ trans('requestForVariation.addNewRFV') }}
                </a>
            </li>
            <li class="divider"></li>
        @endif
        @if ($canViewContractAndContingencySum)
            <li>
                <a href="#" id="btnShowContractAndContingencySum" class="btn btn-block btn-md btn-warning">
                    <i class="fa fa-dollar" aria-hidden="true"></i>&nbsp;&nbsp;{{ trans('requestForVariation.contractAndContingencySum') }}
                </a>
            </li>
            <li class="divider"></li>
        @endif
        @if($canViewVOReportForProject)
        <li>
            <a id="btnDownloadVariationOrderReport" href="{{ route('variationOrder.report.download', array($project->id)) }}" target="_self" class="btn btn-block btn-md btn-info">
                <i class="fa fa-download"></i>
                {{ trans('requestForVariation.variationOrderReport') }}
            </a>
        </li>
        @endif
        <li>
            <a id="btnPrintAllRfvs" href="{{ route('requestForVariation.all.print', array($project->id)) }}" target="_blank" class="btn btn-block btn-md btn-success">
                <i class="fa fa-print"></i>
                {{ trans('requestForVariation.printAllRfvs') }}
            </a>
        </li>
    </ul>
</div>