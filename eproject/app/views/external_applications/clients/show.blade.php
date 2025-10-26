@extends('layout.main')

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), []) }}</li>
    <li>{{ link_to_route('api.v2.clients.index', 'API V2', []) }}</li>
    <li>{{{ str_limit($client->name, 50) }}}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-6 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-network-wired"></i> API V2
        </h1>
    </div>
    <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3 mb-4">
        <a href="{{ route('api.v2.clients.index') }}" class="btn btn-default btn-md pull-right header-btn ms-2">
            {{{ trans('general.back') }}}
        </a>
        <button type="button" id="ext_app_client_edit-btn" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-edit"></i> {{{ trans('general.edit') }}}
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget" style="padding-bottom:0!important;">
            <header>
                <h2> {{{ trans('general.clients') }}} </h2>
            </header>
            <div>
                <div class="widget-body" style="padding-bottom:0!important;">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('general.name') }}:</dt>
                                <dd id="client_name-txt">{{ $client->name }}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>Access Token:</dt>
                                <dd id="client_token-txt">
                                    {{ $client->token }} &nbsp;
                                    <button type="button" id="copy_access_token-btn" class="btn btn-primary btn-xs">
                                        {{{ trans('general.copy') }}}
                                    </button>
                                    {{ Form::hidden('token', $client->token, ['id'=>'client_token-val']) }}
                                </dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('general.remarks') }}:</dt>
                                <dd><div class="well" id="client_remarks-txt">{{ nl2br($client->remarks) }}</div></dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row status">
                        <ul class="links">
                            <li class="@if($tabView == 'module') active @endif">
                            {{ link_to_route('api.v2.clients.show', 'Module Settings', $client->id) }}
                            </li>
                            <li class="@if($tabView == 'outbound') active @endif">
                            {{ link_to_route('api.v2.clients.outbound.show', 'Outbound Settings', $client->id) }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($tabView == 'module')
@include('external_applications.clients.partials.module_view')
@elseif($tabView == 'outbound')
@include('external_applications.clients.partials.outbound_view')
@endif

@include('external_applications.clients.partials.form', ['formPrefix'=>'client_edit'])

@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
<script src="<?php echo asset('js/clipboard/clipboard.min.js'); ?>"></script>
@include('external_applications.clients.partials.form_javascript', ['formPrefix'=>'client_edit'])
<script type="text/javascript">
$(document).ready(function () {
    $('#client_edit-form').on('submit', function(e){
        e.preventDefault();
        submitForm($(this)[0], function(data){
            location.href = "{{ route('api.v2.clients.show', [$client->id]) }}";
        });
    });

    const tokenEl = document.getElementById('client_token-val');
    const clipboard = new ClipboardJS(document.getElementById('copy_access_token-btn'), {
        container: document.getElementById('client_token-txt'),
        target: tokenEl,
        text: function() {
            return '{{ $client->token }}'
        }
    }).on('success', function(e) {
        const copyBtn = document.getElementById('copy_access_token-btn');
        copyBtn.innerHTML = 'Copied';
    });

    @if($tabView == 'module')
        $('#client_add_module-select').on('change', function(e){
            if($(this).val()){
                var url = "{{ route('api.v2.clients.module.show', [$client->id, ':id'])}}";
                    url = url.replace(':id', $(this).val());
                window.location.replace(url);
            }else{
                window.location.replace("{{ route('api.v2.clients.show', [$client->id])}}");
            }
        });

        @if($selectedModule)
        const clientTable = new Tabulator('#created_records-table', {
            height:520,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL: "{{ route('api.v2.clients.module.records', [$selectedModule->id]) }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, formatter:"textarea"},
                {title:"Internal Identifier", field:"internal_id", width:140, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"External Identifier", field:"external_id", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.createdAt') }}", field:"created_at", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
            ]
        });

        $('#created_records_reload-btn').on('click', function(e){
            clientTable.replaceData();
        });

        const outboundLogTable = new Tabulator('#module_outbound_logs-table', {
            height:520,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL: "{{ route('api.v2.clients.module.outbound.logs', [$selectedModule->id]) }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"Data", field:"data", minWidth: 320, hozAlign:"left", headerSort:false, formatter:"textarea"},
                {title:"Response Content", field:"response_contents", width:300, hozAlign:'left', headerSort:false, formatter:"textarea"},
                {title:"Status Code", field:"status_code", width:100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.createdAt') }}", field:"created_at", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"Resync", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        var html = '<form method="POST" action="{{ route("api.v2.clients.module.outbound.logs.resync", [$selectedModule->id]) }}">'
                            +'<input name="id" type="hidden" value="'+rowData.id+'">'
                            +'<input name="_token" type="hidden" value="{{{ csrf_token() }}}">'
                            +'<button type="submit" class="btn btn-xs btn-success"><i class="fa fa-sync"></i></button>'
                            +'</form>';
                        return html;
                    }
                }]
            }}
            ]
        });

        $('#module_outbound_logs_reload-btn').on('click', function(e){
            outboundLogTable.replaceData();
        });
        @endif
    @elseif($tabView == 'outbound')
        $('#client_outbound_auth_type-select').on('change', function(e){
            if($(this).val()){
                var url = "{{ route('api.v2.clients.outbound.type.show', [$client->id, ':id'])}}";
                    url = url.replace(':id', $(this).val());
                window.location.replace(url);
            }else{
                window.location.replace("{{ route('api.v2.clients.outbound.show', [$client->id])}}");
            }
        });
    @endif
});
</script>
@endsection
