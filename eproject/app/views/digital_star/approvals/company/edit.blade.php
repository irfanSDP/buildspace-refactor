@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluation') }}}</li>
        @if($canAssignVerifiers)
            <li>{{ link_to_route('digital-star.approval.company.assign-verifiers.index', trans('forms.approval'), array()) }}</li>
        @endif
        @if($canApproveOrReject)
            <li>{{ link_to_route('digital-star.approval.company.approve.index', trans('forms.approval'), array()) }}</li>
        @endif
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
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $evaluationForm->weightedNode->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="well">
                        <div class="row">
                            <div class="col col-lg-6">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('companies.companyName') }}:</dt>
                                    <dd>{{{ $evaluationForm->evaluation->company->name }}}</dd>
                                </dl>
                            </div>
                            <div class="col col-lg-6">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('digitalStar/vendorManagement.form') }}:</dt>
                                    <dd>{{{ $evaluationForm->weightedNode->name }}}</dd>
                                    <dt>{{ trans('digitalStar/vendorManagement.status') }}:</dt>
                                    <dd>{{{ \PCK\DigitalStar\Evaluation\DsEvaluationForm::getStatusText($evaluationForm->status_id) }}}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    @if(!$evaluationForm->isCompleted() && !empty($remarks['processor']))
                        <div class="well border-danger text-danger">
                            {{{ $remarks['processor'] }}}
                        </div>
                        <br/>
                    @endif
                    <div id="main-table"></div>

                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label pull-right">{{ trans('digitalStar/digitalStar.totalScore') }} : {{ $vpeScore }} / 100</label>
                        </section>
                    </div>
                    {{ Form::open(array('route' => array('digital-star.approval.company.assign-verifiers.update', $evaluationForm->id), 'id' => 'form')) }}
                        <div class="row smart-form">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('digitalStar/vendorManagement.remarks') }}}:</label>
                                <label class="textarea">
                                    {{ Form::textArea('evaluator_remarks', $remarks['evaluator'], array('class' => 'fill-horizontal', 'rows' => 3, 'disabled' => 'disabled')) }}
                                </label>
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
                                'object' => $evaluationForm,
                            ])
                        </footer>
                    @endif
                    <div class="pull-left">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#evaluationLogModal">{{ trans('digitalStar/digitalStar.evaluationLog') }}</button>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#verifierLogsModal">{{ trans('verifiers.verifierLogs') }}</button>
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
    'modalId'    => 'evaluationLogModal',
    'title'      => trans('digitalStar/digitalStar.evaluationLog'),
    'tableId'    => 'evaluationLogTable',
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])
@include('uploads.downloadModal')
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var evaluationLogTable = null;

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
                    {title:"{{ trans('general.selected') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', field:'selected' },
                    {title:"{{ trans('general.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                            innerHtml: [
                                {
                                    show: function(cell){
                                        return cell.getData()['route:getDownloads'];
                                    },
                                    tag:'button',
                                    attributes: {type:'button', 'class':'btn btn-xs btn-default', 'data-toggle':'modal', 'data-target': '#downloadModal', 'data-action':'get-downloads'},
                                    rowAttributes: {'data-get-downloads':'route:getDownloads'},
                                    innerHtml:{
                                        tag:'i',
                                        attributes:{class:'fa fa-paperclip'}
                                    }
                                }
                            ]
                        }},
                ],
            });

            $('#evaluationLogModal').on('shown.bs.modal', function(e) {
                e.preventDefault();

                var evaluationLogTable = new Tabulator('#evaluationLogTable', {
                    height: 450,
                    ajaxConfig: "GET",
                    ajaxFiltering: true,
                    ajaxURL: "{{ route('digital-star.log.evaluation', [$evaluationForm->id]) }}",
                    placeholder: "{{ trans('general.noRecordsFound') }}",
                    paginationSize: 100,
                    pagination: "remote",
                    layout: "fitColumns",
                    columnHeaderSortMulti:false,
                    columns:[
                        { title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('digitalStar/digitalStar.actionBy') }}", field:'actionBy', minWidth:300, hozAlign:"left", headerSort:false, headerFilter:true },
                        { title:"{{ trans('digitalStar/digitalStar.actionType') }}", field:'actionType', width: 200, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                        { title:"{{ trans('digitalStar/digitalStar.actionDate') }}", field:'actionDate', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    ]
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