<?php

use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendation;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationRepository;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationTenderAnalysis\OpenTenderAwardRecommendationTenderAnalysisRepository;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationStatus as Status;
use PCK\Projects\Project;
use PCK\Tenders\Tender;
use PCK\ContractGroups\Types\Role;
use PCK\Verifier\Verifier;
use PCK\Filters\OpenTenderFilters;

class OpenTenderAwardRecommendationController extends \BaseController {
    
    private $awardRecRepo;
    private $awardRecTenderAnalysisRepo;

    public function __construct(OpenTenderAwardRecommendationRepository $awardRecRepo, OpenTenderAwardRecommendationTenderAnalysisRepository $awardRecTenderAnalysisRepo)
    {
        $this->awardRecRepo               = $awardRecRepo;
        $this->awardRecTenderAnalysisRepo = $awardRecTenderAnalysisRepo;
    }

    public function show(Project $project, $tenderId)
    {
        $user                = \Confide::user();
        $tender              = Tender::find($tenderId);
        $verifiers           = $this->awardRecRepo->getVerifiers($project);
        $awardRecommendation = $this->awardRecRepo->findOrNew($tender);
        $isEditor            = \Confide::user()->isEditor($project);

        $getAttachmentsRouteString      = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.attachment.get' : 'open_tender.award_recommendation.report.attachment.get';
        $attachmentsIndexRouteString    = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.attachment.index' : 'open_tender.award_recommendation.report.attachment.index';
        $tenderAnalysisIndexRouteString = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.tender_analysis_table.index' : 'open_tender.award_recommendation.report.tender_analysis_table.index';

        $canEdit = true;

        if($user->isTopManagementVerifier() && ! $user->hasCompanyProjectRole($project, OpenTenderFilters::accessRoles($project)))
        {
            $canEdit = false;
        }

        $data = [
            'user'                              => $user,
            'project'                           => $project,
            'tender'                            => $tender,
            'verifiers'                         => $verifiers,
            'awardRecommendation'               => $awardRecommendation,
            'isCurrentVerifier'                 => Verifier::isCurrentVerifier(\Confide::user(), $awardRecommendation),
            'allAnalysisTables'                 => $this->getAllTenderAnalysisTables($project, $tender),
            'isEditor'                          => $isEditor,
            'verifierRecords'                   => Verifier::getAssignedVerifierRecords($awardRecommendation, true),
            'allAwardRecommendationsForProject' => $this->awardRecRepo->getAllAwardRecommendationsForProject($project, $tender),
            'getAttachmentsRoute'               => route($getAttachmentsRouteString, [$project->id, $tender->id]),
            'attachmentsIndexRoute'             => route($attachmentsIndexRouteString, [$project->id, $tender->id]),
            'tenderAnalysisIndexRoute'          => route($tenderAnalysisIndexRouteString, [$project->id, $tender->id]),
            'canEdit'                           => $canEdit,
        ];

        return View::make('open_tender_award_recommendation.show', $data);
    }

    private function getAllTenderAnalysisTables(Project $project, Tender $tender)
    {
        $tagsWithTableData = [];
        $tableTags = $this->awardRecRepo->getTableTags($project, $tender, true);

        foreach($tableTags as $tag)
        {
            if($tag === '@status_of_participants')
            {
                $tagsWithTableData[$tag] = $this->awardRecTenderAnalysisRepo->getParticipantsDetails($project);
            }

            if($tag === '@pte_vs_award')
            {
                $tagsWithTableData[$tag] = $this->awardRecTenderAnalysisRepo->getBuildspaceBillDetails($project, $tender);
            }

            if($tag === '@budget_vs_award')
            {
                $tagsWithTableData[$tag] = $this->awardRecTenderAnalysisRepo->getBuildspaceBillDetails($project, $tender);
            }

            if($tag === '@contract_sum')
            {
                $tagsWithTableData[$tag] = $this->awardRecTenderAnalysisRepo->getBuildspaceBillDetails($project, $tender);
            }
        }

        foreach($project->tenders->reverse() as $t)
        {
            if($t->isFirstTender())
            {
                $tagsWithTableData['@original_tender_summary']['tendererDetails'] = $this->awardRecTenderAnalysisRepo->getOriginalTenderTendererDetails($project->firstTender);
                $tagsWithTableData['@original_tender_summary']['tenderSummary']   = $this->awardRecTenderAnalysisRepo->getTenderSummaryDetails($project->firstTender);
            }
            else
            {
                $previousTender = Tender::where('project_id', $project->id)->where('count', $t->count - 1)->first();
                $tagsWithTableData['@tender_resubmission_' . $t->count . '_summary']['tenderResubmissionData'] = $this->awardRecTenderAnalysisRepo->getTenderResubmissionTendererDetails($t, $previousTender);
                $tagsWithTableData['@tender_resubmission_' . $t->count . '_summary']['originalTenderSummary']  = $this->awardRecTenderAnalysisRepo->getTenderSummaryDetails($previousTender);
                $tagsWithTableData['@tender_resubmission_' . $t->count . '_summary']['revisedTenderSummary']   = $this->awardRecTenderAnalysisRepo->getTenderSummaryDetails($t);
            }
        }

        return $tagsWithTableData;
    }

    public function edit(Project $project, $tenderId)
    {
        $tender              = Tender::find($tenderId);
        $tableTags           = $this->awardRecRepo->getTableTags($project, $tender);
        $awardRecommendation = $tender->openTenderAwardRecommendtion;

        $data = [
            'project'               => $project,
            'tender'                => $tender,
            'tableTags'             => $tableTags,
            'awardRecommendation'   => $awardRecommendation ?? null,
        ];

        return View::make('open_tender_award_recommendation.edit', $data);
    }

    public function save($projectId, $tenderId)
    {
        $inputs = Input::all();
        $tender = Tender::find($tenderId);
        $currenctlySelectedTendererId = $tender->currently_selected_tenderer_id;
        $awardRecommendation = $tender->openTenderAwardRecommendtion;
        $success = $this->awardRecRepo->saveReportContent($tender, $inputs['report_contents']);

        return Response::json([
            'success' => $success
        ]);
    }

    public function getReport($project, $tenderId)
    {
        $tender = Tender::find($tenderId);

        return $tender->openTenderAwardRecommendtion->report_contents;
    }

    public function submit(Project $project, $tenderId)
    {
        $inputs              = Input::all();
        $verifierIds         = $inputs['verifiers'];
        $tender              = Tender::find($tenderId);
        $awardRecommendation = $tender->openTenderAwardRecommendtion;

        if(!array_filter($verifierIds))
        {
            // no verifiers, verification bypassed
            $awardRecommendation->status = Status::APPROVED;
        }
        else
        {
            $awardRecommendation->status                        = Status::SUBMITTED_FOR_APPROVAL;
            $awardRecommendation->submitted_for_verification_by = \Confide::user()->id;
        }

        $awardRecommendation->save();

        Verifier::setVerifiers($verifierIds, $awardRecommendation);
        Verifier::sendPendingNotification($awardRecommendation);

        return Redirect::route('open_tender.award_recommendation.report.show', [$project->id, $tender->id]);
    }

    public function getReportEditLogs(Project $project, $tenderId)
    {
        $inputs = Input::all();
        $awardRecommendationId = $inputs['awardRecommendationId'];

        $log = $this->awardRecRepo->getReportEditLogs($awardRecommendationId);

        foreach($log as $key => $logEntry)
        {
            $log[ $key ]['formattedDateTime'] = $project->getProjectTimeZoneTime($logEntry['updatedAt'])->format(\Config::get('dates.full_format'));
        }

        return $log;
    }
}

