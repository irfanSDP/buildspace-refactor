@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ trans('vendorManagement.vendorPerformanceEvaluationCycles') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.vendorPerformanceEvaluationCycles') }}}
        </h1>
    </div>
    @if($canAddCycle)
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('vendorPerformanceEvaluation.cycle.create') }}" class="btn btn-primary btn-md pull-right header-btn">
            <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
        </a>
    </div>
    @endif
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.vendorPerformanceEvaluationCycles') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="log-list-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('general.logs') }}}
                </h4>
            </div>
            <div class="modal-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th scope="row">1</th>
                            <td>{{ trans('vendorManagement.formChangeRequests') }}</td>
                            <td>
                                <button type="button" class="btn btn-xs btn-default" data-action="show-form-change-requests">
                                    {{ trans('general.show') }}
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">2</th>
                            <td>{{ trans('vendorManagement.formChanges') }}</td>
                            <td>
                                <button type="button" class="btn btn-xs btn-default" data-action="show-form-changes">
                                    {{ trans('general.show') }}
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">3</th>
                            <td>{{ trans('vendorManagement.projectRemovalRequests') }}</td>
                            <td>
                                <button type="button" class="btn btn-xs btn-default" data-action="show-removal-requests">
                                    {{ trans('general.show') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('templates.generic_table_modal', [
    'modalId'          => 'form-change-requests-modal',
    'title'            => trans('vendorManagement.formChangeRequests'),
    'tableId'          => 'form-change-requests-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'form-changes-modal',
    'title'            => trans('vendorManagement.formChanges'),
    'tableId'          => 'form-changes-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'project-removal-requests-modal',
    'title'            => trans('vendorManagement.projectRemovalRequests'),
    'tableId'          => 'project-removal-requests-table',
    'modalDialogClass' => 'modal-xl',
])
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorPerformanceEvaluation.cycle.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.startDate') }}", field:"start_date", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.endDate') }}", field:"end_date", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.completed') }}", field:"completed", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter: "tick"},
                    {title:"{{ trans('vendorManagement.vpeCycleName') }}", field:"remarks", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("vendorManagement.projects") }}'},
                                rowAttributes: {'href': 'route:projects'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-list'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                tag: 'button',
                                attributes: {type: 'button', class:'btn btn-xs btn-primary', title: '{{ trans("general.logs") }}', 'data-action': 'show-logs-list'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: function(cellData){
                                    return "{{ trans('general.logs') }}";
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                show: function(cell){
                                    return !cell.getData()['completed'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("forms.edit") }}'},
                                rowAttributes: {'href': 'route:edit'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            }
                        ]
                    }}
                ],
            });

            var formChangeRequestsTable = new Tabulator('#form-change-requests-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.requestedBy') }}", field:"requested_by", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendor') }}", field:"company", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('projects.reference') }}", field:"reference", width:150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    {title:"{{ trans('projects.project') }}", field:"project", minWidth: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_categories", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var vendorCategoriesArray = cell.getData()['vendor_categories'];
                        var output = [];
                        for(var i in vendorCategoriesArray){
                            output.push('<span class="label label-warning text-white">'+vendorCategoriesArray[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.requestedAt') }}", field:"requested_at", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.remarks') }}", field:"remarks", width:380, cssClass:"text-left text-middle", headerSort:false, formatter:'textarea'}
                ],
            });

            var formChangesTable = new Tabulator('#form-changes-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.updatedBy') }}", field:"updated_by", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendor') }}", field:"company", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('projects.reference') }}", field:"reference", width:150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    {title:"{{ trans('projects.project') }}", field:"project", minWidth: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_categories", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var vendorCategoriesArray = cell.getData()['vendor_categories'];
                        var output = [];
                        for(var i in vendorCategoriesArray){
                            output.push('<span class="label label-warning text-white">'+vendorCategoriesArray[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('general.from') }}", field:"old_form", width: 180, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('general.to') }}", field:"new_form", width: 180, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('general.updatedAt') }}", field:"updated_at", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                ],
            });

            var projectRemovalRequestsTable = new Tabulator('#project-removal-requests-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.reference') }}", field:"reference", width:150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    {title:"{{ trans('projects.project') }}", field:"project", minWidth: 250, hozAlign:"left", cssClass:"text-left text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.request') }}", hozAlign:"center", cssClass:"text-center text-middle", columns:[
                        {title:"{{ trans('vendorManagement.requestedBy') }}", field:"requested_by", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.vendor') }}", field:"company", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.reason') }}", field:"reason", width: 160, hozAlign:"left", cssClass:"text-left text-middle", headerSort:false},
                        {title:"{{ trans('vendorManagement.remarks') }}", field:"request_remarks", width: 200, hozAlign:"left", cssClass:"text-left text-middle", headerSort:false},
                        {title:"{{ trans('vendorManagement.requestedAt') }}", field:"requested_at", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('vendorManagement.response') }}", hozAlign:"center", cssClass:"text-center text-middle", columns:[
                        {title:"{{ trans('vendorManagement.responded') }}", field:"responded", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter: "tick", headerFilter: 'select', headerFilterParams: {0: "{{ trans('general.all') }}", 1:"{{ trans('vendorManagement.responded') }}", 2:"{{ trans('vendorManagement.notResponded') }}"}},
                        {title:"{{ trans('vendorManagement.attendedBy') }}", field:"attended_by", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.removedAt') }}", field:"removed_at", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: 'select', headerFilterParams: {0: "{{ trans('general.all') }}", 1:"{{ trans('vendorManagement.removed') }}", 2:"{{ trans('vendorManagement.notRemoved') }}"}},
                        {title:"{{ trans('vendorManagement.dismissedAt') }}", field:"dismissed_at", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: 'select', headerFilterParams: {0: "{{ trans('general.all') }}", 1:"{{ trans('vendorManagement.dismissed') }}", 2:"{{ trans('vendorManagement.notDismissed') }}"}},
                        {title:"{{ trans('vendorManagement.remarks') }}", field:"dismissal_remarks", width: 200, hozAlign:"left", cssClass:"text-left text-middle", headerSort:false},
                    ]}
                ],
            });

            var selectedCycleId;

            $('#main-table').on('click', '[data-action=show-logs-list]', function(){
                selectedCycleId = $(this).data('id');
                $('#log-list-modal').modal('show');
            });

            $('#log-list-modal [data-action=show-form-change-requests]').on('click', function(){
                var row = mainTable.getRow(selectedCycleId);
                formChangeRequestsTable.setData(row.getData()['route:form_change_requests']);
                $('#form-change-requests-modal').modal('show');
            });

            $('#log-list-modal [data-action=show-form-changes]').on('click', function(){
                var row = mainTable.getRow(selectedCycleId);
                formChangesTable.setData(row.getData()['route:form_changes']);
                $('#form-changes-modal').modal('show');
            });

            $('#log-list-modal [data-action=show-removal-requests]').on('click', function(){
                var row = mainTable.getRow(selectedCycleId);
                projectRemovalRequestsTable.setData(row.getData()['route:removal_requests']);
                $('#project-removal-requests-modal').modal('show');
            });
        });
    </script>
@endsection