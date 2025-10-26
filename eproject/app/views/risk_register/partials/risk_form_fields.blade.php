<div class="row">
    <section class="col col-xs-12 col-lg-12 col-md-12">
        <label class="label">{{{ trans('riskRegister.description') }}} <span class="required">*</span>:</label>
        <label class="textarea {{{ $errors->has('content') ? 'state-error' : null }}}">
            {{ Form::textArea('content', Input::old('content'), array('required' => 'required', 'rows' => 3, 'autofocus' => true)) }}
        </label>
        {{ $errors->first('content', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-lg-3 col-md-3">
        <label class="label">{{{ trans('riskRegister.probability') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('probability') ? 'state-error' : null }}}">
            {{ Form::number('probability', Input::old('probability'), array('required' => 'required', 'max' => 100, 'min' => 0)) }}
        </label>
        {{ $errors->first('probability', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-lg-3 col-md-3">
        <label class="label">{{{ trans('riskRegister.impact') }}} <span class="required">*</span>:</label>
        <label class="select {{{ $errors->has('impact') ? 'state-error' : null }}}">
            {{ Form::select('impact', $arbitraryRatings, Input::old('impact'), array('required' => 'required')) }}
        </label>
        {{ $errors->first('impact', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-lg-3 col-md-3">
        <label class="label">{{{ trans('riskRegister.detectability') }}} <span class="required">*</span>:</label>
        <label class="select {{{ $errors->has('detectability') ? 'state-error' : null }}}">
            {{ Form::select('detectability', $arbitraryRatings, Input::old('detectability'), array('required' => 'required')) }}
        </label>
        {{ $errors->first('detectability', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-lg-3 col-md-3">
        <label class="label">{{{ trans('riskRegister.importance') }}} <span class="required">*</span>:</label>
        <label class="select {{{ $errors->has('importance') ? 'state-error' : null }}}">
            {{ Form::select('importance', $arbitraryRatings, Input::old('importance'), array('required' => 'required')) }}
        </label>
        {{ $errors->first('importance', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-lg-12 col-md-12">
        <label class="label">{{{ trans('riskRegister.category') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('category') ? 'state-error' : null }}}">
            {{ Form::text('category', Input::old('category'), array('required' => 'required')) }}
        </label>
        {{ $errors->first('category', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-lg-12 col-md-12">
        <label class="label">{{{ trans('riskRegister.triggerEvent') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('trigger_event') ? 'state-error' : null }}}">
            {{ Form::text('trigger_event', Input::old('trigger_event'), array('required' => 'required')) }}
        </label>
        {{ $errors->first('trigger_event', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-lg-12 col-md-12">
        <label class="label">{{{ trans('riskRegister.riskResponse') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('risk_response') ? 'state-error' : null }}}">
            {{ Form::text('risk_response', Input::old('risk_response'), array('required' => 'required')) }}
        </label>
        {{ $errors->first('risk_response', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-lg-12 col-md-12">
        <label class="label">{{{ trans('riskRegister.contingencyPlan') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('contingency_plan') ? 'state-error' : null }}}">
            {{ Form::text('contingency_plan', Input::old('contingency_plan'), array('required' => 'required')) }}
        </label>
        {{ $errors->first('contingency_plan', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-lg-3 col-md-3">
        <label class="label">{{{ trans('riskRegister.status') }}} <span class="required">*</span>:</label>
        <label class="select {{{ $errors->has('status') ? 'state-error' : null }}}">
            {{ Form::select('status', $statusList, Input::old('status'), array('required' => 'required')) }}
        </label>
        {{ $errors->first('status', '<em class="invalid">:message</em>') }}
    </section>
</div>

<div class="row">
    <section class="col col-xs-12 col-md-6 col-lg-6">
        @include('form_partials.select_contract_groups')
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label">{{ trans('riskRegister.dateToReview') }} <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('reply_deadline') ? 'state-error' : null }}}">
            {{ Form::text('reply_deadline', Input::old('reply_deadline') ? Input::old('reply_deadline') : \Carbon\Carbon::now($project->timezone)->addDays(7)->format('d-M-Y'), array('class' => 'datetimepicker')) }}
        </label>
        {{ $errors->first('reply_deadline', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">{{{ trans('requestForInformation.attachments') }}}:</label>

        @include('file_uploads.partials.upload_file_modal')
    </section>
</div>

<div class="row">
    <section class="col col-xs-4 col-md-4 col-lg-4">
        @include('verifiers.select_verifiers', array('modalId' => 'riskFormFieldsVerifierModal'))
    </section>
</div>