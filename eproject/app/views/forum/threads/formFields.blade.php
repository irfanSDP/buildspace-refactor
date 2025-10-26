<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">{{{ trans('forum.title') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('title') ? 'state-error' : null }}}">
            {{ Form::text('title', Input::old('title'), array('required' => 'required', 'autofocus' => 'autofocus', 'maxlength' => 200, 'class' => 'form-control text-indent')) }}
        </label>
        {{ $errors->first('title', '<em class="invalid">:message</em>') }}
    </section>
</div>