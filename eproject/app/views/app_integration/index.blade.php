@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>S/4Hana Integration Files</li>
    </ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fas fa-box"></i> S/4Hana Integration Files
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2><i class="fa fa-fw fa-file-excel"></i> Integration Files </h2>
            </header>
            <div class="widget-body">
                <div id="main-table"></div>
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
                ajaxURL: "{{ route('app.integration.s4hana.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"Batch No.", field:"batch_number", width:120, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
                    {title:"Contracts", field:"name", minWidth: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('batch_number')){
                                var content = '<div class="row"><div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12"><div class="well">'
                                +'<ol style="list-style-type:none;margin:0;padding:0;overflow:hidden;" role="menu">'
                                +'<li style="text-align:left;padding-bottom:8px;"><a onclick="downloadExcel(\''+rowData["route:contractHeaderDownload"]+'\')" class="btn btn-xs btn-primary" title="Download"><i class="fa fa-download"></i></a> Header File ('+parseInt(rowData.total_contract_headers)+' records)</li>'
                                +'<li style="text-align:left;"><a onclick="downloadExcel(\''+rowData["route:contractItemDownload"]+'\')" class="btn btn-xs btn-primary" title="Download"><i class="fa fa-download"></i></a> Item File ('+parseInt(rowData.total_contract_items)+' records)</li>'
                                +'</ol>'
                                +'</div></div></div>';
                                return content;
                            }
                        }
                    }},
                    {title:"Claims", field:"name", minWidth: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('batch_number')){
                                var content = '<div class="row"><div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12"><div class="well">'
                                +'<ol style="list-style-type:none;margin:0;padding:0;overflow:hidden;" role="menu">'
                                +'<li style="text-align:left;padding-bottom:8px;"><a onclick="downloadExcel(\''+rowData["route:claimHeaderDownload"]+'\')" class="btn btn-xs btn-primary" title="Download"><i class="fa fa-download"></i></a> Header File ('+parseInt(rowData.total_claim_headers)+' records)</li>'
                                +'<li style="text-align:left;"><a onclick="downloadExcel(\''+rowData["route:claimItemDownload"]+'\')" class="btn btn-xs btn-primary" title="Download"><i class="fa fa-download"></i></a> Item File ('+parseInt(rowData.total_claim_items)+' records)</li>'
                                +'</ol>'
                                +'</div></div></div>';
                                return content;
                            }
                        }
                    }},
                    {title:"Date", field:"created_date", width:130, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"Resync", field:"batch_number", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('batch_number')){
                                var content = '<button type="button" id="'+rowData.batch_number+'-resync-btn" class="btn btn-xs btn-warning" title="Resync files" onclick="appIntegrationResync('+parseInt(rowData.batch_number)+')"><i class="fa fa-file-export"></i></button>';
                                return content;
                            }
                        }
                    }}
                ],
            });
        });

        function appIntegrationResync(batchNumber){
            app_progressBar.reset();
            app_progressBar.toggle();
            var last_response_len = false;
            var url = '{{ route("app.integration.s4hana.sync", ":batchNumber") }}';
            url = url.replace(':batchNumber', parseInt(batchNumber));
            $.ajax(url, {
                dataType: "text",
                xhrFields: {
                    onprogress: function(e){
                        var this_response, response = e.currentTarget.response;
                        if(last_response_len === false){
                            this_response = response;
                            last_response_len = response.length;
                        }else{
                            this_response = response.substring(last_response_len);
                            last_response_len = response.length;
                        }
                        var i = parseInt(this_response);
                        if(i){
                            app_progressBar.updateValue(i, 50);
                        }
                    }
                }
            })
            .done(function(data){
                setTimeout(function() {
                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                }, 999);
            })
            .fail(function(data){
                app_progressBar.maxOut();
                app_progressBar.toggle();
                console.log(data);
            });
        }

        function downloadExcel(url){
            var redirectWindow = window.open(url, '_blank');
            redirectWindow.location;
        }
    </script>
@endsection