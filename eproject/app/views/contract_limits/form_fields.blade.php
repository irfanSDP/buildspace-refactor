<fieldset>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('contractLimit.limit') }}} <span class="required">*</span>:</label>
            <label class="textarea {{{ $errors->has('limit') ? 'state-error' : null }}}">
                {{ Form::text('limit', Input::old('limit'), array('required' => 'required', 'class' => 'form-control text-indent', 'autofocus' => 'autofocus')) }}
            </label>
            {{ $errors->first('limit', '<em class="invalid">:message</em>') }}
        </section>
    </div>
</fieldset>