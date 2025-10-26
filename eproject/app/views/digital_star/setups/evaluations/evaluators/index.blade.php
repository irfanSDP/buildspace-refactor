@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluation') }}</li>
        <li>{{ link_to_route('digital-star.cycle.index', trans('digitalStar/vendorManagement.cycle'), array()) }}</li>
        <li>{{ link_to_route('digital-star.setups.index', trans('digitalStar/vendorManagement.setup'), array('cycle' => $evaluation->ds_cycle_id)) }}</li>
        <li>{{ trans('digitalStar/digitalStar.evaluators') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{ $company->name }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2>{{ trans('digitalStar/digitalStar.companyEvaluators') }}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div id="company-evaluators-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('digitalStar/digitalStar.projects') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <div id="projects-table"></div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            var companyEvaluatorsTable = new Tabulator('#company-evaluators-table', {
                height: 400,
                ajaxURL: "{{ route('digital-star.setups.evaluators.company.assigned', [$evaluation->id]) }}",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 10,
                pagination: "remote",
                ajaxFiltering: true,
                layout: "fitColumns",
                columns:[
                    {title: "{{ trans('general.no') }}", field: "counter", width: 60, hozAlign: 'center', cssClass: "text-center text-middle", headerSort: false, frozen: true},
                    {title: "{{ trans('users.name') }}", field: "user_name", hozAlign: "left", headerSort: false, headerFilter: true},
                    {title: "{{ trans('users.email') }}", field: "user_email", hozAlign: 'center', cssClass: "text-center text-middle", headerSort: false, headerFilter: true},
                    {title: "{{ trans('users.company') }}", field: "company_name", hozAlign: 'center', headerSort: false, cssClass: "text-center text-middle", headerFilter: true},
                    {title: "{{ trans('digitalStar/digitalStar.vendorGroup') }}", field: "vendor_group", hozAlign: 'center', headerSort: false, cssClass: "text-center text-middle", headerFilter: true}
                ],
            });

            var projectsTable = new Tabulator('#projects-table', {
                height: 405,
                ajaxURL: "{{ route('digital-star.setups.evaluators.projects', [$evaluation->id]) }}",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 10,
                pagination: "remote",
                ajaxFiltering: true,
                layout: "fitColumns",
                columns:[
                    { title: "{{ trans('general.no') }}", formatter:"rownum", width: 60, hozAlign: 'center', cssClass: "text-center text-middle", headerSort: false, frozen: true },
                    { title: "{{ trans('projects.reference') }}", field:"reference", width: 180, cssClass:"text-center text-middle", headerSort: false, headerFilter: 'input', frozen: true },
                    { title: "{{ trans('projects.title') }}", field:"title", hozAlign:'center', cssClass:"text-left", headerSort: false, headerFilter: 'input' },
                    { title: "{{ trans('projects.status') }}", field:"status", width: 180, cssClass:"text-center text-middle", headerSort: false, headerFilter: 'input' },
                    { title: "{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData) {
                            var totalAssigned = rowData['totalAssigned'] || 0; // Ensure a valid value
                            var projectUrl = rowData['route:project'] || '#'; // Ensure valid route

                            return '<a href="'+ projectUrl +'" class="btn btn-xs btn-primary" title="{{ trans('digitalStar/digitalStar.evaluators') }}">'
                                + '<i class="fa fa-users"></i></a> <span>' + totalAssigned + " {{ trans('digitalStar/digitalStar.totalAssignedUsers') }}</span>";
                        }
                    }}
                ],
            });
        });
    </script>
@endsection