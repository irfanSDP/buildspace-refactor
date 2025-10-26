<div class="row">
    <section class="col col-xs-4 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.callingRfpDate') }}:</dt>
            <dd>{{{ $consultantManagementContract->getContractTimeZoneTime($callingRfp->calling_rfp_date) }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </section>
    <section class="col col-xs-4 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.closingRfpDate') }}:</dt>
            <dd>{{{ $consultantManagementContract->getContractTimeZoneTime($callingRfp->closing_rfp_date) }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </section>
    <section class="col col-xs-4 col-md-4 col-lg-4"></section>
</div>
<div class="row">
    <section class="col col-xs-4 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.costType') }}:</dt>
            <dd>{{{$vendorCategoryRfp->getCostTypeText()}}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </section>
    <section class="col col-xs-4 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('general.proposedFee') }}} ({{{$currencyCode}}}):</dt>
            <dd>{{{number_format($listOfConsultant->proposed_fee, 2, '.', ',')}}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('vendorManagement.remarks') }}}:</dt>
            <dd><div class="well">{{ nl2br($listOfConsultant->remarks) }}</div></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </section>
</div>
<hr class="simple">
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark"><i class="fa fa-users"></i> Consultant(s)</h1>
        <div id="selected_consultants-table"></div>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <div class="pull-right">
        @if(Confide::user()->isConsultantManagementCallingRfpEditor($consultantManagementContract) && $callingRfp->consultantManagementRfpRevision->isLatestRevision() && $callingRfp->status == PCK\ConsultantManagement\ConsultantManagementCallingRfp::STATUS_APPROVED)
            {{ HTML::decode(link_to_route('consultant.management.consultant.rfp.interview.index', '<i class="fa fa-comments"></i>&nbsp;'.trans('general.consultantInterview'), [$vendorCategoryRfp->id, $callingRfp->id], ['class' => 'btn btn-warning'])) }}

            @if(!$vendorCategoryRfp->approvalDocument or $vendorCategoryRfp->approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
            {{ HTML::decode(link_to_route('consultant.management.calling.rfp.extend.show', '<i class="fa fa-clock"></i>&nbsp;'.trans('forms.extend'), [$vendorCategoryRfp->id, $callingRfp->id], ['class' => 'btn btn-success'])) }}
            @endif

            {{ Form::button('<i class="fa fa-user-tie"></i> '.trans('openTenderAwardRecommendation.viewVerifierLogs'), ['class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#verifier_logs-modal']) }}
            {{ link_to_route('consultant.management.calling.rfp.index', trans('forms.back'), [$vendorCategoryRfp->id], ['class' => 'btn btn-default']) }}
        @else
            {{ Form::button('<i class="fa fa-user-tie"></i> '.trans('openTenderAwardRecommendation.viewVerifierLogs'), ['class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#verifier_logs-modal']) }}
            {{ link_to_route('consultant.management.calling.rfp.index', trans('forms.back'), [$vendorCategoryRfp->id], ['class' => 'btn btn-default']) }}
        @endif
        </div>
    </section>
</div>
