<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        <li>
            <a href="{{ $attachmentsIndexRoute }}" class="btn btn-block btn-md btn-primary">
                <i class="fa fa-paperclip"></i> {{ trans('openTenderAwardRecommendation.attachments') }}
            </a>
        </li>
        <li class="divider"></li>
        <li>
            <a href="{{ $tenderAnalysisIndexRoute }}" class="btn btn-block btn-md btn-primary">
                <i class="fa fa-table"></i></i> {{ trans('openTenderAwardRecommendation.tenderAnalysisTable') }}
            </a>
        </li>
        <li class="divider"></li>
    </ul>
</div>