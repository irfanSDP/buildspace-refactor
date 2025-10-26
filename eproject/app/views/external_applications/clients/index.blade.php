@extends('layout.main')

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), []) }}</li>
    <li>API V2</li>
    <li>{{{ trans('general.clients') }}}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-network-wired"></i> API V2
        </h1>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 mb-4">
        <button type="button" id="ext_app_client_create-btn" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('general.newClient') }}}
        </button>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('general.clients') }}} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="client-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('external_applications.clients.partials.form', ['formPrefix'=>'client_create'])

@endsection

@section('js')
@include('external_applications.clients.partials.form_javascript', ['formPrefix'=>'client_create'])
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
<script type="text/javascript">
$(document).ready(function () {
    const clientTable = new Tabulator('#client-table', {
        height:520,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('api.v2.clients.list') }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"textarea"},
            {title:"{{ trans('general.remarks') }}", field:"remarks", width: 300, hozAlign:"left", headerSort:false, formatter:"textarea"},
            {title:"{{ trans('general.createdAt') }}", field:"created_at", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    tag: 'a',
                    attributes: {class:'btn btn-xs btn-primary', title: '{{ trans("general.view") }}'},
                    rowAttributes: {'href': 'route:show'},
                    innerHtml: {
                        tag: 'i',
                        attributes: {class: 'fa fa-search'}
                    }
                },{
                    innerHtml: function(){
                        return '&nbsp;';
                    }
                },{
                    innerHtml: function(rowData){
                        return '<a href="'+rowData['route:delete']+'" title="{{ trans('general.delete') }}" class="btn btn-xs btn-danger" data-id="'+rowData.id+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                    }
                }]
            }}
        ]
    });

    $('#client_create-form').on('submit', function(e){
        e.preventDefault();
        submitForm($(this)[0], function(){
            clientTable.replaceData();
        });
    });
});
</script>
@endsection