@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('digital-star.evaluation.project.index', trans('digitalStar/vendorManagement.evaluations'), array()) }}</li>
        <li>{{{ $evaluatedCompany->name }}}</li>
        <li>{{ link_to_route('digital-star.evaluation.project.edit', trans('digitalStar/digitalStar.projectEvaluation'), array($evaluation->id, $evaluation->company_id, $evaluationForm->id)) }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{ trans('digitalStar/vendorManagement.vendorPerformanceEvaluation') }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{ trans('digitalStar/digitalStar.projectEvaluation') }}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="well">
                        <div class="row">
                            <div class="col col-lg-6">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('companies.companyName') }}:</dt>
                                    <dd>{{ $evaluatedCompany->name }}</dd>
                                    <dt>{{ trans('digitalStar/digitalStar.project') }}:</dt>
                                    <dd>{{ $project->title }}</dd>
                                </dl>
                            </div>
                            <div class="col col-lg-6">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('digitalStar/vendorManagement.form') }}:</dt>
                                    <dd>{{ $evaluationForm->weightedNode->name }}</dd>
                                    <dt>{{ trans('digitalStar/vendorManagement.status') }}:</dt>
                                    <dd>{{ \PCK\DigitalStar\Evaluation\DsEvaluationForm::getStatusText($evaluationForm->status_id) }}</dd>
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
                    {{ $errors->first('evaluation_form_questions', '<em class="invalid">:message</em>') }}
                    {{ Form::model($evaluationForm, array('route' => array('digital-star.evaluation.project.update', $evaluation->id, $evaluationForm->id), 'id' => 'scores-form')) }}
                        <div id="main-table"></div>
                        <input type="hidden" name="submit_type">

                        <div class="row smart-form">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('digitalStar/vendorManagement.remarks') }}}:</label>
                                <label class="textarea">
                                    {{ Form::textArea('evaluator_remarks', Input::old('evaluator_remarks') ?? $remarks['evaluator'], array('class' => 'fill-horizontal', 'rows' => 3, 'placeholder' => trans('forms.anyAdditionalRemarks'))) }}
                                </label>
                            </section>
                        </div>
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
@include('uploads.uploadModal')
@include('uploads.downloadModal')
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
                    //$('#totalSelectedScore').text("{{ $score }}");
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
                    {title:"{{ trans('general.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                show: function(cell){
                                    return cell.getData()['route:doUpload'];
                                },
                                tag:'button',
                                attributes: {type:'button', 'class':'btn btn-xs btn-warning', 'data-action':'upload-item-attachments'},
                                rowAttributes: {'data-do-upload':'route:doUpload', 'data-get-uploads':'route:getUploads'},
                                innerHtml:{
                                    tag:'i',
                                    attributes:{class:'fa fa-paperclip'}
                                },
                            },{
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

            $('[data-action=save]').on('click', function(){
                $('#scores-form input[name=submit_type]').val('save');
                $('#scores-form').submit();
            });

            $('[data-action=submit]').on('click', function(){
                $('#scores-form input[name=submit_type]').val('submit');
                $('#scores-form').submit();
            });

            $('#main-table').on('click', 'input[data-action=select-option]', function(){
                if($(this).prop('checked'))
                {
                    selectedIds[$(this).data('node-id')] = $(this).data('score-id');

                    //updateScore();
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

                //updateScore();
            });

            /*function updateScore()
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
                    url: '{{{ route('digital-star.evaluation.company.score', [$evaluation->id, $evaluationForm->id]) }}}',
                    method: 'GET',
                    data: {
                        selectedScoreIds: selectedScoreIds,
                        excludedScoreIds: excludedScoreIds,
                    },
                    success: function (response) {
                        $('#totalSelectedScore').text(response.score);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }*/

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