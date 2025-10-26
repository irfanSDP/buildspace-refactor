<div class="row">
    <section class="col col-xs-12 col-lg-12 col-md-12">
        <label class="label">{{ trans('riskRegister.comment') }} <span class="required">*</span>:</label>
        <label class="textarea {{{ $errors->has('content') ? 'state-error' : null }}}">
            {{ Form::textArea('content', Input::old('content'), array('required' => 'required', 'rows' => 3, 'autofocus' => true)) }}
        </label>
        {{ $errors->first('content', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">{{ trans('requestForInformation.attachments') }}:</label>

        @include('file_uploads.partials.upload_file_modal')
    </section>
</div>

<div class="row">
    <section class="col col-xs-4 col-md-4 col-lg-4">
        @include('verifiers.select_verifiers', array('modalId' => 'commentFormFieldsVerifierModal'))
    </section>
</div>