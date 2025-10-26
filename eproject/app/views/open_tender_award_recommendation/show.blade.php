@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        @if($user->getAssignedCompany($project))
            <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
            <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
            <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
            <li>{{ link_to_route('projects.openTender.show', $tender->current_tender_name, [$project->id, $tender->id]) }}</li>
            <li>{{ trans('openTenderAwardRecommendation.awardRecommendation') }}</li>
        @else
            <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
            <li>{{ trans('openTenderAwardRecommendation.awardRecommendation') }}</li>
        @endif
    </ol>
    @include('projects.partials.project_status')
@endsection
<?php use \PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationStatus as Status; ?>
<?php
    $allowEditReport            = in_array($awardRecommendation->status, [Status::EDITABLE]) && $isEditor && $canEdit;
    $allowSubmitForVerification = in_array($awardRecommendation->status, [Status::EDITABLE]) && $isEditor && $canEdit;
    $showUploadedFiles          = in_array($awardRecommendation->status, [Status::SUBMITTED_FOR_APPROVAL, Status::APPROVED]);

    function formatAmount($amount) {
        $formattedAmount = number_format(abs($amount), 2, '.', ',');
        return ($amount < 0) ? HTML::decode('<font class="invalid">(' . $formattedAmount . ')</font>') : $formattedAmount;
    }

    $reportEditLogsRoute = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.edit.logs.get' : 'open_tender.award_recommendation.report.edit.logs.get';
?>
@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-edit"></i> {{ trans('openTenderAwardRecommendation.awardRecommendationReport') }}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
            <div class="pull-right">
                @if (count($allAwardRecommendationsForProject) > 0)
                <div class="btn-group header-btn">
                    @include('open_tender_award_recommendation.partials.all_award_recommendations_menu')
                </div>
                @endif
                @if ($awardRecommendation)
                <div class="btn-group header-btn">
                    @include('open_tender_award_recommendation.partials.index_actions_menu')
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col col-sm-12">
            <form action="" class="form-group">
                <fieldset>
                    <div id="renderedReport">
                        <?php 
                            $isReportContentEmpty = is_null($awardRecommendation->report_contents) || ($awardRecommendation->report_contents == '');
                            $displayContent = $isReportContentEmpty ? trans('openTenderAwardRecommendation.reportContentPlaceholder') : $awardRecommendation->report_contents;
                        ?>
                        <div class="well">{{ $displayContent }}</div>
                    </div>
                </fieldset>
                <footer class="pull-right" style="padding:2px;margin-bottom:35px;">
                    @if ($allowEditReport)
                        {{ HTML::decode(link_to_route('open_tender.award_recommendation.report.edit', '<i class="fa fa-edit"></i> '.trans('openTenderAwardRecommendation.editReport'), [$project->id, $tender->id], ['class' => 'btn btn-primary'])) }}
                    @endif
                    @if ($awardRecommendation)
                        <button type="button" class="btn btn-success" id="btnViewReportEditLogs" data-toggle="modal" data-target="#openTenderAwardRecommendationReportEditLogModal"><i class="fa fa-search"></i> {{ trans('openTenderAwardRecommendation.viewReportEditLogs') }}</button>
                        <button type="button" class="btn btn-success" id="btnViewVerifierLogs" data-toggle="modal" data-target="#openTenderAwardRecommendationVerifierLogModal"><i class="fa fa-search"></i> {{ trans('openTenderAwardRecommendation.viewVerifierLogs') }}</button>
                    @endif
                </footer>
            </form>
        </div>
    </div>
    @if($showUploadedFiles || $allowSubmitForVerification)
        <div class="row">
            <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget well">
                    <div>
                        <div class="widget-body">
                            @if ($showUploadedFiles)
                                <div class="row">
                                    <section class="col col-xs-12 ">
                                        <table class="table table-bordered table-striped table-hover" id="uploadedFilesTable">
                                            <thead>
                                                <tr>
                                                    <th class="text-center occupy-min" style="background-color:#000;color:#FFF;">{{ trans('openTenderAwardRecommendation.fileName') }}</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </section>
                                </div>
                            @endif
                            @if ($allowSubmitForVerification)
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <form action="{{ route('open_tender.award_recommendation.report.verifiers.submit', [$project->id, $tender->id]) }}" id="selectVerifiersForm" method="POST" class="smart-form ">
                                        <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                                        <section id="select_verifiers_control" class="col-xs-4 col-md-4 col-lg-4">
                                            @include('verifiers.select_verifiers')
                                        </section>
                                    </form>
                                </section>
                            </div>
                            @endif
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <div class="pull-right">
                                    @if($user->getAssignedCompany($project))
                                        @include('verifiers.forum_navigation', array('object' => $tender->openTenderAwardRecommendtion))
                                    @endif
                                    @if ($isCurrentVerifier)
                                        @include('verifiers.approvalForm', ['object' => $tender->openTenderAwardRecommendtion])
                                    @endif

                                    @if ($allowSubmitForVerification)
                                    <button type="button" id="btnSubmitForApproval" class="btn btn-primary btn-sm" data-form-id="selectVerifiersForm"><i class="fa fa-save"></i> {{ trans('openTenderAwardRecommendation.submit') }}</button>
                                    @endif
                                    @if($user->getAssignedCompany($project))
                                        {{ link_to_route('projects.openTender.show', trans('forms.back'), [$project->id, $tender->id], ['class' => 'btn btn-default btn-sm']) }}
                                    @else
                                        {{ link_to_route('home.index', trans('forms.back'), [$project->id, $tender->id], ['class' => 'btn btn-default btn-sm']) }}
                                    @endif
                                    </div>
                                    @if ($isCurrentVerifier)
                                        @include('open_tender_award_recommendation.partials.award_recommendation_verifier_remark_modal')
                                    @endif
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    @endif

    @include('open_tender_award_recommendation.partials.award_recommendation_report_edit_log_modal')
    @include('open_tender_award_recommendation.partials.award_recommendation_verifier_log_modal')

    @include('open_tender_award_recommendation.partials.tables.status_of_participants_table', [
        'participantStatusDetails' => $allAnalysisTables['@status_of_participants']
    ])

    @include('open_tender_award_recommendation.partials.tables.original_tender_summary_table', [
        'originalTenderSummaryData' => $allAnalysisTables['@original_tender_summary']['tendererDetails'],
        'tenderSummary'             => $allAnalysisTables['@original_tender_summary']['tenderSummary']
    ])
    
    @foreach ($project->tenders->reverse() as $t)
        @if(!$t->isFirstTender())
            @include('open_tender_award_recommendation.partials.tables.tender_resubmission_summary_table', [
                'tenderResubmission'        => $t,
                'tenderResubmissionData'    => $allAnalysisTables['@tender_resubmission_' . $t->count . '_summary']['tenderResubmissionData'],
                'originalTenderSummary'     => $allAnalysisTables['@tender_resubmission_' . $t->count . '_summary']['originalTenderSummary'],
                'revisedTenderSummary'      => $allAnalysisTables['@tender_resubmission_' . $t->count . '_summary']['revisedTenderSummary'],
            ])
        @endif
    @endforeach

    @include('open_tender_award_recommendation.partials.tables.contract_sum_table', [
        'contractSum' => $allAnalysisTables['@pte_vs_award']
    ])
    @include('open_tender_award_recommendation.partials.tables.budget_vs_award_table', [
        'budgetVsAwardData' => $allAnalysisTables['@budget_vs_award']
    ])

    @include('open_tender_award_recommendation.partials.tables.pte_vs_award_table', [
        'pteVsAwardData' => $allAnalysisTables['@contract_sum']
    ])
    @include('templates.verifiers_required_modal')
    @include('templates.warning_modal', [
        'modalId' => 'remarksRequiredModal',
        'message' => trans('general.remarksRequired'),
    ])
@endsection

@section('js')
@if ($showUploadedFiles)
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
@endif
<script src="{{ asset('js/app/app.common.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {

        $('#verifierForm button[type=submit][name="approve"], button[type=submit][name="reject"]').on('click', function(e) {
            e.preventDefault();

            $('textarea[name=verifier_remark]').val("");

            var approveRejectInput = document.getElementById('approveRejectInput');
            if(approveRejectInput !== null) {
                approveRejectInput.remove();
            }

            var verificationLabel = '<span class="badge bg-color-red"> <i class="fa fa-times-circle"></i> {{trans('forms.reject')}} </span>';

            if($(this).prop('name') == 'approve') {
                approveRejectInput = document.createElement('input');
                approveRejectInput.setAttribute('id', 'approveRejectInput');
                approveRejectInput.setAttribute('type', 'hidden');
                approveRejectInput.setAttribute('name', 'approve');
                $('#verifierForm').append(approveRejectInput);

                verificationLabel = '<span class="badge bg-color-green"> <i class="fa fa-check-circle"></i> {{trans('forms.approve')}} </span>';
            }

            $('#verification-lbl').html(verificationLabel);
            $('#remark').data('action-type', $(this).prop('name'));

            $('#award_recommendation_verifier_remark_modal').modal('show');
        });

        $('#remark').on('click', function(e) {
            e.preventDefault();

            var remarks = DOMPurify.sanitize($('textarea[name=verifier_remark]').val()).trim();

            if(($(this).data('action-type') == 'reject') && (remarks == '')) {
                $('#remarksRequiredModal').modal('show');

                return false;
            }

            var verifier_remark_input = document.createElement('input');
            verifier_remark_input.setAttribute('type', 'hidden');
            verifier_remark_input.setAttribute('name', 'verifier_remarks');
            verifier_remark_input.setAttribute('value', remarks);

            $('#verifierForm').append(verifier_remark_input);
            $('#verifierForm').submit();
        });

        @if ($showUploadedFiles)
            $('#uploadedFilesTable').DataTable({
                "sDom": "tpi",
                "autoWidth" : false,
                scrollCollapse: true,
                bServerSide:true,
                "language": {
                    "infoFiltered": "",
                    "zeroRecords": "No files uploaded"
                },
                "sAjaxSource":"{{ $getAttachmentsRoute }}",
                "fnServerParams": function ( aoData ) {
                    aoData.push( { name: 'tenderId', value: "{{{ $tender->id }}}" } );
                },
                "aoColumnDefs": [{
                    "aTargets": [ 0 ],
                    "orderable": false,
                    "mData": function ( source, type, val ) {
                        return '<a href="' + source['download_route'] + '">' + source['fileName'] + '</a>';
                    },
                    "sClass": "text-left text-center text-nowrap squeeze"
                }]
            });
        @endif

        renderTables();

        $('#btnSubmitForApproval').on('click', function(e) {
            $('#selectVerifiersForm').submit();
        });

        $('#selectVerifiersForm').on('submit', function(e) {
            if(noVerifier(e)) {
                $('#verifiersRequiredModal').modal('show');

                return false;
            }
        });
    });

    function renderTables() {
        $('#renderedReport').html($('#renderedReport').html().replaceAll('@status_of_participants', $('#status_of_participants_table').html()));
        $('#renderedReport').html($('#renderedReport').html().replaceAll('@original_tender_summary', $('#original_tender_summary_table').html()));
        $('#renderedReport').html($('#renderedReport').html().replaceAll('@pte_vs_award', $('#pte_vs_award_table').html()));
        $('#renderedReport').html($('#renderedReport').html().replaceAll('@budget_vs_award', $('#budget_vs_award_table').html()));
        $('#renderedReport').html($('#renderedReport').html().replaceAll('@contract_sum', $('#contract_sum_table').html()));
        
        @foreach($project->tenders->reverse() as $t)
            @if(!$t->isFirstTender())
                $('#renderedReport').html($('#renderedReport').html().replaceAll('{{{ '@tender_resubmission_' . $t->count . '_summary' }}}', $('{{{ "#tender_resubmission_" . $t->count . "_summary_table" }}}').html()));
            @endif
        @endforeach
    }

    @if ($allowSubmitForVerification)
    function noVerifier(e) {
        var form = $(e.target).closest('form');
        var input = form.find(':input[name="verifiers[]"]').serializeArray();

        return !input.some(function(element){
            return (element.value > 0);
        });
    }
    @endif

    $('#openTenderAwardRecommendationReportEditLogModal').on('show.bs.modal', function (event) {
        var modal = $(this);
        modal.find('.modal-body ol').empty();
        modal.find('.message').empty();

        $.ajax({
            url: "{{ route($reportEditLogsRoute, [$project->id, $tender->id]) }}",
            data: {
                projectId: {{{ $project->id }}},
                awardRecommendationId: {{{ $awardRecommendation->id }}},
            },
            success: function(data) {
                var logEntry = "";
                var user = "";
                var logText = "";
                var updatedAt = "";

                if(data.length < 1) {
                    modal.find('.message').append('No changes have been made');
                } else {
                    for(var i = 0; i < data.length; i++) {
                        logText = '<span style="color:black">' + data[i].actionString + '</span> ';
                        user = '<span style="color:blue">' + data[i].user + '</span> ';
                        updatedAt = '<span style="color:red">' + data[i].formattedDateTime + '</span>';
                        logEntry = logText + user + updatedAt;
                        modal.find('.modal-body ol').append('<li>' + logEntry + '</li>');
                    }
                }
            }
        });
    });
</script>
@endsection