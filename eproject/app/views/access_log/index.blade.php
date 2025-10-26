@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
    <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('general.userAccessLogs') }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-user-tag"></i> {{{ trans('general.userAccessLogs') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('general.userAccessLogs') }}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div id="main-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('log.access.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('auth.username') }}", field:"username", width: 280, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('users.name') }}", field:"name", width: 300, hozAlign:'left', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"Timestamp", field:"created_at", width: 280, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "yyyy-mm-dd hh:mm:ss~yyyy-mm-dd hh:mm:ss"},
                    {title:"Url", field:"url", width: 300, hozAlign:"left", cssClass:"text-left text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"Path", field:"url_path", width: 200, hozAlign:"left", cssClass:"text-left text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"Params", field:"params", width: 200, hozAlign:"left", cssClass:"text-left text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"IP", field:"ip_address", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"User-Agent", field:"user_agent", minWidth: 300, hozAlign:"left", cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"Method", field:"http_method", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"}
                ]
            });
        });
    </script>
@endsection