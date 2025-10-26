<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">Token <span class="required">*</span>:</label>
        <label class="input {{{ ($errors->has('token')) ? 'state-error' : null }}}">
        {{ Form::text('token', Input::old('token', isset($authOptions->token) ? $authOptions->token : ''), ['id'=>'token-input', 'required'=>'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $errors->first('token', '<em class="invalid">:message</em>') }}
    </section>
</div>