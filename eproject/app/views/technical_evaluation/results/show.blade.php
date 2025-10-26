@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('technicalEvaluation.results.index', trans('technicalEvaluation.technicalEvaluationResults'), array($project->id)) }}</li>
        <li>{{{ $tender->current_tender_name }}}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
<?php $isSubmittedForApproval = !is_null($submitter); ?>
<div id="technicalEvaluation">
    <?php use \PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption as Option; ?>
    <?php use \PCK\Verifier\Verifier;?>
    <?php
        $technicalAssessmentApproved = null;
        if($tender->technicalEvaluation) {
            $technicalAssessmentApproved = Verifier::isApproved($tender->technicalEvaluation);
        }
    ?>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                <h1 class="page-title txt-color-blueDark">
                    <i class="fa fa-users"></i> {{ trans('technicalEvaluation.technicalEvaluationResults') }}
                </h1>
            </div>
            @if ($isProjectOwnerOrGCD)
                <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                    <button class="btn btn-primary pull-right" id="btnTechnicalAssessmentForm" type="submit" disabled>{{ trans('tenders.technicalAssessmentForm') }}</button>
                </div>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{ trans('technicalEvaluation.tenderers') }}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div id="tenderers-table"></div>
                    </div>
                    @if($project->showTechnicalEvaluationDetails($tender))
                        <div class="widget-footer">
                            <a href="{{ route('technicalEvaluation.results.summary', array($project->id, $tender->id)) }}" class="btn btn-primary">
                                <i class="fa fa-chart-bar"></i>
                                {{ trans('technicalEvaluation.report') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('technical_evaluation.partials.remarks_modal')

    @include('technical_evaluation.form_modal', [
        'editable' => ($currentUser->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::getRolesExcept(\PCK\ContractGroups\Types\Role::CONTRACTOR)) && $currentUser->isEditor($project) && !$isSubmittedForApproval && !$technicalAssessmentApproved)
    ])

    @foreach($tenderers as $tenderer)
        @include('templates.log_modal', array(
            'log' => $technicalEvaluationInfo[$tenderer->id]->log,
            'modalId' => 'logModal'.$tenderer->id,
            'title' => trans('technicalEvaluation.remarksLog'),
        ))
    @endforeach

    @include('technical_evaluation.attachments.download_modal', array(
        'modalId' => 'technicalEvaluationAttachmentDownloadModal',
        'company' => 'remove',
        'setReference' => $setReference,
    ))
</div>

@endsection

@section('js')
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script>
        var tenderersTable = new Tabulator('#tenderers-table', {
            height:450,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL: "{{ route('technicalEvaluation.results.show.tenderers', [$project->id, $tender->id]) }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            dataLoaded: function(data){
                var selectedIds = [];
                data.forEach(function(row){
                    if(row['shortlisted']) selectedIds.push(row['id']);
                });
                tenderersTable.selectRow(selectedIds);

                $('#btnTechnicalAssessmentForm').prop('disabled', tenderersTable.getSelectedRows().length < 1);
            },
            @if($canSelectTenderer = ($project->showTechnicalEvaluationDetails($tender) && !$isSubmittedForApproval && !$technicalAssessmentApproved && $isProjectOwnerOrGCD))
            cellClick:function(e, cell){
                var selectTriggerFields = ['rowselect', 'counter', 'name', 'submitted_at', 'score'];
                if(selectTriggerFields.includes(cell.getField())){
                    cell.getRow().toggleSelect();
                }
            },
            rowSelected:function(row){
                updateTendererSelection(row.getData()['id'], true);
            },
            rowDeselected:function(row){
                updateTendererSelection(row.getData()['id'], false);
            },
            @endif
            columns:[
                @if($canSelectTenderer)
                {formatter:"rowSelection",field:"rowselect", titleFormatter:"rowSelection", width:40, cssClass:"text-center text-top", headerSort:false},
                @endif
                {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('technicalEvaluation.tenderer') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}"},
                {title:"{{ trans('forms.submittedAt') }}", field:"submitted_at", width: 150, cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('forms.form') }}", width: 70, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: {
                        tag: 'button',
                        rowAttributes: {'data-id':'id'},
                        attributes: {"data-action":"form", type:'button', class:'btn btn-xs btn-primary text-white', title: '{{ trans("technicalEvaluation.technicalEvaluationForm") }}'},
                        innerHtml:{
                            tag: 'i',
                            attributes: {class: 'fa fa-clipboard-list'}
                        }
                    }
                }},
                @if($project->showTechnicalEvaluationDetails($tender))
                {title:"{{ trans('technicalEvaluation.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('technicalEvaluation.remarks') }}", field:"remarks", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: [
                        {
                            tag: 'button',
                            rowAttributes: {'data-id':'id'},
                            attributes: {"data-action":"showRemarksModal", type:'button', class:'btn btn-xs btn-default', title: '{{ trans("forms.edit") }}'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-edit'}
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            innerHtml: function(rowData){
                                return rowData['remarks'];
                            }
                        }
                    ]
                }},
                {title:"{{ trans('technicalEvaluation.attachments') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: [
                        {
                            tag: 'button',
                            rowAttributes: {'data-id':'id'},
                            attributes: {"data-action":"attachments", type:'button', class:'btn btn-xs btn-warning text-white', title: '{{ trans("technicalEvaluation.attachments") }}'},
                            innerHtml: [
                                {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-paperclip'}
                                },{
                                    innerHtml: function(){
                                        return '&nbsp;';
                                    }
                                },{
                                    innerHtml: function(rowData){
                                        return rowData['attachments_count'];
                                    }
                                },
                            ]
                        }
                    ]
                }}
                @endif
            ],
        });

        var vue = new Vue({
            el: '#technicalEvaluation',
            data: {
                remarks: '',
                tendererId: '',
                remarkLogModalId: ''
            },
            methods: {
                updateRemarks: function(){
                    var submitButton = $('button[data-action=update-remarks]' );
                    submitButton.prop('disabled', true);
                    app_progressBar.toggle();
                    $.ajax({
                        url: '{{{ route('technicalEvaluation.remarks.update', array($project->id, $tender->id)) }}}',
                        method: 'POST',
                        data: {
                            _token: '{{{ csrf_token() }}}',
                            tenderer_id: vue.tendererId,
                            remarks: vue.remarks
                        },
                        success: function (data) {
                            if (data['success']) {
                                app_progressBar.maxOut(0, function(){
                                    tenderersTable.setData();
                                    $('#remarksModal' ).modal('hide');
                                    app_progressBar.hide();
                                });
                            }
                            submitButton.prop('disabled', false);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            // error
                        }
                    });
                }
            }
        });

        $('#tenderers-table').on('click', "[data-action=showRemarksModal]", function() {
            var id = $(this).data('id');
            var data = tenderersTable.getRow(id).getData();
            var remarks = data['remarks'];
            $('#remarksModal' ).modal('show');

            vue.tendererId = id;
            vue.remarks = remarks;
            $('#remarks-input' ).select();

            vue.remarkLogModalId = 'logModal' + vue.tendererId;
        });

        $(document).on("click", '#btnTechnicalAssessmentForm', function(e) {
            e.preventDefault();
            var url = '{{  route('technicalEvaluation.assessment.confirm', array($project->id, $tender->id)) }}';
            window.location.href = url;
        });

        function updateTendererSelection(tendererId, selected){
            var url = "{{ route('technicalEvaluation.tenderer.save', array($project->id, $tender->id)) }}";
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    tenderer: tendererId,
                    status: selected
                },
                success: function(data) {
                    tenderersTable.setData();
                    $('#btnTechnicalAssessmentForm').prop('disabled', tenderersTable.getSelectedRows().length < 1);
                }
            });
        }

        $('#tenderers-table').on('click', '[data-action=form]', function(){
            $.get("{{ route('technicalEvaluation.results.show.tenderers.formResponses', [$project->id, $tender->id]) }}?id="+$(this).data('id'), function(data){
                formModal.init(data);
            });
        });

        $('#tenderers-table').on('click', '[data-action=attachments]', function(){
            var rowData = tenderersTable.getRow($(this).data('id')).getData();
            $('#technicalEvaluationAttachmentDownloadModal [data-id=company-name]').html(rowData['name']);
            var attachmentsTable = Tabulator.prototype.findTable("#technicalEvaluationAttachmentDownloadModal [data-id=attachments-table]")[0];
            attachmentsTable.setData(rowData['route:attachments']);
            $('#technicalEvaluationAttachmentDownloadModal a[data-action=download-all]').hide();
            if(rowData['attachments_count'] > 0) $('#technicalEvaluationAttachmentDownloadModal a[data-action=download-all]').show();
            $('#technicalEvaluationAttachmentDownloadModal [data-action=download-all]').prop('href', rowData['route:download_all']);
            $('#technicalEvaluationAttachmentDownloadModal').modal('show');
        });
    </script>
@endsection