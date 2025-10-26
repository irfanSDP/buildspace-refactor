@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.index', trans('vendorManagement.evaluations'), array()) }}</li>
        <li>{{{ $evaluation->project->short_title }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.evaluations.forms', trans('forms.forms'), array($evaluation->id)) }}</li>
        <li>{{{ $companyForm->company->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}
        </h1>
    </div>
    @if($companyForm->isDraft())
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        <div class="btn-group pull-right header-btn">
            <div class="dropdown pull-right">
                <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
                <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
                    <li>
                        <a href="#" class="btn btn-block btn-md btn-info" data-toggle="modal" data-target="#additionalRemarksModal">
                            {{ trans('vendorManagement.requestFormChange') }}
                            <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="{{{ trans('vendorManagement.requestFormChangeTooltip') }}}"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $companyForm->company->name }}}</h2>
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
                    {{ $errors->first('evaluation_form_questions', '<em class="invalid">:message</em>') }}
                    {{ Form::model($companyForm, array('route' => array('vendorPerformanceEvaluation.evaluations.forms.update', $evaluation->id, $companyForm->id), 'id' => 'scores-form')) }}
                    <div id="main-table"></div>
                    <input type="hidden" name="submit_type">
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label pull-right">{{ trans('vendorPerformanceEvaluation.totalScore') }} : <span id="totalSelectedScore"></span> / 100</label>
                        </section>
                    </div>
                    <div class="row smart-form">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('vendorManagement.remarks') }}}:</label>
                            <label class="textarea">
                                {{ Form::textArea('evaluator_remarks', Input::old('evaluator_remarks'), array('class' => 'fill-horizontal', 'rows' => 3, 'placeholder' => trans('forms.anyAdditionalRemarks'))) }}
                            </label>
                        </section>
                    </div>
                    <section>
                        {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}
                        <label class="label">{{{ trans('companies.attachments') }}}:</label>

                        @include('file_uploads.partials.upload_file_modal')
                    </section>
                    {{ Form::close() }}
                    @if(!$readOnly)
                    <footer class="pull-right">
                        <button type="button" class="btn btn-success" data-action="save">{{ trans('forms.save') }}</button>
                        <button type="button" class="btn btn-primary" data-action="submit">{{ trans('forms.submit') }}</button>
                    </footer>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@include('vendor_performance_evaluation.evaluations.forms.partials.message_modal', [
    'title'      => trans('vendorManagement.requestFormChange'),
    'modalId'    => 'additionalRemarksModal',
    'textAreaId' => 'txtRemarks',
])
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var selectedIds    = {};
            var excludedIds    = {};
            var mainTable = new Tabulator('#main-table', {
                dataTree: true,
                dataTreeStartExpanded:true,
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                dataLoaded: function(data){
                    var preSelectedIds = {{ json_encode($nodeIdsBySelectedScoreIds) }};

                    for(var scoreId in preSelectedIds){
                        $('input[type=radio][data-score-id="'+scoreId+'"]').prop('checked', true);
                        selectedIds[preSelectedIds[scoreId]] = scoreId;
                    }

                    excludedIds = {{ json_encode($excludedIds) }};

                    var excludedRow;
                    for(var nodeId in excludedIds){
                        $('input[type=checkbox][data-node-id="'+nodeId+'"]').prop('checked', true);

                        excludedRow = getNestedRow(this, 'node-'+excludedIds[nodeId]);

                        if(excludedRow) excludedRow.treeCollapse();
                    }
                },
                renderComplete: function() {
                    $('#totalSelectedScore').text("{{ $vpeScore }}");
                },
                dataTreeRowExpanded:function(row, level){
                    if(excludedIds.hasOwnProperty(row.getData()['nodeId']))
                    {
                        $('input[type=checkbox][data-node-id="'+row.getData()['nodeId']+'"]').prop('checked', true);
                    }
                    else
                    {
                        $('input[type=checkbox][data-node-id="'+row.getData()['nodeId']+'"]').prop('checked', false);
                    }
                },
                dataTreeRowCollapsed:function(row, level){
                    if(excludedIds.hasOwnProperty(row.getData()['nodeId']))
                    {
                        $('input[type=checkbox][data-node-id="'+row.getData()['nodeId']+'"]').prop('checked', true);
                    }
                    else
                    {
                        $('input[type=checkbox][data-node-id="'+row.getData()['nodeId']+'"]').prop('checked', false);
                    }
                },
                columns:[
                    {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter: function(cell){
                        var description = cell.getData()['description'];

                        if(cell.getData()['type'] == 'node'){
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
                                    return '<input type="checkbox" name="exclude-'+rowData['nodeId']+'" data-action="exclude-node" data-id="'+rowData['id']+'" data-node-id="'+rowData['nodeId']+'" '+checked+' @if($readOnly)disabled @endif>';
                                }
                            }
                        }
                    }},
                    {title:"{{ trans('general.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", hozAlign:"center", headerSort:false},
                    {title:"{{ trans('general.selected') }}", width: 80, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: function(rowData){
                            if(rowData.hasOwnProperty('id')){
                                if(rowData['type'] == 'score')
                                {
                                    var checked = rowData['selected'] ? 'checked' : '';
                                    return '<input type="radio" name="'+rowData['nodeId']+'" data-action="select-option" data-score-id="'+rowData['id']+'" data-node-id="'+rowData['nodeId']+'" '+checked+' @if($readOnly)disabled @endif>';
                                }
                            }
                        }
                    }},
                ],
            });

            $('[data-action=save]').on('click', function(){
                $('#scores-form input[name=submit_type]').val('save');
                $('#scores-form').submit();
            });

            $('[data-action=submit]').on('click', function(){
                $('#scores-form input[name=submit_type]').val('submit');
                $('#scores-form').submit();
            });

            $('#additionalRemarksModal [data-action="sendEmailWithAdditionalRemarks"]').on('click', function(e) {
                e.preventDefault();

                var remarks = DOMPurify.sanitize($('#txtRemarks').val().trim());

                $.post("{{ route('vendorPerformanceEvaluation.evaluations.forms.changeRequest', array($evaluation->id, $companyForm->id)) }}", {
                    remarks: remarks,
                    _token: '{{ csrf_token() }}'
                })
                .done(function(data){
                    if(data.success){
                        SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('vendorManagement.sentFormChangeRequest')}}");
                        $('#additionalRemarksModal').modal('hide');
                    }
                })
                .fail(function(){
                    SmallErrorBox.refreshAndRetry();
                });
            });

            $('#main-table').on('click', 'input[data-action=select-option]', function(){
                if($(this).prop('checked'))
                {
                    selectedIds[$(this).data('node-id')] = $(this).data('score-id');

                    updateScore();
                }
            });

            $("#scores-form").submit(function(e) {
                $(this).find('[data-action=select-option]').remove();
                $(this).find('[data-action=exclude-node]').remove();

                for(var nodeId in selectedIds)
                {
                    var input =
                        $('<input>', {
                            'name': nodeId,
                            'type': 'hidden',
                            'value': selectedIds[nodeId]
                        });

                    $(this).append(input).appendTo('body');
                }

                for(var nodeId in excludedIds)
                {
                    var input =
                        $('<input>', {
                            'name': 'excluded_ids['+nodeId+']',
                            'type': 'hidden',
                            'value': excludedIds[nodeId]
                        });

                    $(this).append(input).appendTo('body');
                }
            });

            $('#main-table').on('change', 'input[data-action=exclude-node]', function(){
                var row = getNestedRow(mainTable, $(this).data('id'));

                if($(this).prop('checked'))
                {
                    excludedIds[$(this).data('node-id')] = $(this).data('node-id');

                    row.treeCollapse();
                }
                else
                {
                    delete excludedIds[$(this).data('node-id')];

                    row.treeExpand();
                }

                updateScore();
            });

            function updateScore()
            {
                var selectedScoreIds = {};
                var excludedScoreIds = {};

                for (const [key, value] of Object.entries(selectedIds)) {
                    if(key in excludedIds) continue;

                    selectedScoreIds[key] = value;
                }

                for (const [key, value] of Object.entries(excludedIds)) {
                    excludedScoreIds[key] = value;
                }

                $.ajax({
                    url: '{{{ route('vendorPerformanceEvaluation.evaluations.forms.vpe.live.score.get', [$evaluation->id, $companyForm->id]) }}}',
                    method: 'GET',
                    data: {
                        selectedScoreIds: selectedScoreIds,
                        excludedScoreIds: excludedScoreIds,
                    },
                    success: function (response) {
                        $('#totalSelectedScore').text(response.vpeScore);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
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
        });
    </script>
@endsection