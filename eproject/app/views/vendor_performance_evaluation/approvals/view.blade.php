@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.companyForms.approval', trans('forms.approval'), array()) }}</li>
        <li>{{{ trans('forms.approval') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-check"></i> {{{ trans('forms.approval') }}}
        </h1>
    </div>
    @if($canAssignVerifiers)
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <a href="{{ route('vendorPerformanceEvaluation.companyForms.approval.submitter.edit', [$companyForm->id]) }}" class="btn btn-warning pull-right"><i class="fas fa-edit"></i>&nbsp;{{ trans('forms.edit') }}</a>
    </div>
    @endif
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $companyForm->weightedNode->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="well">
                        <div class="row">
                            <div class="col col-lg-12">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('projects.reference') }}:</dt>
                                    <dd>{{{ $companyForm->vendorPerformanceEvaluation->project->reference }}}</dd>
                                </dl>
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('projects.project') }}:</dt>
                                    <dd>{{{ $companyForm->vendorPerformanceEvaluation->project->title }}}</dd>
                                </dl>
                            </div>
                            <div class="col col-lg-6">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('companies.companyName') }}:</dt>
                                    <dd>{{{ $companyForm->company->name }}}</dd>
                                    <dt>{{ trans('vendorManagement.vendorWorkCategory') }}:</dt>
                                    <dd>{{{ $companyForm->vendorWorkCategory->name }}}</dd>
                                </dl>
                            </div>
                            <div class="col col-lg-6">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('vendorManagement.form') }}:</dt>
                                    <dd>{{{ $companyForm->weightedNode->name }}}</dd>
                                    <dt>{{ trans('vendorManagement.status') }}:</dt>
                                    <dd>{{{ \PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm::getStatusText($companyForm->status_id) }}}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    @if(!$companyForm->isCompleted() && !empty($companyForm->processor_remarks))
                    <div class="well border-danger text-danger">
                        {{{ $companyForm->processor_remarks }}}
                    </div>
                    <br/>
                    @endif
                    <div id="main-table"></div>
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label pull-right">{{ trans('vendorPerformanceEvaluation.totalScore') }} : {{ $vpeScore }} / 100</label>
                        </section>
                    </div>
                    {{ Form::open(array('route' => array('vendorPerformanceEvaluation.companyForms.approval.update', $companyForm->id), 'id' => 'form')) }}
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('vendorManagement.remarks') }}}:</label>
                            <label class="textarea">
                                {{ Form::textArea('evaluator_remarks', $companyForm->evaluator_remarks, array('class' => 'fill-horizontal', 'rows' => 3, 'disabled' => 'disabled')) }}
                            </label>
                        </section>
                    </div>
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('forms.attachments') }}}:</label>
                            @include('file_uploads.partials.uploaded_file_show_only')
                        </section>
                    </div>
                    @if($canAssignVerifiers)
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            @include('verifiers.select_verifiers', ['verifiers' => $listOfVerifiers])
                        </section>
                    </div>
                    <footer class="pull-right">
                        <button type="submit" id="btnReject" name="submit" value="reject" class="btn btn-danger" data-intercept="confirmation" data-confirmation-with-remarks="remarks" data-confirmation-with-remarks-required="true" data-confirmation-message="{{ trans('forms.rejectionReason') }}">{{ trans('forms.reject') }}</button>
                        <button type="submit" id="btnSubmit" name="submit" value="approve" class="btn btn-success" data-intercept="confirmation" data-intercept-condition="noVerifier">{{ trans('forms.submit') }}</button>
                    </footer>
                    @endif
                    {{ Form::close() }}
                    @if($canApproveOrReject)
                        <footer class="pull-right">
                            @include('verifiers.approvalForm', [
                                'formId' => 'verifierForm',
                                'object' => $companyForm,
                            ])
                        </footer>
                    @endif
                    <div class="pull-left">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#companyFormEvaluationLogsModal">{{ trans('vendorPerformanceEvaluation.evaluationLogs') }}</button>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#verifierLogsModal">{{ trans('verifiers.verifierLogs') }}</button>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#processorEditLogsModal">{{ trans('vendorPerformanceEvaluation.editLogs') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('templates.warning_modal', [
    'modalId' => 'warningModal',
    'message' => trans('forms.verifiersRequired'),
])
@include('templates.verifier_remarks_modal', [
    'verifierApproveModalId' => 'verifierApproveModal',
    'verifierRejectModalId'  => 'verifierRejectModal',
])
@include('templates.verifier_logs_modal', [
    'modalId' => 'verifierLogsModal',
])
@include('templates.generic_table_modal', [
    'modalId'    => 'processorEditLogsModal',
    'title'      => trans('vendorPerformanceEvaluation.editLogs'),
    'tableId'    => 'processorEditLogsTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@include('templates.generic_table_modal', [
    'modalId'    => 'processorEditDetailsModal',
    'title'      => trans('vendorPerformanceEvaluation.editDetails'),
    'tableId'    => 'processorEditDetailsTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@include('templates.generic_table_modal', [
    'modalId'    => 'companyFormEvaluationLogsModal',
    'title'      => trans('vendorPerformanceEvaluation.evaluationLogs'),
    'tableId'    => 'companyFormEvaluationLogsTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var processorEditLogsTable = null;
            var processorEditDetailsTable = null;
            var companyFormEvaluationLogsTable = null;

            var mainTable = new Tabulator('#main-table', {
                dataTree: true,
                dataTreeStartExpanded:true,
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                dataLoaded: function(data){
                    excludedIds = {{ json_encode($excludedIds) }};

                    var excludedRow;
                    for(var nodeId in excludedIds){
                        $('input[type=checkbox][data-node-id="'+nodeId+'"]').prop('checked', true);

                        excludedRow = getNestedRow(this, 'node-'+excludedIds[nodeId]);

                        if(excludedRow) excludedRow.treeCollapse();
                    }
                },
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
                                    return '<input type="checkbox" name="exclude-'+rowData['nodeId']+'" data-action="exclude-node" data-id="'+rowData['id']+'" data-node-id="'+rowData['nodeId']+'" '+checked+' disabled>';
                                }
                            }
                        }
                    }},
                    {title:"{{ trans('general.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", hozAlign:"center", headerSort:false},
                    {title:"{{ trans('general.selected') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', field:'selected' }
                ],
            });

            var actionsFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var viewEditDetailsButton = document.createElement('a');
                viewEditDetailsButton.dataset.toggle = 'tooltip';
                viewEditDetailsButton.className = 'btn btn-xs btn-success';
                viewEditDetailsButton.innerHTML = '<i class="fas fa-list"></i>';
                viewEditDetailsButton.style['margin-right'] = '5px';

                viewEditDetailsButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    $('#processorEditDetailsModal').data('url', data.route_edit_details);
                    $('#processorEditDetailsModal').modal('show');
                });

                return viewEditDetailsButton;
            }

            $('#processorEditLogsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                processorEditLogsTable = new Tabulator('#processorEditLogsTable', {
                    height:400,
                    pagination:"local",
                    columns: [
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('vendorPerformanceEvaluation.editor') }}", field: 'edited_by', headerSort:false, headerFilter:"input" },
                        { title:"{{ trans('general.date') }}", field: 'time_stamp', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                        { title: "{{ trans('forms.actions') }}", width: 60, 'align': 'center', cssClass:"text-center", headerSort:false, formatter: actionsFormatter },
                    ],
                    layout:"fitColumns",
                    ajaxURL: "{{ route('vendorPerformanceEvaluation.processor.edit.logs.get', [$companyForm->id]) }}",
                    movableColumns:true,
                    placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                    columnHeaderSortMulti:false,
                });
            });

            $('#companyFormEvaluationLogsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                companyFormEvaluationLogsTable = new Tabulator('#companyFormEvaluationLogsTable', {
                    height:400,
                    pagination:"local",
                    columns: [
                        { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('vendorPerformanceEvaluation.editor') }}", field: 'created_by', headerSort:false },
                        { title:"{{ trans('general.actions') }}", field: 'action', width: 200, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('general.date') }}", field: 'time_stamp', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    ],
                    layout:"fitColumns",
                    ajaxURL: "{{ route('vendorPerformanceEvaluation.evaluation.logs.get', [$companyForm->id]) }}",
                    movableColumns:true,
                    placeholder:"{{ trans('general.noRecordsToDisplay') }}",
                    columnHeaderSortMulti:false,
                });
            });

            var scoreFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();
                var text = '';

                switch(formatterParams.column)
                {
                    case 'previous_score_name':
                    case 'previous_score':
                        text = ((data.previous_score_id == null) || data.previous_score_excluded) ? "{{ trans('general.notAvailable') }}" : data[formatterParams.column];
                        break;
                    case 'current_score_name':
                    case 'current_score':
                        text = ((data.current_score_id == null) || data.current_score_excluded) ? "{{ trans('general.notAvailable') }}" : data[formatterParams.column];
                        break;
                }

                return text;
            }

            var applicabilityFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();
                var text = data[formatterParams.column] ? "{{ trans('forms.notApplicable') }}" : '-';

                return text;
            }

            $('#processorEditDetailsModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                processorEditDetailsTable = new Tabulator('#processorEditDetailsTable', {
                    height:400,
                    pagination:"local",
                    columns:[
                        {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                        {title:"{{ trans('general.name') }}", field:"node_name", minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true},
                        {
                            title: "{{ trans('general.previous') }}",
                            cssClass:"text-center text-middle",
                            columns:[
                                {title:"{{ trans('general.name') }}", field:"previous_score_name", width: 250, cssClass:"text-center text-middle", headerSort:false, formatter: scoreFormatter, formatterParams: { column: 'previous_score_name' }},
                                {title:"{{ trans('vendorPerformanceEvaluation.score') }}", field:"previous_score", width: 80, cssClass:"text-center text-middle", headerSort:false, formatter: scoreFormatter, formatterParams: { column: 'previous_score' }},
                                {title:"{{ trans('vendorPerformanceEvaluation.applicability') }}", field:"previous_score_excluded", width: 120, cssClass:"text-center text-middle", headerSort:false, formatter: applicabilityFormatter, formatterParams: { column: 'previous_score_excluded' }},
                            ]
                        },{
                            title: "{{ trans('general.current') }}",
                            cssClass:"text-center text-middle",
                            columns:[
                                {title:"{{ trans('general.name') }}", field:"current_score_name", width: 250, cssClass:"text-center text-middle", headerSort:false, formatter: scoreFormatter, formatterParams: { column: 'current_score_name' }},
                                {title:"{{ trans('vendorPerformanceEvaluation.score') }}", field:"current_score", width: 80, cssClass:"text-center text-middle", headerSort:false, formatter: scoreFormatter, formatterParams: { column: 'current_score' }},
                                {title:"{{ trans('vendorPerformanceEvaluation.applicability') }}", field:"current_score_excluded", width: 120, cssClass:"text-center text-middle", headerSort:false, formatter: applicabilityFormatter, formatterParams: { column: 'current_score_excluded' }},
                            ]
                        }
                    ],
                    layout:"fitColumns",
                    ajaxURL: url,
                    movableColumns:true,
                    placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                    columnHeaderSortMulti:false,
                });
            });

            function getNestedRow(table, id)
            {
                // hackish way of getting a nested row since tabulator doesn't have a method for it.
                function traverseAndFind(rows, targetId)
                {
                    var row, resultFromChildSearch;

                    for(var i in rows)
                    {
                        row = rows[i];

                        if(row.getData()['id'] == targetId) return row;

                        if(row.getData()['hasScores']) continue;

                        resultFromChildSearch = traverseAndFind(row.getTreeChildren(), targetId);

                        if(resultFromChildSearch) return resultFromChildSearch;
                    }

                    return false;
                }

                return traverseAndFind(table.getRows(), id);
            }

            function noVerifier(e){
                var form = $(e.target).closest('form');
                var input = form.find(':input[name="verifiers[]"]').serializeArray();
                return !input.some(function(element){
                    return (element.value > 0);
                });
            }

            $('#btnSubmit').on('click', function(e) {
                if(noVerifier(e)) {
                    $('#warningModal').modal('show');
                    return false;
                }
            });

            $('#verifierForm button[name=approve], #verifierForm button[name=reject]').on('click', function(e) {
				e.preventDefault();

				if(this.name == 'reject') {
					$('#verifierRejectModal').modal('show');
				}

				if(this.name == 'approve') {
					$('#verifierApproveModal').modal('show');           
                }
			});

            $('#verifierApproveModal button[type="submit"]').on('click', function(e) {
                e.preventDefault();

                var input = $("<input>").attr("type", "hidden").attr("name", "approve").val(1);
                $('#verifierForm').append(input);

                var remarks = $('#verifierForm').append($("<input>").attr("type", "hidden").attr("name", "verifier_remarks").val($('#verifierApproveModal [name="verifier_remarks"]').val()));
                $('#verifierForm').append(remarks);

                $('#verifierForm').submit();
            });

            $('#verifierRejectModal button[type="submit"]').on('click', function(e) {
                e.preventDefault();

                var remarks = $('#verifierForm').append($("<input>").attr("type", "hidden").attr("name", "verifier_remarks").val($('#verifierRejectModal [name="verifier_remarks"]').val()));
                $('#verifierForm').append(remarks);

                $('#verifierForm').submit();
            });
        });
    </script>
@endsection