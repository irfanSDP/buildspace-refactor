@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
    <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('general.userAuthenticationLogs') }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-user-shield"></i> {{{ trans('general.userAuthenticationLogs') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <div class="pull-right ">
                <button type="button" class="btn btn-success btn-md header-btn" id="authenticationLogExportBtn"><i class="fa fa-file-excel"></i> {{ trans('general.exportToExcel') }}</button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('general.userAuthenticationLogs') }}</h2>
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
                ajaxURL: "{{ route('log.authentication.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('auth.username') }}", field:"username", width: 280, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"{{ trans('contractGroupCategories.name') }}", field:"name", width: 300, hozAlign:'left', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                    {title:"IP", field:"ip", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"User-Agent", field:"user_agent", minWidth: 300, hozAlign:"left", cssClass:"text-center text-middle", headerSort:false},
                    {title:"Login At", field:"login_at", width: 160, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"Logout At", field:"logout_at", width: 160, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
                ]
            });

            $('#authenticationLogExportBtn').on('click', function(e){
                if(mainTable){
                    var filters = mainTable.getHeaderFilters();

                    var parameters = [];
                    for (var i=0;i< filters.length;i++){
                        if (filters[i].hasOwnProperty('field') && filters[i].hasOwnProperty('value')) {
                            parameters.push(encodeURI(filters[i].field + '=' + filters[i].value));
                        }
                    }
                    var url = '{{{route("log.authentication.export.excel")}}}';
                    if(parameters.length){
                        url += '?'+parameters.join('&');
                    }

                    window.open(url, '_blank');
                }
            });
        });
    </script>
@endsection