@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.show', $tender->current_tender_name, [$project->id, $tender->id]) }}</li>
        <li>{{ link_to_route('open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $tender->id]) }}</li>
        <li>{{ link_to_route('open_tender.award_recommendation.report.tender_analysis_table.index', 'Tender Analysis', [$project->id, $tender->id]) }}</li>
        <li>Status of Participants</li>
    </ol>
    @include('projects.partials.project_status')
@endsection
<?php use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus; ?>
@section('content')
    <article class="col-sm-12">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="page-title txt-color-bluedark">
                    <i class="fa fa-users fa-fw"></i>
                    {{ trans('openTenderAwardRecommendation.statusOfParticipants') }}
                </h1>
            </div>
        </div>
        <div class="row">
            <div class="jarviswidget " data-widget-editbutton="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-users"></i> </span>
                    <h2>{{ trans('openTenderAwardRecommendation.participants') }}</h2>
                </header>
                <div>
                    <div class="jarviswidget-editbox"></div>
                    <div class="widget-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <th class="text-middle text-center" width="64px;">{{ trans('openTenderAwardRecommendation.rank') }}</th>
                                    <th class="text-middle text-left">{{ trans('openTenderAwardRecommendation.tendererName') }}</th>
                                    <th class="text-middle text-center" width="160px;">{{ trans('openTenderAwardRecommendation.statusOfParticipant') }}</th>
                                </thead>
                                <tbody>
                                    <?php $number = 1; ?>
                                    @foreach ($participantsDetails as $detail)
                                        <tr>
                                            <td class="squeeze text-middle text-center">{{{ $number ++ }}}</td>
                                            <td lass="text-middle text-left">{{{ $detail['participantName'] }}}</td>
                                            <td class="text-middle text-center">{{{ $detail['commitmentStatus'] }}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </article>
@endsection