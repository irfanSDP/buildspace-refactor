<form class="smart-form">
    <fieldset>
        <button type="button" class="btn btn-warning btn-xs pull-right" data-action="expandToggle" data-target="reply-{{{ $inspection->reply->id }}}">
            <i class="fa fa-minus"></i>
        </button>
        <div data-type="expandable" data-id="reply-{{{ $inspection->reply->id }}}">
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('requestForInspection.comments') }}} :</label>
                    {{{ $inspection->reply->comments }}}
                </section>
            </div>
            @if($inspection->reply->attachments->count() > 0)
                @include('request_for_inspection.partials.attachments', array('attachments' => $inspection->reply->attachments))
            @endif
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('requestForInspection.repliedBy') }}} :</label>
                    {{{ $inspection->reply->createdBy->name }}}
                    <br/>
                    ({{{ $inspection->reply->createdBy->getProjectCompanyName($project, $inspection->reply->created_at) }}})
                </section>
            </div>
            @if($inspection->reply->completed_date)
                <div class="row">
                    <section class="col col-xs-12 col-md-9 col-lg-9">
                    </section>
                    <section class="col col-xs-12 col-md-3 col-lg-3">
                        <label class="label">{{{ trans('requestForInspection.completedDate') }}} :</label>
                        <strong>
                            <span class="text-success">
                                {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($inspection->reply->completed_date))->format(\Config::get('dates.created_and_updated_at_formatting')) }}}
                            </span>
                        </strong>
                    </section>
                </div>
            @else
                <div class="row">
                    <section class="col col-xs-12 col-md-9 col-lg-9">
                    </section>
                    <section class="col col-xs-12 col-md-3 col-lg-3">
                        <label class="label">{{{ trans('requestForInspection.readyDate') }}} :</label>
                        {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($inspection->reply->ready_date))->format(\Config::get('dates.created_and_updated_at_formatting')) }}}
                    </section>
                </div>
            @endif
            <div class="row">
                <section class="col col-xs-12 col-md-12 col-lg-12">
                    <label class="label">{{{ trans('requestForInspection.ballInCourt') }}} :</label>
                    @foreach($inspection->reply->directedTo as $record)
                        {{{ $project->getRoleName($record->target->group) }}}
                        @if(($company = $project->getCompanyByGroup($record->target->group)))
                            ({{{ $company->getNameInProject($project) }}})
                        @endif
                        <br/>
                    @endforeach
                </section>
            </div>
            @include('request_for_inspection.partials.verifierInfo', array('object' => $inspection->reply))
        </div>
    </fieldset>
</form>
@if(\PCK\Verifier\Verifier::isCurrentVerifier($currentUser, $inspection->reply))
    <div data-type="expandable" data-id="reply-{{{ $inspection->reply->id }}}">
        {{ Form::open(array('route' => array('verify', $inspection->reply->id), 'id' => 'verifierForm', 'class' => 'smart-form')) }}
            <footer class="bg-color-white">
                <input type="hidden" name="class" value="{{{ get_class($inspection->reply) }}}"/>
                {{ Form::submit(trans('forms.approve'), array('name' => 'approve', 'class' => 'btn btn-primary')) }}
                {{ Form::submit(trans('forms.reject'), array('name' => 'reject', 'class' => 'btn btn-danger')) }}
            </footer>
        {{ Form::close() }}
    </div>
@endif