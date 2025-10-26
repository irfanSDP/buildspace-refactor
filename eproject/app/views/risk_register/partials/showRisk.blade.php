<div class="row">
    <section class="col col-xs-12 col-md-9 col-lg-9">
        <label class="label">{{{ trans('riskRegister.issuedBy') }}} :</label>
        {{{ $message->composer->name }}}
        <br/>
        ({{{ $message->composer->getProjectCompanyName($project, $message->created_at) }}})
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label">{{{ trans('riskRegister.issuedAt') }}} :</label>
        {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($message->created_at))->format(\Config::get('dates.created_and_updated_at_formatting')) }}}
    </section>
</div>
@if($message->created_at != $message->updated_at)
    <div class="row">
        <section class="col col-xs-12 col-md-9 col-lg-9">
        </section>
        <section class="col col-xs-12 col-md-3 col-lg-3">
            <label class="label"><strong>{{{ trans('riskRegister.updatedAt') }}}</strong> :</label>
            {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($message->updated_at))->format(\Config::get('dates.created_and_updated_at_formatting')) }}}
        </section>
    </div>
@endif
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label"><strong>{{{ trans('riskRegister.description') }}}</strong> :</label>
        <span class="text-danger">
            {{{ $message->content }}}
        </span>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label"><strong>{{{ trans('riskRegister.probability') }}}</strong> :</label>
        <span class="text-danger">
            {{{ $message->probability }}} %
        </span>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label"><strong>{{{ trans('riskRegister.impact') }}}</strong> :</label>
        <span class="text-danger">
            {{{ \PCK\RiskRegister\RiskRegisterMessage::getRatingText($message->impact) }}}
        </span>
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label"><strong>{{{ trans('riskRegister.detectability') }}}</strong> :</label>
        <span class="text-danger">
            {{{ \PCK\RiskRegister\RiskRegisterMessage::getRatingText($message->detectability) }}}
        </span>
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label"><strong>{{{ trans('riskRegister.importance') }}}</strong> :</label>
        <span class="text-danger">
            {{{ \PCK\RiskRegister\RiskRegisterMessage::getRatingText($message->importance) }}}
        </span>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label"><strong>{{{ trans('riskRegister.category') }}}</strong> :</label>
        <span class="text-danger">
            {{{ $message->category }}}
        </span>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label"><strong>{{{ trans('riskRegister.triggerEvent') }}}</strong> :</label>
        <span class="text-danger">
            {{{ $message->trigger_event }}}
        </span>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label"><strong>{{{ trans('riskRegister.riskResponse') }}}</strong> :</label>
        <span class="text-danger">
            {{{ $message->risk_response }}}
        </span>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label"><strong>{{{ trans('riskRegister.contingencyPlan') }}}</strong> :</label>
        <span class="text-danger">
            {{{ $message->contingency_plan }}}
        </span>
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label"><strong>{{{ trans('riskRegister.status') }}}</strong> :</label>
        <span class="text-danger">
            {{{ \PCK\RiskRegister\RiskRegisterMessage::getStatusText($message->status) }}}
        </span>
    </section>
</div>
@include('risk_register.partials.attachments')
<div class="row">
    <section class="col col-xs-12 col-md-9 col-lg-9">
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label">{{{ trans('riskRegister.reviewDeadline') }}} :</label>
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