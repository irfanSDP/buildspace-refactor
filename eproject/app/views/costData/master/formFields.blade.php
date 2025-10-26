<fieldset>
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('general.name') }}} <span class="required">*</span>:</label>
            <label class="textarea {{{ $errors->has('name') ? 'state-error' : null }}}">
                {{ Form::text('name', Input::old('name'), array('required' => 'required', 'class' => 'form-control padded-less-left', 'autofocus' => 'autofocus')) }}
            </label>
            {{ $errors->first('name', '<em class="invalid">:message</em>') }}
        </section>
    </div>
</fieldset>