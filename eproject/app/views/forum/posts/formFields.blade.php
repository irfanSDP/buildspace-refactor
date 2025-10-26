<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">
            {{{ trans('forum.post') }}} <span class="required">*</span>:
        </label>
        <label class="textarea {{{ $errors->has('content') ? 'state-error' : null }}}">
            {{ Form::textarea('content', Input::old('content') ?? (isset($post) ? $post->getContent() : ''), array('required' => 'required', 'rows' => '1', 'autofocus' => 'autofocus')) }}
        </label>
        {{ $errors->first('content', '<em class="invalid">:message</em>') }}
    </section>
</div>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <div class="well">
            <label class="label">{{{ trans('forms.attachments') }}}:</label>

            @include('file_uploads.partials.upload_file_modal')
        </div>
    </section>
</div>