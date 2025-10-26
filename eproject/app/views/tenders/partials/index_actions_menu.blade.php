<?php $showExportLotInformationButton = $showExportLotInformationButton ?? false; ?>
<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <button data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:;"> {{ trans('general.actions') }} <span class="caret"></span> </button>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        @if ($showExportLotInformationButton)
            <li>
                <a href="{{{ $exportLotInformationURL }}}" target="_self" class="btn btn-block btn-md btn-success"><i class="fa fa-file-excel" aria-hidden="true"></i> {{ trans('tenders.exportListOfTenderersInfo') }}</a>
            </li>
        @endif
    </ul>
</div>