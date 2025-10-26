@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ trans('vendorManagement.nomineesForWatchList') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.nomineesForWatchList') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <div class="btn-group pull-right header-btn">
            <div class="dropdown {{{ $classes ?? 'pull-right' }}}">
                <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
                <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
                    <li class="dropdown-submenu left">
                        <a tabindex="-1" href="javascript:void(0);" class="text-center">{{ trans('general.summary') }}</a>
                        <ul class="dropdown-menu">
                            <li>
                                <button type="button" class="btn btn-block btn-default btn-mg header-btn" data-action="vendor-group-summary">
                                    {{ trans('vendorManagement.vendorGroupSummary') }}
                                </button>
                            </li>
                            <li>
                                <button type="button" class="btn btn-block btn-default btn-mg header-btn" data-action="vendor-category-summary">
                                    {{ trans('vendorManagement.vendorCategorySummary') }}
                                </button>
                            </li>
                            <li>
                                <button type="button" class="btn btn-block btn-default btn-mg header-btn" data-action="vendor-work-category-summary">
                                    {{ trans('vendorManagement.vendorWorkCategorySummary') }}
                                </button>
                            </li>
                        </ul>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <button type="button" class="btn btn-block btn-primary btn-mg header-btn" data-action="view-scores">
                            {{ trans('vendorManagement.scores') }}
                        </button>
                    </li>
                    <li>
                        <button type="button" class="btn btn-block btn-primary btn-mg header-btn" data-action="view-scores-with-sub-work-categories">
                            {{ trans('vendorManagement.categories') }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.nomineesForWatchList') }}}</h2>
            </header>
            <div class="widget-body">
                <div id="watch-list-table"></div>
            </div>
        </div>
    </div>
</div>

@include('templates.generic_table_modal', [
    'modalId'          => 'vendor-group-summary-modal',
    'title'            => trans('vendorManagement.vendorGroupSummary'),
    'tableId'          => 'vendor-group-summary-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'vendor-category-summary-modal',
    'title'            => trans('vendorManagement.vendorCategorySummary'),
    'tableId'          => 'vendor-category-summary-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'vendor-work-category-summary-modal',
    'title'            => trans('vendorManagement.vendorWorkCategorySummary'),
    'tableId'          => 'vendor-work-category-summary-table',
    'modalDialogClass' => 'modal-xl',
])

@include('templates.generic_table_modal', [
    'modalId'          => 'cycle-evaluations-modal',
    'title'            => '',
    'tableId'          => 'cycle-evaluations-table',
    'modalDialogClass' => 'modal-xl',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-forms-modal',
    'title'            => '',
    'tableId'          => 'evaluation-forms-table',
    'modalDialogClass' => 'modal-xl',
])

<div class="modal fade" id="evaluation-form-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('vendorManagement.form') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div class="well">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('projects.reference') }}:</dt>
                                <dd data-name="project-reference"></dd>
                            </dl>
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('projects.project') }}:</dt>
                                <dd data-name="project"></dd>
                            </dl>
                        </div>
                        <div class="col col-lg-6">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('companies.companyName') }}:</dt>
                                <dd data-name="company"></dd>
                                <dt>{{ trans('vendorManagement.vendorWorkCategory') }}:</dt>
                                <dd data-name="vendor_work_category"></dd>
                                <dt>{{ trans('vendorManagement.evaluator') }}:</dt>
                                <dd data-name="evaluator"></dd>
                                <dt>{{ trans('vendorManagement.rating') }}:</dt>
                                <dd data-name="rating"></dd>
                            </dl>
                        </div>
                        <div class="col col-lg-6">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('vendorManagement.form') }}:</dt>
                                <dd data-name="form_name"></dd>
                                <dt>{{ trans('vendorManagement.status') }}:</dt>
                                <dd data-name="status"></dd>
                                <dt>&nbsp;</dt>
                                <dd></dd>
                                <dt>{{ trans('vendorManagement.score') }}:</dt>
                                <dd data-name="score"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div id="evaluation-form-table"></div>
                <div class="row">
                    <div class="col col-lg-12">
                    <dl>
                        <dt>{{ trans('general.remarks') }}:</dt>
                        <dd data-name="remarks"></dd>
                    </dl>
                    <dl>
                        <dt>{{ trans('general.attachments') }}:</dt>
                        <dd><button class="btn btn-xs btn-primary" data-action='show-attachments' data-route=''><i class="fa fa-paperclip"></i></button></dd>
                    </dl>
                    <dl>
                        <dt>{{ trans('general.logs') }}:</dt>
                        <dd>
                            <button class="btn btn-xs btn-primary" data-action='show-evaluator-log'>{{ trans('vendorPerformanceEvaluation.evaluationLogs') }}</button>
                            <button class="btn btn-xs btn-primary" data-action='show-verifier-log'>{{ trans('verifiers.verifierLog') }}</button>
                            <button class="btn btn-xs btn-primary" data-action='show-edit-log'>{{ trans('general.editLogs') }}</button>
                        </dd>
                    </dl>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('templates.generic_table_modal', [
    'modalId'    => 'evaluation-form-attachments-modal',
    'title'      => trans('general.attachments'),
    'tableId'    => 'evaluation-form-attachments-table',
])

@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-evaluator-log-modal',
    'title'            => trans('vendorPerformanceEvaluation.evaluationLogs'),
    'tableId'          => 'evaluation-form-evaluator-log-table',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-verifier-log-modal',
    'title'            => trans('verifiers.verifierLog'),
    'tableId'          => 'evaluation-form-verifier-log-table',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-edit-log-modal',
    'title'            => trans('general.editLogs'),
    'tableId'          => 'evaluation-form-edit-log-table',
])
@include('templates.generic_table_modal', [
    'modalId'          => 'evaluation-form-edit-details-log-modal',
    'title'            => trans('vendorPerformanceEvaluation.editDetails'),
    'tableId'          => 'evaluation-form-edit-details-log-table',
])

<div class="modal fade" id="scores-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('vendorManagement.scores') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div id="scores-table"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-action="export-scores"><i class="fa fa-download"></i> {{ trans('general.download') }}</a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="scores-with-sub-work-categories-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('vendorManagement.categories') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div id="scores-with-sub-work-categories-table"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-action="export-scores-with-sub-work-categories"><i class="fa fa-download"></i> {{ trans('general.download') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script src="{{ asset('js/app/modalStack.js') }}"></script>
    <script>
        $(document).ready(function () {
            <?php $canViewVendorProfile = $currentUser->canViewVendorProfile(); ?>
            var modalStack = new ModalStack();
            var watchListTable = new Tabulator('#watch-list-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorManagement.nominatedWatchList.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen:true},
                    {title:"{{ trans('vendorManagement.name') }}", field:"company", minWidth: 300, hozAlign:'left', headerSort:false, headerFilter: true, frozen:true, formatter:function(cell){
                        @if($canViewVendorProfile)
                            return '<a href="'+cell.getData()['route:vendor_profile']+'">'+cell.getData()['company']+'</a>';
                        @else
                            return cell.getData()['company'];
                        @endif
                    }},
                    {title:"{{ trans('vendorManagement.vendorCode') }}", field:"vendor_code", width:130, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, editor:"select", headerFilterParams:{{ json_encode($externalVendorGroupsFilterOptions) }} },
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_categories", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var vendorCategoriesArray = cell.getData()['vendor_categories'];
                        var output = [];
                        for(var i in vendorCategoriesArray){
                            output.push('<span class="label label-warning text-white">'+vendorCategoriesArray[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.from') }}", field:"from", width: 200, cssClass:"text-center text-middle", headerSort:false, editable: false, editor:"select", headerFilter:true, headerFilterParams:["{{ trans('general.all') }}", "{{ trans('vendorManagement.vendorPerformanceEvaluation') }}", "{{ trans('vendorManagement.watchList') }}"]},
                    {title:"{{ trans('vendorManagement.latestVpeScore') }}", cssClass:"text-center text-middle", columns:[
                        {title:"{{ trans('vendorManagement.cycle') }}", field:"cycle", width: 200, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorManagement.deliberatedScore') }}", field:"deliberated_score", width: 150, cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('vendorManagement.rating') }}", field:"rating", width: 120, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                show: function(cell){
                                    return cell.getData()['can_edit'];
                                },
                                tag: 'a',
                                attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("forms.edit") }}'},
                                rowAttributes: {'href': 'route:edit'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-edit'}
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                tag: 'button',
                                attributes: {type: 'button', class:'btn btn-xs btn-default', title: '{{ trans("vendorManagement.scores") }}', 'data-action': 'show-evaluations'},
                                rowAttributes: {'data-id': 'id', 'disabled': 'disable_scores'},
                                innerHtml: function(cellData){
                                    return "{{ trans('vendorManagement.scores') }}";
                                }
                            }
                        ]
                    }}
                ],
            });

            var cycleEvaluationsTable = new Tabulator('#cycle-evaluations-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.businessUnit') }}", field:"business_unit", width:150, hozAlign:"center", headerSort:false, cssClass:"text-center text-middle", headerFilter:true},
                    {title:"{{ trans('projects.reference') }}", field:"reference", width:150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, headerFilter:true},
                    {title:"{{ trans('projects.title') }}", field:"title", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.actions') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'button',
                                attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('vendorManagement.forms') }}", 'data-action': 'show-forms'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-list'},
                                }
                            }
                        ]
                    }}
                ]
            });

            $('#watch-list-table').on('click', '[data-action=show-evaluations]', function(){
                var row = watchListTable.getRow($(this).data('id'));
                cycleEvaluationsTable.setData(row.getData()['route:evaluations']);
                $('#cycle-evaluations-modal .modal-title').html(row.getData()['company']);
                modalStack.push('#cycle-evaluations-modal');
            });

            var evaluationFormsTable = new Tabulator('#evaluation-forms-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.evaluator') }}", field:"evaluator", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 80, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 150, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.actions') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'button',
                                attributes: {type: 'button', class:'btn btn-xs btn-default', title: "{{ trans('general.view') }}", 'data-action': 'show-form'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: function(rowData){
                                    return "{{ trans('general.view') }}";
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                }
                            },{
                                tag: 'a',
                                attributes: {target: '_blank', class:'btn btn-xs btn-warning', title: "{{ trans('general.download') }}", 'data-action': 'download'},
                                rowAttributes: {href: 'route:download'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-download'},
                                }
                            }
                        ]
                    }}
                ]
            });

            $('#cycle-evaluations-table').on('click', '[data-action=show-forms]', function(){
                var row = cycleEvaluationsTable.getRow($(this).data('id'));
                evaluationFormsTable.setData(row.getData()['route:forms']);
                $('#evaluation-forms-modal .modal-title').html(row.getData()['title']);
                modalStack.push('#evaluation-forms-modal');
            });

            var evaluationFormEvaluatorLogTable = new Tabulator('#evaluation-form-evaluator-log-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"evaluator", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    { title:"{{ trans('general.actions') }}", field: 'action', width: 200, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('general.date') }}", field: 'created_at', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                ]
            });

            $('#evaluation-form-modal').on('click', '[data-action=show-evaluator-log]', function(){
                modalStack.push('#evaluation-form-evaluator-log-modal');
            });

            var evaluationFormVerifierLogTable = new Tabulator('#evaluation-form-verifier-log-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('users.name') }}", field:"name", minWidth:250, hozAlign:"left", headerSort:false, headerFilter:true},
                    { title:"{{ trans('verifiers.status') }}", field: 'approved', width: 150, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:function(rowData){
                            if(rowData['approved'] === true){
                                return "<span class='text-success'><i class='fa fa-thumbs-up'></i> <strong>{{ trans('verifiers.approved') }}</strong></span>";
                            }
                            else if(rowData['approved'] === false){
                                return "<span class='text-danger'><i class='fa fa-thumbs-down'></i> <strong>{{ trans('verifiers.rejected') }}</strong></span>";
                            }
                            else{
                                return "<span class='text-warning'><i class='fa fa-question'></i> <strong>{{ trans('verifiers.unverified') }}</strong></span>";

                            }
                        }
                    }},
                    { title:"{{ trans('verifiers.verifiedAt') }}", field: 'verified_at', width: 150, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('verifiers.remarks') }}", field: 'remarks', width: 240, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                ]
            });

            $('#evaluation-form-modal').on('click', '[data-action=show-verifier-log]', function(){
                modalStack.push('#evaluation-form-verifier-log-modal');
            });

            var evaluationFormEditLogTable = new Tabulator('#evaluation-form-edit-log-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    { title:"{{ trans('vendorPerformanceEvaluation.editor') }}", field: 'name', headerSort:false, headerFilter:"input" },
                    { title:"{{ trans('general.date') }}", field: 'created_at', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml:[
                            {
                                tag: 'button',
                                attributes: {type: 'button', class:'btn btn-xs btn-success', title: "{{ trans('general.view') }}", 'data-action': 'show-edit-details-log'},
                                rowAttributes: {'data-id': 'id'},
                                innerHtml: function(rowData){
                                    return "{{ trans('general.view') }}";
                                }
                            }
                        ]
                    }}
                ]
            });

            $('#evaluation-form-modal').on('click', '[data-action=show-edit-log]', function(){
                modalStack.push('#evaluation-form-edit-log-modal');
            });

            var evaluationFormEditDetailsLogTable = new Tabulator('#evaluation-form-edit-details-log-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.name') }}", field:"node_name", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                    {
                        title: "{{ trans('general.previous') }}",
                        cssClass:"text-center text-middle",
                        columns:[
                            {title:"{{ trans('general.name') }}", field:"previous_score_name", width: 250, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorPerformanceEvaluation.score') }}", field:"previous_score_value", width: 80, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorPerformanceEvaluation.applicability') }}", field:"previous_score_excluded", width: 120, cssClass:"text-center text-middle", headerSort:false},
                        ]
                    },{
                        title: "{{ trans('general.current') }}",
                        cssClass:"text-center text-middle",
                        columns:[
                            {title:"{{ trans('general.name') }}", field:"current_score_name", width: 250, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorPerformanceEvaluation.score') }}", field:"current_score_value", width: 80, cssClass:"text-center text-middle", headerSort:false},
                            {title:"{{ trans('vendorPerformanceEvaluation.applicability') }}", field:"current_score_excluded", width: 120, cssClass:"text-center text-middle", headerSort:false},
                        ]
                    }
                ]
            });

            $('#evaluation-form-edit-log-table').on('click', '[data-action=show-edit-details-log]', function(){
                var row = evaluationFormEditLogTable.getRow($(this).data('id'));
                evaluationFormEditDetailsLogTable.setData(row.getData()['route:details']);
                modalStack.push('#evaluation-form-edit-details-log-modal');
            });

            var evaluationFormTable = new Tabulator('#evaluation-form-table', {
                dataTree: true,
                dataTreeStartExpanded:true,
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter: function(cell){
                        var cellData = cell.getData();

                        var description = cellData['description'];

                        if(cell.getData()['type'] == 'node'){
                            description = '<strong>'+description+'</strong>';
                        }
                        else if(cell.getData()['type'] == 'score' && cell.getData()['selected']){
                            description = '<strong>'+description+'</strong>';
                        }

                        return description;
                    }},
                    {title:"{{ trans('forms.notApplicable') }}", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                if(rowData['type'] == 'node' && rowData['depth'] > 0 && rowData['hasScores'])
                                {
                                    var checked = rowData['is_excluded'] ? 'checked' : '';
                                    return '<input type="checkbox" '+checked+' disabled>';
                                }
                            }
                        }
                    }},
                    {title:"{{ trans('general.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", hozAlign:"center", headerSort:false},
                    {title:"{{ trans('general.selected') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', field:'selected' }
                ],
            });

            var evaluationFormAttachmentsTable = new Tabulator('#evaluation-form-attachments-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", cssClass:"text-center", width: 15, headerSort:false, formatter:"rownum"},
                    {title:"{{ trans('general.name') }}", cssClass:"text-left", minWidth: 400, headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter,
                        formatterParams: {
                            innerHtml: function(rowData){
                                return rowData.filename;
                            },
                            tag: 'a',
                            attributes: {'download': ''},
                            rowAttributes: {'href': 'download_url'}
                        }
                    },
                    {title:"{{ trans('files.uploadedBy') }}", field:'uploaded_by', minWidth: 150, cssClass:"text-center", headerSort:false},
                    {title:"{{ trans('files.uploadedAt') }}", field:'uploaded_at', minWidth: 150, cssClass:"text-center", headerSort:false},
                ]
            });

            $('#evaluation-forms-table').on('click', '[data-action=show-form]', function(){
                var row = evaluationFormsTable.getRow($(this).data('id'));
                evaluationFormEvaluatorLogTable.setData(row.getData()['route:evaluator_log']);
                evaluationFormVerifierLogTable.setData(row.getData()['route:verifier_log']);
                evaluationFormEditLogTable.setData(row.getData()['route:edit_log']);
                $.get(row.getData()['route:form_info'], function(data){
                    evaluationFormTable.setData(data['route:grid']);
                    $('#evaluation-form-modal [data-name=project-reference]').html(data['project_reference']);
                    $('#evaluation-form-modal [data-name=project]').html(data['project']);
                    $('#evaluation-form-modal [data-name=company]').html(data['company']);
                    $('#evaluation-form-modal [data-name=vendor_work_category]').html(data['vendor_work_category']);
                    $('#evaluation-form-modal [data-name=form_name]').html(data['form_name']);
                    $('#evaluation-form-modal [data-name=status]').html(data['status']);
                    $('#evaluation-form-modal [data-name=evaluator]').html(data['evaluator']);
                    $('#evaluation-form-modal [data-name=score]').html(data['score']);
                    $('#evaluation-form-modal [data-name=rating]').html(data['rating']);
                    $('#evaluation-form-modal [data-name=remarks]').html(data['remarks']);
                    evaluationFormAttachmentsTable.setData(data['route:attachments']);
                    modalStack.push('#evaluation-form-modal');
                });
            });

            $('#evaluation-form-modal').on('click', '[data-action=show-attachments]', function(){
                modalStack.push('#evaluation-form-attachments-modal');
            });

            var contractGroupCategorySummaryTable = new Tabulator('#vendor-group-summary-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('general.count') }}", field:"count", width: 100, cssClass:"text-center text-middle", headerSort:true}
                ],
            });

            $('[data-action=vendor-group-summary]').on('click', function(){
                contractGroupCategorySummaryTable.setData("{{ route('vendorManagement.nominatedWatchList.summary.contractGroupCategories') }}");
                modalStack.push('#vendor-group-summary-modal');
            });

            var vendorCategorySummaryTable = new Tabulator('#vendor-category-summary-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width: 200, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_category", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('general.count') }}", field:"count", width: 100, cssClass:"text-center text-middle", headerSort:true}
                ],
            });

            $('[data-action=vendor-category-summary]').on('click', function(){
                vendorCategorySummaryTable.setData("{{ route('vendorManagement.nominatedWatchList.summary.vendorCategories') }}");
                modalStack.push('#vendor-category-summary-modal');
            });

            var vendorWorkCategorySummaryTable = new Tabulator('#vendor-work-category-summary-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var arr = cell.getData()['contract_group_categories'];
                        var output = [];
                        for(var i in arr){
                            output.push('<span class="label label-primary text-white">'+arr[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('vendorManagement.vendorCategories') }}", field:"vendor_categories", width: 200, hozAlign:"left", headerSort:false, headerFilter: true, formatter: function(cell){
                        var vendorCategoriesArray = cell.getData()['vendor_categories'];
                        var output = [];
                        for(var i in vendorCategoriesArray){
                            output.push('<span class="label label-warning text-white">'+vendorCategoriesArray[i]+'</span>');
                        }
                        return output.join('&nbsp;', output);
                    }},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true},
                    {title:"{{ trans('general.count') }}", field:"count", width: 100, cssClass:"text-center text-middle", headerSort:true}
                ],
            });

            $('[data-action=vendor-work-category-summary]').on('click', function(){
                vendorWorkCategorySummaryTable.setData("{{ route('vendorManagement.nominatedWatchList.summary.vendorWorkCategories') }}");
                modalStack.push('#vendor-work-category-summary-modal');
            });

            var scoresTable = new Tabulator('#scores-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"company", minWidth: 300, hozAlign:'left', headerSort:false, headerFilter: true, formatter:function(cell){
                        @if($canViewVendorProfile)
                            return '<a href="'+cell.getData()['route:vendor_profile']+'">'+cell.getData()['company']+'</a>';
                        @else
                            return cell.getData()['company'];
                        @endif
                    }},
                    {title:"{{ trans('vendorManagement.vendorCode') }}", field:"vendor_code", width:130, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width:200, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_work_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_work_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]}
                ],
            });

            $('[data-action=view-scores]').on('click', function(){
                scoresTable.setData("{{ route('vendorManagement.nominatedWatchList.scores.list') }}");
                modalStack.push('#scores-modal');
            });

            var scoresWithSubWorkCategoriesTable = new Tabulator('#scores-with-sub-work-categories-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"company", minWidth: 300, hozAlign:'left', headerSort:false, headerFilter: true, formatter:function(cell){
                        @if($canViewVendorProfile)
                            return '<a href="'+cell.getData()['route:vendor_profile']+'">'+cell.getData()['company']+'</a>';
                        @else
                            return cell.getData()['company'];
                        @endif
                    }},
                    {title:"{{ trans('vendorManagement.vendorCode') }}", field:"vendor_code", width:130, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"contract_group_category", width:200, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", cssClass:"text-center text-middle", columns: [
                        {title:"{{ trans('general.name') }}", field:"vendor_work_category", width: 200, cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                        {title:"{{ trans('vendorManagement.score') }}", field:"vendor_work_category_score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    ]},
                    {title:"{{ trans('vendorManagement.vendorSubWorkCategories') }}", field:"vendor_sub_work_categories", width:250, hozAlign:'center', headerFilter: true, cssClass:"text-center text-bottom", headerSort:false},
                ],
            });

            $('[data-action=view-scores-with-sub-work-categories]').on('click', function() {
                scoresWithSubWorkCategoriesTable.setData("{{ route('vendorManagement.nominatedWatchList.scores.subWorkCategories.list') }}");
                $('#scores-with-sub-work-categories-modal').modal('show');
            });

            $('[data-action=export-scores]').on('click', function(){
                var filters = scoresTable.getHeaderFilters();
                var parameters = [];
                var url = "{{ route('vendorManagement.nominatedWatchList.scores.export') }}";

                for (var i=0;i< filters.length;i++){
                    if (filters[i].hasOwnProperty('field') && filters[i].hasOwnProperty('value')) {
                        parameters.push(encodeURI('filters['+i+'][field]=' + filters[i].field));
                        parameters.push(encodeURI('filters['+i+'][value]=' + filters[i].value));
                    }
                }

                if(parameters.length){
                    url += '?'+parameters.join('&');
                }

                window.open(url, '_blank');
            });

            $('[data-action=export-scores-with-sub-work-categories]').on('click', function(){
                var filters = scoresWithSubWorkCategoriesTable.getHeaderFilters();
                var parameters = [];
                var url = "{{ route('vendorManagement.nominatedWatchList.scores.subWorkCategories.export') }}";

                for (var i=0;i< filters.length;i++){
                    if (filters[i].hasOwnProperty('field') && filters[i].hasOwnProperty('value')) {
                        parameters.push(encodeURI('filters['+i+'][field]=' + filters[i].field));
                        parameters.push(encodeURI('filters['+i+'][value]=' + filters[i].value));
                    }
                }

                if(parameters.length){
                    url += '?'+parameters.join('&');
                }

                window.open(url, '_blank');
            });
        });
    </script>
@endsection