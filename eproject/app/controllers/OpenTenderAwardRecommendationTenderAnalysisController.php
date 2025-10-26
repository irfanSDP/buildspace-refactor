<?php

use PCK\Projects\Project;
use PCK\Tenders\Tender;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendation;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationTenderAnalysis\OpenTenderAwardRecommendationTenderAnalysisRepository;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationStatus;

class OpenTenderAwardRecommendationTenderAnalysisController extends \BaseController {

    private $awardRecTenderAnalysisRepo;

    public function __construct(OpenTenderAwardRecommendationTenderAnalysisRepository $awardRecTenderAnalysisRepo) {
        $this->awardRecTenderAnalysisRepo = $awardRecTenderAnalysisRepo;
    }

    public function index(Project $project, $tenderId) {
        $user   = \Confide::user();
        $tender = Tender::find($tenderId);

        $data = [
            'user'                => $user,
            'project'             => $project,
            'tender'              => $tender,
            'awardRecommendation' => $tender->openTenderAwardRecommendtion,
        ];

        $projectHasTenderResubmission = $project->tenders->count() > 1;

        if($projectHasTenderResubmission) {
            $data['tenderResubmissions'] = $project->tenders->reverse()->reject(function($t) {
                return $t->count == 0; 
            });
        }

        return View::make('open_tender_award_recommendation.tender_analysis.index', $data);
    }

    public function getStatusOfParticipants(Project $project, $tenderId) {
        $tender = Tender::find($tenderId);
        $participantsDetails = $this->awardRecTenderAnalysisRepo->getParticipantsDetails($project);
        $data = [
            'project'               => $project,
            'tender'                => $tender,
            'participantsDetails'   => $participantsDetails,
        ];

        return View::make('open_tender_award_recommendation.tender_analysis.status_of_participants', $data);
    }

    public function getOriginalTenderSummary(Project $project, $tenderId) {
        $tender = Tender::find($tenderId);
        $tendererDetails = $this->awardRecTenderAnalysisRepo->getOriginalTenderTendererDetails($tender);
        $tenderSummary = $this->awardRecTenderAnalysisRepo->getTenderSummaryDetails($tender);
        $isEditor = \Confide::user()->isEditor($project);
        $data = [
            'project'                   => $project,
            'tender'                    => $tender,
            'tendererDetails'           => $tendererDetails,
            'tenderSummary'             => $tenderSummary,
            'isEditable'                => $project->latestTender->openTenderAwardRecommendtion->status == OpenTenderAwardRecommendationStatus::EDITABLE && $isEditor,
        ];

        return View::make('open_tender_award_recommendation.tender_analysis.original_tender_summary', $data);
    }

    public function getTenderResubmissionSummary(Project $project, $tenderId) {
        $tender = Tender::find($tenderId);
        $previousTender = Tender::where('project_id', $project->id)->where('count', $tender->count - 1)->first();
        $tendererDetails = $this->awardRecTenderAnalysisRepo->getTenderResubmissionTendererDetails($tender, $previousTender);
        $originalTenderSummary = $this->awardRecTenderAnalysisRepo->getTenderSummaryDetails($previousTender);
        $revisedTenderSummary = $this->awardRecTenderAnalysisRepo->getTenderSummaryDetails($tender);
        $isEditor = \Confide::user()->isEditor($project);
        $data = [
            'project'                   => $project,
            'tender'                    => $tender,
            'tendererDetails'           => $tendererDetails,
            'lowestTenderSum'           => $tendererDetails['lowestTenderSum'],
            'originalTenderSummary'     => $originalTenderSummary,
            'revisedTenderSummary'      => $revisedTenderSummary,
            'isEditable'                => $project->latestTender->openTenderAwardRecommendtion->status == OpenTenderAwardRecommendationStatus::EDITABLE && $isEditor,
        ];

        return View::make('open_tender_award_recommendation.tender_analysis.tender_resubmission_summary', $data);
    }

    public function updateConsultantEstimate(Project $project, $tenderId) {
        $inputs = Input::all();
        $tender = Tender::find($tenderId);
        
        $this->awardRecTenderAnalysisRepo->updateConsultantEstimate($tender, $inputs['consultant_estimate']);

        return Redirect::to(URL::previous());
    }

    public function updateBudget(Project $project, $tenderId) {
        $inputs = Input::all();
        $tender = Tender::find($tenderId);

        $this->awardRecTenderAnalysisRepo->updateBudget($tender, $inputs['budget']);

        return Redirect::to(URL::previous());
    }

    public function getPteVsAwardSummary(Project $project, $tenderId) {
        $tender = Tender::find($tenderId);
        $results = $this->awardRecTenderAnalysisRepo->getBuildspaceBillDetails($project, $tender);
        $isEditor = \Confide::user()->isEditor($project);
        $data = [
            'project'       => $project,
            'tender'        => $tender,
            'results'       => $results,
            'isEditable'    => $tender->openTenderAwardRecommendtion->status == OpenTenderAwardRecommendationStatus::EDITABLE && $isEditor,
        ];

        return View::make('open_tender_award_recommendation.tender_analysis.pte_vs_award', $data);
    }

    public function updatePteVsAwardSummary(Project $project, $tenderId) {
        $inputs = Input::all();
        unset($inputs['_token']);
        $tender = Tender::find($tenderId);
        
        $this->awardRecTenderAnalysisRepo->updatePteVsAwardSummary($tender, $inputs);

        return Redirect::to(URL::previous());
    }

    public function getBudgetVsAwardSummary(Project $project, $tenderId) {
        $tender = Tender::find($tenderId);
        $results = $this->awardRecTenderAnalysisRepo->getBuildspaceBillDetails($project, $tender);
        $isEditor = \Confide::user()->isEditor($project);
        $data = [
            'project'       => $project,
            'tender'        => $tender,
            'results'       => $results,
            'isEditable'    => $tender->openTenderAwardRecommendtion->status == OpenTenderAwardRecommendationStatus::EDITABLE && $isEditor,
        ];

        return View::make('open_tender_award_recommendation.tender_analysis.budget_vs_award', $data);
    }

    public function updateBudgetVsAwardSummary(Project $project, $tenderId) {
        $inputs = Input::all();
        unset($inputs['_token']);
        $tender = Tender::find($tenderId);

        $this->awardRecTenderAnalysisRepo->updateBudgetVsAwardSummary($tender, $inputs);

        return Redirect::to(URL::previous());
    }

    public function getContractSumSummary(Project $project, $tenderId) {
        $tender = Tender::find($tenderId);
        $results = $this->awardRecTenderAnalysisRepo->getBuildspaceBillDetails($project, $tender);
        $data = [
            'project'       => $project,
            'tender'        => $tender,
            'results'       => $results,
        ];

        return View::make('open_tender_award_recommendation.tender_analysis.contract_sum', $data);
    }

    public function getTenderAnalaysisEditLogs(Project $project, $tenderId)
    {
        $log = $this->awardRecTenderAnalysisRepo->getTenderAnalaysisEditLogs($project);

        foreach($log as $key => $logEntry)
        {
            $log[ $key ]['formattedDateTime'] = $project->getProjectTimeZoneTime($logEntry['updatedAt'])->format(\Config::get('dates.full_format'));
        }

        return $log;
    }
}