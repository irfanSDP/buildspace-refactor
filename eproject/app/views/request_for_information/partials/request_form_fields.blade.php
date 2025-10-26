<div class="row">
    <section class="col col-xs-12 col-lg-12 col-md-12">
        <label class="label">{{{ trans('requestForInformation.question') }}} <span class="required">*</span>:</label>
        <label class="textarea {{{ $errors->has('content') ? 'state-error' : null }}}">
            {{ Form::textArea('content', Input::old('content'), array('required' => 'required', 'rows' => 3, 'autofocus' => true)) }}
        </label>
        {{ $errors->first('content', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-6 col-lg-6">
        @include('form_partials.select_contract_groups')
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
    </section>
    <section class="col col-xs-12 col-md-3 col-lg-3">
        <label class="label">{{ trans('requestForInformation.replyDeadline') }} <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('reply_deadline') ? 'state-error' : null }}}">
            <input type="date" name="reply_deadline" value="{{ Input::old('reply_deadline') ?? \Carbon\Carbon::now($project->timezone)->addDays(7)->format('Y-m-d') }}">
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
        @include('verifiers.select_verifiers')
    </section>
</div>