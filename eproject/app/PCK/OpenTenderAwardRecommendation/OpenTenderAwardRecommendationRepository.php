<?php namespace PCK\OpenTenderAwardRecommendation;

use Carbon\Carbon;
use Confide;
use DB;
use PCK\Projects\Project;
use PCK\Tenders\Tender;
use PCK\Users\User;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendation;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationReportEditLog;
use PCK\ContractGroups\Types\Role;
use PCK\Verifier\Verifier;
use PCK\CompanyProject\CompanyProject;

class OpenTenderAwardRecommendationRepository {

    public function findOrNew(Tender $tender)
    {
        $awardRecommendation = $tender->openTenderAwardRecommendtion;

        if($awardRecommendation)
        {
            return $awardRecommendation;
        }

        $awardRecommendation                  = new OpenTenderAwardRecommendation();
        $awardRecommendation->tender_id       = $tender->id;
        $awardRecommendation->created_by      = Confide::user()->id;
        $awardRecommendation->report_contents = $this->getPreviousTenderAwardRecommendationReportContents($tender);
        $awardRecommendation->save();

        // re-query the object
        return OpenTenderAwardRecommendation::find($awardRecommendation->id);
    }

    /**
     * gets the report contents of the award recommendation previous tender, provided that it is not approved
     * 
     * @param Tender $tender
     * @return String $contents
     */
    private function getPreviousTenderAwardRecommendationReportContents(Tender $tender)
    {
        if($tender->isFirstTender()) return '';

        $contents                    = '';
        $previousTender              = Tender::where('project_id', $tender->project->id)->where('count', $tender->count - 1)->first();
        $previousAwardRecommendation = $previousTender->openTenderAwardRecommendtion;

        if($previousAwardRecommendation && ( ! $previousAwardRecommendation->isApproved() ))
        {
            $contents = $previousAwardRecommendation->report_contents;
        }

        return $contents;
    }

    public function getAllAwardRecommendationsForProject(Project $project, Tender $currentTender)
    {
        $user    = \Confide::user();
        $records = [];

        foreach($project->tenders as $tender)
        {
            if(is_null($tender->openTenderAwardRecommendtion)) continue;

            $tenderName = $tender->getCurrentTenderNameByLocale($user->settings->language->code);

            if($tender->id == $project->latestTender->id)
            {
                $tenderName .= ' (current)';
            }
            else
            {
                if( ! $tender->openTenderAwardRecommendtion->isApproved() ) continue;
            }

            array_push($records, [
                'tender_id'   => $tender->id,
                'show_route'  => route('open_tender.award_recommendation.report.show', [$project->id, $tender->id]),
                'tender_name' => $tenderName,
                'disabled'    => ($tender->id == $currentTender->id),
            ]);
        }

        return $records;
    }

    private function createLog(OpenTenderAwardRecommendation $awardRecommendation) {
        $log = new OpenTenderAwardRecommendationReportEditLog();
        $log->open_tender_award_recommendation_id = $awardRecommendation->id;
        $log->user_id = \Confide::user()->id;
        $log->save();
    }

    public function getReportEditLogs($awardRecommendationId) {
        $awardRecommendation = OpenTenderAwardRecommendation::find($awardRecommendationId);
        $logs = $awardRecommendation->editLogs;
        $formattedLogs = [];
        
        foreach($logs as $log) {
            array_push($formattedLogs, $this->formatReportEditLogs($log));
        }

        return $formattedLogs;
    }

    private function formatReportEditLogs($log) {
        $user = User::find($log->user_id);
        $actionString = trans('openTenderAwardRecommendation.editedBy');

        return [
            'actionString' => $actionString,
            'user'         => $user->name,
            'updatedAt'    => $log->updated_at,
        ];
    }

    public function getVerifiers(Project $project) {
        $assignedGCD = $project->getAssignedGroups(
            [
                Role::INSTRUCTION_ISSUER,
                Role::CLAIM_VERIFIER,
                Role::CONSULTANT_1,
                Role::CONSULTANT_2,
                Role::CONSULTANT_3,
                Role::CONSULTANT_4,
                Role::CONSULTANT_5,
                Role::CONSULTANT_6,
                Role::CONSULTANT_7,
                Role::CONSULTANT_8,
                Role::CONSULTANT_9,
                Role::CONSULTANT_10,
                Role::CONSULTANT_11,
                Role::CONSULTANT_12,
                Role::CONSULTANT_13,
                Role::CONSULTANT_14,
                Role::CONSULTANT_15,
                Role::CONSULTANT_16,
                Role::CONSULTANT_17,
                Role::PROJECT_OWNER,
                Role::PROJECT_MANAGER,
                Role::CONTRACTOR
            ]
        );

        $isGCDAssignedToProject = !empty($assignedGCD);

        $role = $isGCDAssignedToProject ? Role::GROUP_CONTRACT : Role::PROJECT_OWNER;

        $company        = $project->getCompanyByGroup($role);
        $companyProject = CompanyProject::where('project_id', $project->id)->where('company_id', $company->id)->first();

        $assignedUserIds = \DB::table('contract_group_project_users')
                            ->where('contract_group_id', '=', $companyProject->contract_group_id)
                            ->where('project_id', '=', $project->id)
                            ->lists('user_id');

        $verifiers = [];

        foreach($company->getVerifierList($project, true) as $verifier)
        {
            if((!$verifier->isTopManagementVerifier()) && (!in_array($verifier->id, $assignedUserIds))) continue;

            array_push($verifiers, $verifier);
        }

        return $verifiers;
    }

    public function saveReportContent(Tender $tender, $reportContents)
    {
        $success             = true;
        $awardRecommendation = $tender->openTenderAwardRecommendtion;

        if($awardRecommendation->report_contents !== $reportContents)
        {
            $awardRecommendation->report_contents = $reportContents;
            $success                              = $awardRecommendation->save();

            $this->createLog($awardRecommendation);
        }

        return $success;
    }

    public function getPendingAwardRecommendation(User $user, $includeFutureTasks, Project $project = null)
    {
        $pendingAwardRecommendations = [];
        $proceed                     = false;

        if($project)
        {
            $tender              = $project->latestTender;
            $awardRecommendation = $tender->openTenderAwardRecommendtion;

            if(!$awardRecommendation) return [];

            $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $awardRecommendation) : Verifier::isCurrentVerifier($user, $awardRecommendation);

            if($proceed)
            {
                $previousVerifierRecord = Verifier::getPreviousVerifierRecord($awardRecommendation);
                $now                    = Carbon::now();
                $then                   = $previousVerifierRecord ? Carbon::parse($previousVerifierRecord->verified_at) : Carbon::parse($awardRecommendation->updated_at);

                array_push($pendingAwardRecommendations, [
                    'project_reference'        => $project->reference,
                    'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                    'project_id'               => $project->id,
                    'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                    'company_id'               => $project->business_unit_id,
                    'project_title'            => $project->title,
                    'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                    'module'                   => OpenTenderAwardRecommendation::AWARD_RECOMMENDATION_MODULE_NAME,
                    'days_pending'             => $then->diffInDays($now),
                    'tender_id'                => $tender->id,
                    'is_future_task'           => !(Verifier::isCurrentVerifier($user, $awardRecommendation)),
                    'route'                    => route('open_tender.award_recommendation.report.show', array($project->id, $tender->id))
                ]);
            }
        }
        else
        {
            $records = Verifier::where('verifier_id', $user->id)->where('object_type', OpenTenderAwardRecommendation::class)->get();

            foreach($records as $record)
            {
                $awardRecommendation = OpenTenderAwardRecommendation::find($record->object_id);
                $proceed             = $includeFutureTasks ? Verifier::isAVerifierInline($user, $awardRecommendation) : Verifier::isCurrentVerifier($user, $awardRecommendation);

                if($proceed)
                {
                    if(is_null($awardRecommendation->tender->project)) continue;

                    $previousVerifierRecord = Verifier::getPreviousVerifierRecord($awardRecommendation);
                    $now                    = Carbon::now();
                    $then                   = $previousVerifierRecord ? Carbon::parse($previousVerifierRecord->verified_at) : Carbon::parse($awardRecommendation->updated_at);
                    $project                = $awardRecommendation->tender->project;
                    $routeString            = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.show' : 'open_tender.award_recommendation.report.show';

                    array_push($pendingAwardRecommendations, [
                        'project_reference'        => $project->reference,
                        'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                        'project_id'               => $project->id,
                        'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                        'company_id'               => $project->business_unit_id,
                        'project_title'            => $project->title,
                        'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                        'module'                   => OpenTenderAwardRecommendation::AWARD_RECOMMENDATION_MODULE_NAME,
                        'days_pending'             => $then->diffInDays($now),
                        'tender_id'                => $awardRecommendation->tender->id,
                        'is_future_task'           => !(Verifier::isCurrentVerifier($user, $awardRecommendation)),
                        'route'                    => route($routeString, [$project->id, $awardRecommendation->tender->id]),
                    ]);
                }
            }
        }

        return $pendingAwardRecommendations;
    }

    public function getTableTags(Project $project, Tender $tender, $withPrefix = false)
    {
        $tags       = [];
        $allTenders = $project->tenders->reverse();

        array_push($tags, $withPrefix ? '@status_of_participants' : 'status_of_participants');

        foreach($allTenders as $t)
        {
            if($t->isFirstTender())
            {
                array_push($tags, $withPrefix ? '@original_tender_summary' : 'original_tender_summary');
            }
            else
            {
                array_push($tags, $this->formatTableTags($t->current_tender_name, $withPrefix));
            }
        }

        array_push($tags, $withPrefix ? '@pte_vs_award' : 'pte_vs_award');
        array_push($tags, $withPrefix ? '@budget_vs_award' : 'budget_vs_award');
        array_push($tags, $withPrefix ? '@contract_sum' : 'contract_sum');

        return $tags;
    }

    private function formatTableTags($value, $withPrefix)
    {
        $value = str_replace(' ', '_', $value);
        $value = strtolower($value);
        $value = $value . '_summary';

        if($withPrefix)
        {
            $value = '@' . $value;
        }
        
        return $value;
    }
}

