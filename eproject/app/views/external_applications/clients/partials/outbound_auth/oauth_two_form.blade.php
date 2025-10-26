<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">Header Prefix <span class="required">*</span>:</label>
        <label class="input {{{ ($errors->has('token')) ? 'state-error' : null }}}">
        {{ Form::text('header_prefix', Input::old('header_prefix', isset($authOptions->header_prefix) ? $authOptions->header_prefix : 'Bearer'), ['id'=>'header_prefix-input', 'required'=>'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $errors->first('header_prefix', '<em class="invalid">:message</em>') }}
    </section>
</div>

<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">Access Token URL<span class="required">*</span>:</label>
        <label class="input {{{ ($errors->has('access_token_url')) ? 'state-error' : null }}}">
        {{ Form::text('access_token_url', Input::old('access_token_url', isset($authOptions->access_token_url) ? $authOptions->access_token_url : ''), ['id'=>'access_token_url-input', 'required'=>'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $errors->first('access_token_url', '<em class="invalid">:message</em>') }}
    </section>
</div>

<div class="row">
    <section class="col col-xs-12 col-md-6 col-lg-6">
        <label class="label">Client ID<span class="required">*</span>:</label>
        <label class="input {{{ ($errors->has('client_id')) ? 'state-error' : null }}}">
        {{ Form::text('client_id', Input::old('client_id', isset($authOptions->client_id) ? $authOptions->client_id : ''), ['id'=>'client_id-input', 'required'=>'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $errors->first('client_id', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-md-6 col-lg-6">
        <label class="label">Client Secret<span class="required">*</span>:</label>
        <label class="input {{{ ($errors->has('client_secret')) ? 'state-error' : null }}}">
        {{ Form::text('client_secret', Input::old('client_secret', isset($authOptions->client_secret) ? $authOptions->client_secret : ''), ['id'=>'client_secret-input', 'required'=>'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $errors->first('client_secret', '<em class="invalid">:message</em>') }}
    </section>
</div>

<div class="row">
    <section class="col col-xs-12 col-md-6 col-lg-6">
        <label class="label">Scope:</label>
        <label class="input {{{ ($errors->has('scope')) ? 'state-error' : null }}}">
        {{ Form::text('scope', Input::old('scope', isset($authOptions->scope) ? $authOptions->scope : ''), ['id'=>'scope-input', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $errors->first('scope', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-md-6 col-lg-6">
        <label class="label">Grant Type<span class="required">*</span>:</label>
        <label data-field="form_error_label-grant_type" class="input {{{ ($errors->has('grant_type')) ? 'state-error' : null }}}">
            <select class="select2 fill-horizontal" name="grant_type" id="outbound_auth_grant_type-select" placeholder="Please Select Grant Type">
                <option value=""></option>
                @foreach($grantTypes as $val => $txt)
                <option value="{{ $val }}" @if(isset($authOptions->grant_type) && $authOptions->grant_type == $val) selected @endif>{{ $txt }}</option>
                @endforeach
            </select>
        </label>
        {{ $errors->first('grant_type', '<em class="invalid">:message</em>') }}
    </section>
</div>