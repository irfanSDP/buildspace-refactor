<div class="row">
    <section class="col col-xs-12 col-md-9 col-lg-9">
        <label class="label">{{{ trans('requestForInformation.requestedBy') }}} :</label>
        {{{ $message->composer->name }}}
        <br/>
        ({{{ $message->composer->getProjectCompanyName($project, $message->created_at) }}})
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label">{{{ trans('requestForInformation.requestedAt') }}} :</label>
        {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($message->created_at))->format(\Config::get('dates.created_and_updated_at_formatting')) }}}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label"><strong>{{{ trans('requestForInformation.question') }}}</strong> :</label>
        <span class="text-danger">
            {{{ $message->content }}}
        </span>
    </section>
</div>
@if($message->attachments->count() > 0)
    @include('request_for_information.partials.attachments')
@endif
<div class="row">
    <section class="col col-xs-12 col-md-9 col-lg-9">
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label">{{{ trans('requestForInformation.replyDeadline') }}} :</label>
        {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($message->reply_deadline))->format(\Config::get('dates.created_and_updated_at_formatting')) }}}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">{{{ trans('requestForInformation.ballInCourt') }}} :</label>
        @foreach($message->directedTo as $record)
            {{{ $project->getRoleName($record->target->group) }}}
            @if(($company = $project->getCompanyByGroup($record->target->group)))
                ({{{ $company->getNameInProject($project) }}})
            @endif
            <br/>
        @endforeach
    </section>
</div>

@include('request_for_information.partials.verifierInfo')