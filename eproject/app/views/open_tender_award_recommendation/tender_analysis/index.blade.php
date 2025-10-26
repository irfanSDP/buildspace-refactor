@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        @if($user->getAssignedCompany($project))
            <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
            <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
            <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
            <li>{{ link_to_route('projects.openTender.show', $tender->current_tender_name, [$project->id, $tender->id]) }}</li>
            <li>{{ link_to_route('open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $tender->id]) }}</li>
            <li>{{ trans('openTenderAwardRecommendation.tenderAnalysis') }}</li>
        @else
            <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
            <li>{{ link_to_route('topManagementVerifiers.open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $tender->id]) }}</li>
            <li>{{ trans('openTenderAwardRecommendation.tenderAnalysis') }}</li>
        @endif
    </ol>
    @include('projects.partials.project_status')
@endsection
<?php
$statusOfParticipantsTableRouteString      = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.statusOfParticipants' : 'open_tender.award_recommendation.report.tender_analysis_table.statusOfParticipants';
$originalTenderSummaryTableRouteString     = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.originalTenderSummary' : 'open_tender.award_recommendation.report.tender_analysis_table.originalTenderSummary';
$tenderResubmissionSummaryTableRouteString = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.tenderResubmissionSummary' : 'open_tender.award_recommendation.report.tender_analysis_table.tenderResubmissionSummary';
$pteVsAwardTableRouteString                = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.ptevsaward' : 'open_tender.award_recommendation.report.tender_analysis_table.ptevsaward';
$budgetVsAwardTableRouteString             = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.budgetvsaward' : 'open_tender.award_recommendation.report.tender_analysis_table.budgetvsaward';
$contractSumTableRouteString               = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.contractsum' : 'open_tender.award_recommendation.report.tender_analysis_table.contractsum';
$logsRouteString                           = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.logs.get' : 'open_tender.award_recommendation.report.tender_analysis_table.logs.get';
?>
@section('content')
    <article class="col-sm-12">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="page-title">
                    <i class="fa fa-file-alt fa-fw"></i>
                    {{ trans('openTenderAwardRecommendation.tenderAnalysisTables') }}
                </h1>
            </div>
        </div>
        <div class="jarviswidget well">
            <div>
                <div class="widget-body">
                    <table class="table ttable-hover">
                        <thead>
                            <tr>
                                <th class="text-middle text-center" style="width:64px;">No.</th>
                                <th class="text-middle text-left">{{trans('formOfTender.section')}}</th>
                                <th class="text-middle text-center" style="width:120px;">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-middle text-center">1</td>
                                <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.statusOfParticipants') }}</td>
                                <td class="text-middle text-center">
                                    <a href="{{ route($statusOfParticipantsTableRouteString, [$project->id, $tender->id]) }}"
                                        class="btn btn-warning btn-sm"><i class="fa fa-search"></i> {{ trans('forms.view')}}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-middle text-center">2</td>
                                <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.originalTenderSummary') }}</td>
                                <td class="text-middle text-center">
                                    <a href="{{ route($originalTenderSummaryTableRouteString, [$project->id, $project->firstTender->id]) }}"
                                        class="btn btn-warning btn-sm"><i class="fa fa-search"></i> {{ trans('forms.view')}}</a>
                                </td>
                            </tr>
                            <?php
                            $count = 3;
                            ?>
                            @if (isset($tenderResubmissions))
                                @foreach ($tenderResubmissions as $tenderResubmission)
                                    <tr>
                                        <td class="text-middle text-center">{{$count}}</td>
                                        <td class="text-middle text-left">{{{ $tenderResubmission->current_tender_name . ' ' . trans('openTenderAwardRecommendation.summary') }}}</td>
                                        <td class="text-middle text-center">
                                            <a href="{{ route($tenderResubmissionSummaryTableRouteString, [$project->id, $tenderResubmission->id]) }}"
                                                class="btn btn-warning btn-sm"><i class="fa fa-search"></i> {{ trans('forms.view') }}</a>
                                        </td>
                                    </tr>
                                    <?php $count++; ?>
                                @endforeach
                            @endif
                            <tr>
                                <td class="text-middle text-center">{{$count}}</td>
                                <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.pteVSaward') }}</td>
                                <td class="text-middle text-center">
                                    <a href="{{ route($pteVsAwardTableRouteString, [$project->id, $tender->id]) }}"
                                        class="btn btn-warning btn-sm"><i class="fa fa-search"></i> {{ trans('forms.view')}}</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-middle text-center">{{($count +=1)}}</td>
                                <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.budgetVSaward') }}</td>
                                <td class="text-middle text-center">
                                    <a href="{{ route($budgetVsAwardTableRouteString, [$project->id, $tender->id]) }}"
                                        class="btn btn-warning btn-sm"><i class="fa fa-search"></i> {{ trans('forms.view')}}</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-middle text-center">{{($count +=1)}}</td>
                                <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.contractSum') }}</td>
                                <td class="text-middle text-center">
                                    <a href="{{ route($contractSumTableRouteString, [$project->id, $tender->id]) }}"
                                        class="btn btn-warning btn-sm"><i class="fa fa-search"></i> {{ trans('forms.view')}}</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <footer class="pull-right" style="padding:2px">
                        <button type="button" id="showTenderAnalysisLog" data-toggle="modal" data-target="#openTenderAwardRecommendationTenderAnalysisTableEditLogModal"  class="btn btn-success" data-toggle="modal" data-target="#formOfTenderLogModal"><i class="fa fa-search"></i> {{ trans('openTenderAwardRecommendation.viewLogs') }}</button>
                    </footer>
                </div>
            </div>
        </div>
    </article>
    @include('open_tender_award_recommendation.partials.award_recommendation_tender_analysis_table_edit_log_modal')
@endsection

@section('js')
<script>
    $('#openTenderAwardRecommendationTenderAnalysisTableEditLogModal').on('show.bs.modal', function (event) {
        var modal = $(this);
        modal.find('.modal-body ol').empty();
        modal.find('.message').empty();

        $.ajax({
            url: "{{ route($logsRouteString, [$project->id, $tender->id]) }}",
            data: {
                projectId: {{{ $project->id }}},
                awardRecommendationId: {{{ $awardRecommendation->id }}},
            },
            success: function(data){
                var logEntry = "";
                var user = "";
                var action = "";
                var tableName = "";
                var type = "";
                var updatedAt = "";

                if(data.length < 1)
                {
                    modal.find('.message').append('No changes have been made');
                }
                for(dataIndex in data)
                {
                    user = '<span style="color:blue">' + data[dataIndex].user + '</span> ';
                    action = '<span style="color:black">' + "{{ trans('openTenderAwardRecommendation.updated') }}" + '</span> ';
                    tableName = '<span style="color:blue">' + data[dataIndex].tableName + '</span> ';
                    type = '<span style="color:blue">' + data[dataIndex].type + '</span> ';
                    updatedAt = '<span style="color:red">' + data[dataIndex].formattedDateTime + '</span>';
                    logEntry = user + action + type + "{{ trans('openTenderAwardRecommendation.in') }} " + tableName + "{{ trans('openTenderAwardRecommendation.table') }} " + "{{ trans('openTenderAwardRecommendation.on') }} " + updatedAt;
                    modal.find('.modal-body ol').append('<li>' + logEntry + '</li>');
                }
            }
        });
    });
</script>
@endsection