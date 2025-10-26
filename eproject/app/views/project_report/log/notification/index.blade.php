@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
    <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('projectReportNotification.logTitle') }}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-user-tag"></i> {{ trans('projectReportNotification.logTitle') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('projectReportNotification.logTitle') }}</h2>
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
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('log.projectReport.notification.list') }}",
                ajaxConfig: 'GET',
                paginationSize: 30,
                pagination: 'local',
                layout:'fitColumns',
                columns:[
                    { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('projectReportNotification.templateName') }}", field:"templateName", width: 300, hozAlign:"left", cssClass:"text-left text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    { title:"{{ trans('auth.username') }}", field:"recipientUsername", width: 280, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    { title:"{{ trans('users.name') }}", field:"recipientName", width: 300, hozAlign:'left', headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                    { title:"{{ trans('projectReportNotification.sentDateTime') }}", field:"sentDateTime", width: 280, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
                ]
            });
        });
    </script>
@endsection