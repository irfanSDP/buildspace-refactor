<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark"><i class="fa fa-comments"></i> {{{ trans('tenders.tenderInterview') }}}</h1>
        <div id="rfp_interview-table">
    </section>
</div>
@if($vendorCategoryRfp->rfpInterviews->count())
    @foreach($vendorCategoryRfp->rfpInterviews as $rfpInterview)
    <div class="well">
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <dl class="dl-horizontal no-margin">
                    <dt>{{ trans('general.title') }}:</dt>
                    <dd>{{{ $rfpInterview->title }}}</dd>
                    <dt>&nbsp;</dt>
                    <dd>&nbsp;</dd>
                </dl>
            </section>
        </div>
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <dl class="dl-horizontal no-margin">
                    <dt>{{ trans('general.details') }}:</dt>
                    <dd><div class="well">{{ nl2br($rfpInterview->details) }}</div></dd>
                    <dt>&nbsp;</dt>
                    <dd>&nbsp;</dd>
                </dl>
            </section>
        </div>
        <div class="row">
            <section class="col col-xs-4 col-md-4 col-lg-4">
                <dl class="dl-horizontal no-margin">
                    <dt>{{ trans('general.date') }}:</dt>
                    <dd>{{{ date('d-M-Y', strtotime($rfpInterview->interview_date)) }}}</dd>
                    <dt>&nbsp;</dt>
                    <dd>&nbsp;</dd>
                </dl>
            </section>
        </div>
        <div class="row">
            <section class="col col-xs-4 col-md-4 col-lg-4">
                <dl class="dl-horizontal no-margin">
                    <dt>{{ trans('general.createdBy') }}:</dt>
                    <dd>{{{ $rfpInterview->createdBy->name }}}</dd>
                    <dt>&nbsp;</dt>
                    <dd>&nbsp;</dd>
                </dl>
            </section>
            <section class="col col-xs-4 col-md-4 col-lg-4">
                <dl class="dl-horizontal no-margin">
                    <dt>{{ trans('general.updatedBy') }}:</dt>
                    <dd>{{{ $rfpInterview->updatedBy->name }}}</dd>
                    <dt>&nbsp;</dt>
                    <dd>&nbsp;</dd>
                </dl>
            </section>
        </div>

        <div id="rfp_interview_{{$rfpInterview->id}}-consultants">
            <hr class="simple">
            <div class="row">
                <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <h5><i class="fa fa-users"></i> Consultant(s)</h5>
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div id="consultant_list-{{$rfpInterview->id}}-table"></div>
                </section>
            </div>
        </div>
    </div>
    <hr class="simple">
    @endforeach
@else
<div class="well">
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <div class="alert alert-warning text-center fade in">
                <i class="fa-fw fa fa-exclamation-triangle"></i>
                There is <strong>no RFP Interview</strong> initiated.
            </div>
        </section>
    </div>
</div>
<hr class="simple">
@endif
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark"><i class="fa fa-user-tie"></i> {{{ trans('verifiers.verifierLogs') }}} (Latest Revision)</h1>
        <div id="calling_rfp-verifier_logs-table">
    </section>
</div>