<form class="smart-form">
    <fieldset class="bg-grey-e">
        <button type="button" class="btn btn-warning btn-xs pull-right" data-action="expandToggle" data-target="inspection-{{{ $inspection->id }}}">
            <i class="fa fa-minus"></i>
        </button>
        <div data-type="expandable" data-id="inspection-{{{ $inspection->id }}}">
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('requestForInspection.comments') }}} :</label>
                    {{{ $inspection->comments }}}
                </section>
            </div>
            @if($inspection->attachments->count() > 0)
                @include('request_for_inspection.partials.attachments', array('attachments' => $inspection->attachments))
            @endif
            <div class="well padded rounded-less">
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('requestForInspection.decision') }}} :</label>
                    @if($inspection->status == \PCK\RequestForInspection\RequestForInspectionInspection::STATUS_PASSED)
                        <strong>
                            <span class="text-success">
                                {{ trans('requestForInspection.inspectionPassed') }}
                            </span>
                        </strong>
                    @endif
                    @if($inspection->status == \PCK\RequestForInspection\RequestForInspectionInspection::STATUS_REMEDY_WITH_RE_INSPECTION)
                        <span class="text-danger">
                            {{ trans('requestForInspection.remedyAndReInspectionRequired') }}
                        </span>
                    @endif
                    @if($inspection->status == \PCK\RequestForInspection\RequestForInspectionInspection::STATUS_REMEDY_WITHOUT_RE_INSPECTION)
                        <span class="text-danger">
                        {{ trans('requestForInspection.remedyAndReInspectionNotRequired') }}
                        </span>
                    @endif
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('requestForInspection.remarks') }}} :</label>
                    <span class="text-warning">
                        {{{ $inspection->remarks }}}
                    </span>
                </section>
            </div>
            </div>
            <br/>
            <div class="row">
                <section class="col col-xs-12 col-md-9 col-lg-9">
                    <label class="label">{{{ trans('requestForInspection.inspectedBy') }}} :</label>
                    {{{ $inspection->createdBy->name }}}
                    <br/>
                    ({{{ $inspection->createdBy->getProjectCompanyName($project, $inspection->created_at) }}})
                </section>
                <section class="col col-xs-12 col-md-3 col-lg-3">
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-md-9 col-lg-9">
                    <label class="label">{{{ trans('requestForInspection.inspectedAt') }}} :</label>
                    {{{ $project->getProjectTimeZoneTime($inspection->inspected_at) }}}
                </section>
                <section class="col col-xs-12 col-md-3 col-lg-3">
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-md-9 col-lg-9">
                </section>
                <section class="col col-xs-12 col-md-3 col-lg-3">
                    <label class="label">{{{ trans('forms.submittedAt') }}} :</label>
                    {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime(\PCK\Verifier\Verifier::verifiedAt($inspection)))->format(\Config::get('dates.created_and_updated_at_formatting')) }}}
                </section>
            </div>
            @include('request_for_inspection.partials.verifierInfo', array('object' => $inspection))
        </div>
    </fieldset>
</form>
@if(\PCK\Verifier\Verifier::isCurrentVerifier($currentUser, $inspection))
    <div data-type="expandable" data-id="inspection-{{{ $inspection->id }}}">
        {{ Form::open(array('route' => array('verify', $inspection->id), 'class'=>'smart-form')) }}
        <input type="hidden" name="class" value="{{{ get_class($inspection) }}}"/>
        <footer>
            {{ Form::submit(trans('forms.approve'), array('name' => 'approve', 'class' => 'btn btn-primary')) }}
            {{ Form::submit(trans('forms.reject'), array('name' => 'reject', 'class' => 'btn btn-danger')) }}
        </footer>
        {{ Form::close() }}
    </div>
@endif