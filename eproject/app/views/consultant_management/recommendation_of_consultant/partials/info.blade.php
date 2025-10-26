<div class="row">
    <section class="col col-xs-4 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.callingRfpProposedDate') }}:</dt>
            <dd>{{{ $consultantManagementContract->getContractTimeZoneTime($recommendationOfConsultant->calling_rfp_proposed_date) }}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </section>
    <section class="col col-xs-4 col-md-4 col-lg-4">
        <dl class="dl-horizontal no-margin">
            <dt>{{ trans('general.closingRfpProposedDate') }}:</dt>
            <dd>{{{ $consultantManagementContract->getContractTimeZoneTime($recommendationOfConsultant->closing_rfp_proposed_date) }}}</dd>
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
            <dd>{{{number_format($recommendationOfConsultant->proposed_fee, 2, '.', ',')}}}</dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>{{{ trans('vendorManagement.remarks') }}}:</dt>
            <dd><div class="well">{{ nl2br($recommendationOfConsultant->remarks) }}</div></dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </section>
</div>
<hr class="simple">
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark"><i class="fa fa-users"></i> Proposed Consultant(s)</h1>
        <div id="selected_consultants-table"></div>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <div class="pull-right">
            {{ Form::button('<i class="fa fa-user-tie"></i> '.trans('openTenderAwardRecommendation.viewVerifierLogs'), ['class' => 'btn btn-info', 'data-toggle' => 'modal', 'data-target' => '#verifier_logs-modal']) }}
            {{ link_to_route('consultant.management.contracts.contract.show', trans('forms.back'), [$consultantManagementContract->id], ['class' => 'btn btn-default']) }}
        </div>
    </section>
</div>
