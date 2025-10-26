{{ Form::open(['route' => ['api.v2.clients.module.outbound.store', $selectedModule->id], 'class' => 'smart-form', 'method' => 'post']) }}
<div class="row">
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">Outbound Status:</label>
        <label class="input {{{ ($errors->has('outbound_status')) ? 'state-error' : null }}}">
            <select class="select2 fill-horizontal" name="outbound_status" id="client_module_outbound_status-select" placeholder="Please Select Outbound Status">
                <option value=""></option>
                @foreach($outboundStatuses as $val => $txt)
                <option value="{{ $val }}" @if($selectedModule->outbound_status == $val) selected @endif>{{ $txt }}</option>
                @endforeach
            </select>
        </label>
        {{ $errors->first('outbound_status', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">Outbound URL Path:</label>
        <label class="input {{{ ($errors->has('outbound_url_path')) ? 'state-error' : null }}}">
        {{ Form::text('outbound_url_path', Input::old('outbound_url_path', isset($selectedModule->outbound_url_path) ? $selectedModule->outbound_url_path : ''), ['id'=>'outbound_url_path-input']) }}
        </label>
        {{ $errors->first('outbound_url_path', '<em class="invalid">:message</em>') }}
    </section>
    <section class="col col-xs-12 col-md-4 col-lg-4">
        <label class="label">Only Same Source:</label>
        <label class="checkbox">
            <input type="checkbox" name="outbound_only_same_source" @if(isset($selectedModule->outbound_only_same_source) && $selectedModule->outbound_only_same_source) checked @endif>
            <i></i>
        </label>
    </section>
</div>
<footer>
    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
</footer>
{{ Form::close() }}