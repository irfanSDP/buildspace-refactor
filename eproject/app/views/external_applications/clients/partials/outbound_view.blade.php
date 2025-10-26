<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header class="ps-2 pt-2 pb-14">
                <label class="input" style="min-width:300px;">
                    <select class="select2 fill-horizontal" id="client_outbound_auth_type-select" placeholder="Please Select Auth Type">
                        <option value=""></option>
                        @foreach($authTypes as $value => $text)
                        <option value="{{ $value }}" @if($selectedAuthType && $selectedAuthType == $value) selected @endif>{{ $text }}</option>
                        @endforeach
                    </select>
                </label>
            </header>
            <div>
                <div class="widget-body">
                    @if($selectedAuthType)
                    {{ Form::open(['route' => ['api.v2.clients.outbound.auth.store', $client->id], 'id' => 'outbound_auth-form', 'method' => 'post', 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">URL <span class="required">*</span>:</label>
                            <label class="input {{{ ($errors->has('url')) ? 'state-error' : null }}}">
                            {{ Form::text('url', Input::old('url', isset($outboundAuth) ? $outboundAuth->url : ''), ['id'=>'url-input', 'required'=>'required', 'autofocus' => 'autofocus']) }}
                            </label>
                            {{ $errors->first('url', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <div class="row">
                        <?php
                        $headerParams = [];
                        if(isset($authOptions->header_params))
                        {
                            $params = json_decode($authOptions->header_params, true);
                            foreach($params as $key => $value)
                            {
                                $headerParams[] = $key.':'.$value; 
                            }
                        }
                        ?>
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">Header Params (comma separated)<span class="required">*</span>:</label>
                            <label class="input {{{ ($errors->has('header_params')) ? 'state-error' : null }}}">
                            {{ Form::text('header_params', Input::old('header_params', !empty($headerParams) ? implode(',', $headerParams) : 'Content-Type: application/json'), ['id'=>'header_params-input', 'required'=>'required']) }}
                            </label>
                            {{ $errors->first('header_params', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                        @if($selectedAuthType == \PCK\ExternalApplication\OutboundAuthorization::TYPE_BEARER_TOKEN)
                            @include('external_applications.clients.partials.outbound_auth.bearer_token_form')
                        @elseif($selectedAuthType == \PCK\ExternalApplication\OutboundAuthorization::TYPE_OAUTH_TWO)
                            @include('external_applications.clients.partials.outbound_auth.oauth_two_form')
                        @endif
                    <footer>
                    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::hidden('type', $selectedAuthType) }}
                    {{ Form::close() }}
                    @else
                    <div class="padding-10">
                        <div class="alert alert-warning text-center">Please select Authorization Type.</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>