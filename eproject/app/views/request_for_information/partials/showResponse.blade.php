<div class="row">
    <section class="col col-xs-12 col-md-9 col-lg-9">
        <label class="label">{{{ trans('requestForInformation.answeredBy') }}} :</label>
        {{{ $message->composer->name }}}
        <br/>
        ({{{ $message->composer->getProjectCompanyName($project, $message->created_at) }}})
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label">{{{ trans('requestForInformation.answeredAt') }}} :</label>
        {{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($message->created_at))->format(\Config::get('dates.created_and_updated_at_formatting')) }}}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label"><strong>{{{ trans('requestForInformation.response') }}}</strong> :</label>
        <span class="text-success">
            {{{ $message->content }}}
        </span>
    </section>
</div>
@if($message->attachments->count() > 0)
    @include('request_for_information.partials.attachments')
@endif
<div class="row">
    <section class="col col-xs-12 col-md-6 col-lg-6">
        <label class="label"><strong>{{{ trans('requestForInformation.costImpact') }}}</strong> :</label>
        {{{ $message->cost_impact ? trans('forms.yes') : trans('forms.no') }}}
    </section>
    <section class="col col-xs-12 col-md-6 col-lg-6">
        <label class="label"><strong>{{{ trans('requestForInformation.scheduleImpact') }}}</strong> :</label>
        {{{ $message->schedule_impact ? trans('forms.yes') : trans('forms.no') }}}
    </section>
</div>

@include('request_for_information.partials.verifierInfo')