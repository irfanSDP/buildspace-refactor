<?php namespace PCK\TendererTechnicalEvaluationInformation;

use Carbon\Carbon;
use PCK\Companies\Company;
use PCK\Tenders\Tender;
use PCK\Verifier\Verifier;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\TendererTechnicalEvaluationInformation\TechnicalEvaluation;

class TendererTechnicalEvaluationInformationRepository {

    /**
     * Returns a matching record,
     * or a new one if there are none.
     *
     * @param Company $company
     * @param Tender  $tender
     *
     * @return TendererTechnicalEvaluationInformation
     */
    public function findOrNew(Company $company, Tender $tender)
    {
        $record = TendererTechnicalEvaluationInformation::where('company_id', '=', $company->id)
            ->where('tender_id', '=', $tender->id)
            ->first();

        if( ! $record )
        {
            $record             = new TendererTechnicalEvaluationInformation();
            $record->company_id = $company->id;
            $record->tender_id  = $tender->id;
        }

        return $record;
    }

    /**
     * Updates the information.
     *
     * @param Company $company
     * @param Tender  $tender
     * @param         $input
     *
     * @return bool
     */
    public function update(Company $company, Tender $tender, $input)
    {
        $record = $this->findOrNew($company, $tender);

        $record->remarks = substr($input['remarks'], 0, 100);

        return $record->save();
    }

    /**
     * Returns remarks for all tenderers.
     *
     * @param Tender $tender
     * @param        $tenderers
     *
     * @return array
     */
    public function getRemarks(Tender $tender, $tenderers)
    {
        $remarks = array();

        foreach($tenderers as $tenderer)
        {
            $remarks[ $tenderer->id ] = $this->findOrNew($tenderer, $tender)->remarks;
        }

        return $remarks;
    }

    public function getPendingTechnicalEvaluationsByUser(User $user, Project $project = null)
    {
        $listOfProjects = [];

        if($project)
        {
            $latestTender = $project->latestTender;

            foreach($latestTender->technicalEvaluationVerifiers as $verifier)
            {
                if(($verifier->id === $user->id) && $latestTender->technicalEvaluationIsBeingValidated())
                {
                    array_push($listOfProjects, [
                        'project_reference'        => $project->reference,
                        'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                        'project_id'               => $project->id,
                        'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                        'company_id'               => $project->business_unit_id,
                        'project_title'            => $project->title,
                        'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                        'module'                   => TechnicalEvaluation::TECHNICAL_OPENING_MODULE_NAME,
                        'days_pending'             => TenderTechnicalEvaluationInformationUser::getDaysPending($latestTender->id, $user->id),
                        'tender_id'                => $latestTender->id,
                        'route'                    => route('projects.technicalEvaluation.accessToVerifierDecisionForm', array('projectId' => $project->id, 'tenderId' => $latestTender->id))
                    ]);
                }
            }
        }
        else
        {
            foreach($user->technicalEvaluations as $technicalEvaluation)
            {
                $project = $technicalEvaluation->project;

                if($project && $technicalEvaluation->technicalEvaluationIsBeingValidated())
                {
                    $latestTender = $project->latestTender;

                    array_push($listOfProjects, [
                        'project_reference'        => $project->reference,
                        'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                        'project_id'               => $project->id,
                        'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                        'company_id'               => $project->business_unit_id,
                        'project_title'            => $project->title,
                        'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                        'module'                   => TechnicalEvaluation::TECHNICAL_OPENING_MODULE_NAME,
                        'days_pending'             => TenderTechnicalEvaluationInformationUser::getDaysPending($technicalEvaluation->id, $user->id),
                        'tender_id'                => $latestTender->id,
                        'route'                    => route('projects.technicalEvaluation.accessToVerifierDecisionForm', array('projectId' => $project->id, 'tenderId' => $technicalEvaluation->id))
                    ]);
                }
            }
        }

        return $listOfProjects;
    }

    public function getPendingTechnicalAssessment(User $user, $includeFutureTasks, Project $project = null)
    {
        $pendingTechnicalAssessments = [];
        $proceed = false;

        if($project)
        {
            $tender = $project->latestTender;
            $techAssessment = $tender->technicalEvaluation;

            if(!$techAssessment) return [];
            
            $proceed = $includeFutureTasks ? Verifier::isAVerifierInline($user, $techAssessment) : Verifier::isCurrentVerifier($user, $techAssessment);

            if($proceed)
            {
                $previousVerifierRecord = Verifier::getPreviousVerifierRecord($techAssessment);
                $now                    = Carbon::now();
                $then                   = $previousVerifierRecord ? Carbon::parse($previousVerifierRecord->verified_at) : Carbon::parse($techAssessment->updated_at);

                array_push($pendingTechnicalAssessments, [
                    'project_reference'        => $project->reference,
                    'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                    'project_id'               => $project->id,
                    'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                    'company_id'               => $project->business_unit_id,
                    'project_title'            => $project->title,
                    'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                    'module'                   => TechnicalEvaluation::TECHNICAL_ASSESSMENT_MODULE_NAME,
                    'days_pending'             => $then->diffInDays($now),
                    'tender_id'                => $tender->id,
                    'is_future_task'           => !Verifier::isCurrentVerifier($user, $techAssessment),
                    'route'                    => route('technicalEvaluation.assessment.confirm', array($project->id, $tender->id)),
                ]);
            }
        }
        else
        {
            $records = Verifier::where('verifier_id', $user->id)->where('object_type', TechnicalEvaluation::class)->get();

            foreach($records as $record)
            {
                $techAssessment = TechnicalEvaluation::find($record->object_id);
                $proceed        = $includeFutureTasks ? Verifier::isAVerifierInline($user, $techAssessment) : Verifier::isCurrentVerifier($user, $techAssessment);
                
                if($proceed)
                {
                    if(is_null($techAssessment->tender->project)) continue;

                    $previousVerifierRecord = Verifier::getPreviousVerifierRecord($techAssessment);
                    $now                    = Carbon::now();
                    $then                   = $previousVerifierRecord ? Carbon::parse($previousVerifierRecord->verified_at) : Carbon::parse($techAssessment->updated_at);
                    $project                = $techAssessment->tender->project;

                    array_push($pendingTechnicalAssessments, [
                        'project_reference'        => $project->reference,
                        'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                        'project_id'               => $project->id,
                        'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                        'company_id'               => $project->business_unit_id,
                        'project_title'            => $project->title,
                        'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                        'module'                   => TechnicalEvaluation::TECHNICAL_ASSESSMENT_MODULE_NAME,
                        'days_pending'             => $then->diffInDays($now),
                        'tender_id'                => $techAssessment->tender->id,
                        'is_future_task'           => !Verifier::isCurrentVerifier($user, $techAssessment),
                        'route'                    => route('technicalEvaluation.assessment.confirm', array($techAssessment->tender->project->id, $techAssessment->tender->id)),
                    ]);
                }
            }
        }

        return $pendingTechnicalAssessments;
    }

    public function deshortlistTenderersByTender($tender)
    {
        TendererTechnicalEvaluationInformation::where('tender_id', $tender->id)->update(array('shortlisted' => false));
    }
}