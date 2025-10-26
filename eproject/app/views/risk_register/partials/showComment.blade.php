<div class="row">
    <section class="col col-xs-12 col-md-9 col-lg-9">
        <label class="label">{{ trans('riskRegister.postedBy') }} :</label>
        {{{ $message->composer->name }}}
        <br/>
        ({{{ $message->composer->getProjectCompanyName($project, $message->created_at) }}})
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label">{{ trans('riskRegister.postedAt') }} :</label>
        {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($message->created_at))->format(\Config::get('dates.created_and_updated_at_formatting')) }}}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label"><strong>{{ trans('riskRegister.comment') }}</strong> :</label>
        <span class="text-danger">
            {{{ $message->content }}}
        </span>
    </section>
</div>
@include('risk_register.partials.attachments')
@include('request_for_information.partials.verifierInfo')