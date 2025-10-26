<form class="smart-form">
    <fieldset>
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <label class="label">{{{ trans('requestForInspection.reference') }}} :</label>
                {{{ $requestForInspection->reference }}}
            </section>
        </div>
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <label class="label">{{{ trans('requestForInspection.subject') }}} :</label>
                {{{ $requestForInspection->subject }}}
            </section>
        </div>
    </fieldset>
    <fieldset>
        <button type="button" class="btn btn-warning btn-xs pull-right" data-action="expandToggle" data-target="request-{{{ $requestForInspection->id }}}">
            <i class="fa fa-minus"></i>
        </button>
        <div data-type="expandable" data-id="request-{{{ $requestForInspection->id }}}">
            <div class="row">
                <section class="col col-xs-12 col-md-9 col-lg-9">
                    <label class="label">{{{ trans('requestForInspection.requestedBy') }}} :</label>
                    {{{ $requestForInspection->createdBy->name }}}
                    <br/>
                    ({{{ $requestForInspection->createdBy->getProjectCompanyName($project, $requestForInspection->created_at) }}})
                </section>
                <section class="col col-xs-12 col-md-3 col-lg-3">
                    <label class="label">{{{ trans('requestForInspection.requestedAt') }}} :</label>
                    {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime(\PCK\Verifier\Verifier::verifiedAt($requestForInspection)))->format(\Config::get('dates.created_and_updated_at_formatting')) }}}
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('requestForInspection.inspectionReference') }}} :</label>
                    {{{ $requestForInspection->subject }}}
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('requestForInspection.descriptionOfWork') }}} :</label>
                    {{{ $requestForInspection->description }}}
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('requestForInspection.location') }}} :</label>
                    {{{ $requestForInspection->location }}}
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('requestForInspection.typeOfWork') }}} :</label>
                    {{{ $requestForInspection->works }}}
                </section>
            </div>
            @if($requestForInspection->attachments->count() > 0)
                @include('request_for_inspection.partials.attachments', array('attachments' => $requestForInspection->attachments))
            @endif
            <div class="row">
                <section class="col col-xs-12 col-md-9 col-lg-9">
                </section>
                <section class="col col-xs-12 col-md-3 col-lg-3">
                    <label class="label">{{{ trans('requestForInspection.readyDate') }}} :</label>
                    {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($requestForInspection->ready_date))->format(\Config::get('dates.created_and_updated_at_formatting')) }}}
                </section>
            </div>
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('requestForInspection.ballInCourt') }}} :</label>
                    @foreach($requestForInspection->directedTo as $record)
                        {{{ $project->getRoleName($record->target->group) }}}
                        @if(($company = $project->getCompanyByGroup($record->target->group)))
                            ({{{ $company->getNameInProject($project) }}})
                        @endif
                        <br/>
                    @endforeach
                </section>
            </div>
            @include('request_for_inspection.partials.verifierInfo', array('object' => $requestForInspection))
        </div>
    </fieldset>
</form>
@if(\PCK\Verifier\Verifier::isCurrentVerifier($currentUser, $requestForInspection))
    <div data-type="expandable" data-id="request-{{{ $requestForInspection->id }}}">
        {{ Form::open(array('route' => array('verify', $requestForInspection->id), 'class'=>'smart-form')) }}
        <input type="hidden" name="class" value="{{{ get_class($requestForInspection) }}}"/>
        <footer>
            {{ Form::submit(trans('forms.approve'), array('name' => 'approve', 'class' => 'btn btn-primary')) }}
            {{ Form::submit(trans('forms.reject'), array('name' => 'reject', 'class' => 'btn btn-danger')) }}
        </footer>
        {{ Form::close() }}
    </div>
@endif