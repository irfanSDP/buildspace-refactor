<?php namespace PCK\DigitalStar\Evaluation;

use Carbon\Carbon;
use PCK\VendorManagement\VendorManagementUserPermission;
use PCK\Verifier\Verifier;
use Illuminate\Database\Eloquent\Collection;


class DsEvaluationRepository
{
    public function getPendingApprovals($user, $includeFutureTasks)
    {
        $pendingList = [];

        $latestCycle = DsCycle::latestActive();

        if (is_null($latestCycle)) {    // No active cycle
            return new Collection($pendingList);
        }

        $records = Verifier::where('verifier_id', $user->id)->where('object_type', DsEvaluationForm::class)->get();
        if ($records->isEmpty()) {    // No pending approvals
            return new Collection($pendingList);
        }

        foreach($records as $record)
        {
            $formId = $record->object_id;

            $evaluationForm = DsEvaluationForm::select(
                    'ds_evaluation_forms.id AS form_id',
                    'ds_evaluation_forms.project_id as project_id',
                    'companies.id AS company_id',
                    'companies.name AS company_name',
                    'contract_group_categories.name as vendor_group'
                )
                ->join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
                ->join('companies', 'companies.id', '=', 'ds_evaluations.company_id')
                ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
                ->where('contract_group_categories.hidden', '=', false)
                ->where('ds_evaluations.ds_cycle_id', '=', $latestCycle->id)
                ->where('ds_evaluations.status_id', '=', DsEvaluation::STATUS_IN_PROGRESS)
                ->where('ds_evaluation_forms.status_id', '=', DsEvaluationForm::STATUS_PENDING_VERIFICATION)
                ->where('ds_evaluation_forms.id', '=', $formId)
                ->first();

            if (! $evaluationForm)
            {
                continue;
            }

            $project = null;
            if (! is_null($evaluationForm->project_id))
            {
                // Is project evaluation
                $project = $evaluationForm->project;
                if (! $project || ! is_null($project->deleted_at))
                {
                    // No project found / deleted
                    continue;
                }
            }

            $formObject = DsEvaluationForm::find($formId);
            $proceed  = $includeFutureTasks ? Verifier::isAVerifierInline($user, $formObject) : Verifier::isCurrentVerifier($user, $formObject);

            if ($proceed)
            {
                $previousVerifierRecord = Verifier::getPreviousVerifierRecord($formObject);
                $now                    = Carbon::now();
                $then                   = $previousVerifierRecord ? Carbon::parse($previousVerifierRecord->verified_at) : Carbon::parse($evaluationForm->updated_at);

                if (is_null($project))
                {   // Company Evaluation
                    $pendingList[] = [
                        'project_reference' => $evaluationForm->vendor_group,
                        'parent_project_reference' => null,
                        'project_id' => null,
                        'parent_project_id' => null,
                        'company_id' => $evaluationForm->company_id,
                        'project_title' => $evaluationForm->company_name,
                        'parent_project_title' => null,
                        'module' => trans('digitalStar/digitalStar.companyEvaluation'),
                        'days_pending' => $then->diffInDays($now),
                        'is_future_task' => !(Verifier::isCurrentVerifier($user, $formObject)),
                        'route' => route('digital-star.approval.company.approve.edit', [$evaluationForm->form_id]),
                    ];
                } else {    // Project Evaluation
                    $pendingList[] = [
                        'project_reference' => $project->reference,
                        'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                        'project_id' => $project->id,
                        'parent_project_id' => $project->isSubProject() ? $project->parentProject->id : null,
                        'company_id' => $project->business_unit_id,
                        'project_title' => $project->title,
                        'parent_project_title' => $project->isSubProject() ? $project->parentProject->title : null,
                        'module' => trans('digitalStar/digitalStar.projectEvaluation'),
                        'days_pending' => $then->diffInDays($now),
                        'is_future_task' => !(Verifier::isCurrentVerifier($user, $formObject)),
                        'route' => route('digital-star.approval.project.approve.edit', [$evaluationForm->form_id]),
                    ];
                }
            }
        }

        // sort by days pending descendingly
        uasort($pendingList, function($element1, $element2)
        {
            return $element2['days_pending'] <=> $element1['days_pending'];
        });

        return new Collection($pendingList);
    }

    private static function countPendingProcessing($userId, $cycleId, $slug, $isProject)
    {
        $query = DsRole::join('ds_evaluation_form_user_roles', 'ds_roles.id', '=', 'ds_evaluation_form_user_roles.ds_role_id')
            ->join('ds_evaluation_forms', 'ds_evaluation_forms.id', '=', 'ds_evaluation_form_user_roles.ds_evaluation_form_id')
            ->join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
            ->where('ds_evaluations.status_id', '=', DsEvaluation::STATUS_IN_PROGRESS)
            ->where('ds_evaluations.start_date', '<=', 'now()')
            ->where('ds_evaluations.ds_cycle_id', '=', $cycleId)
            ->where('ds_evaluation_form_user_roles.user_id', '=', $userId)
            ->where('ds_roles.slug', '=', $slug)
            ->where('ds_evaluation_forms.status_id', '=', DsEvaluationForm::STATUS_SUBMITTED);

        if ($isProject) {
            $query->whereNotNull('ds_evaluation_forms.project_id');
        } else {
            $query->whereNull('ds_evaluation_forms.project_id');
        }

        return $query->count();
    }

    private static function countPendingApprovals($userId, $cycleId, $isProject = false)
    {
        $evaluationFormClass = DsEvaluationForm::class;

        $forms = DsEvaluationForm::select('ds_evaluation_forms.id as form_id')
            ->join('verifiers', 'verifiers.object_id', '=', 'ds_evaluation_forms.id')
            ->join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
            ->where('ds_evaluations.status_id', '=', DsEvaluation::STATUS_IN_PROGRESS)
            ->where('ds_evaluations.start_date', '<=', 'now()')
            ->where('ds_evaluations.ds_cycle_id', '=', $cycleId)
            ->where('ds_evaluation_forms.status_id', '=', DsEvaluationForm::STATUS_PENDING_VERIFICATION)
            ->where('verifiers.object_type', '=', $evaluationFormClass)
            ->where('verifiers.verifier_id', '=', $userId)
            ->whereNull('verifiers.approved')
            ->whereNull('verifiers.deleted_at');

        if ($isProject) {
            $forms->whereNotNull('ds_evaluation_forms.project_id');
        } else {
            $forms->whereNull('ds_evaluation_forms.project_id');
        }

        $formIds = $forms->lists('form_id'); // Laravel 4.2

        $count = 0;

        foreach ($formIds as $formId) {
            $currentVerifier = Verifier::select('verifier_id')
                ->where('object_id', '=', $formId)
                ->where('object_type', '=', $evaluationFormClass)
                ->whereNull('approved')
                ->orderBy('sequence_number')
                ->first();

            if ($currentVerifier && $currentVerifier->verifier_id === $userId) {
                $count++;
            }
        }

        return $count;
    }

    public static function getPendingFormsCount()
    {
        $count = [
            'company-evaluator' => 0,
            'company-processor' => 0,
            'project-evaluator' => 0,
            'project-processor' => 0,   // Use this for project evaluator during assign verifier phase
            'company-verifier' => 0,
            'project-verifier' => 0,
        ];

        $latestCycle = DsCycle::latestActive();

        if (is_null($latestCycle)) return $count;

        $user = \Confide::user();
        if (is_null($user)) return $count;

        // Cycle / evaluation in progress
        $cycleInProgress = DsEvaluation::STATUS_IN_PROGRESS;

        // Company evaluator
        $isAdmin = $user->isGroupAdmin();
        if ($isAdmin)
        {   // User is company admin -> Check if any pending company evaluations
            $count['company-evaluator'] = DsEvaluationForm::join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
                ->where('ds_evaluations.status_id', '=', $cycleInProgress)
                ->where('ds_evaluations.start_date', '<=', 'now()')
                ->where('ds_evaluations.ds_cycle_id', '=', $latestCycle->id)
                ->where('ds_evaluations.company_id', '=', $user->company_id)
                ->where('ds_evaluation_forms.status_id', '=', DsEvaluationForm::STATUS_DRAFT)
                ->whereNull('ds_evaluation_forms.project_id')
                ->count();
        }

        // Other roles
        $userPermissions = [
            VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_COMPANY,    // a.k.a. Company processor
            VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT,
            VendorManagementUserPermission::TYPE_DIGITAL_STAR_VERIFIER_COMPANY,
            VendorManagementUserPermission::TYPE_DIGITAL_STAR_VERIFIER_PROJECT,
        ];

        foreach ($userPermissions as $permission)
        {
            $hasPermission = VendorManagementUserPermission::where('user_id', '=', $user->id)
                ->where('type', '=', $permission)
                ->exists();

            if ($hasPermission)
            {
                switch ($permission)
                {
                    case VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_COMPANY:   // Is company processor
                        // Count submitted forms which are pending for processing
                        $count['company-processor'] = self::countPendingProcessing($user->id, $latestCycle->id, 'company-processor', false);
                        break;

                    case VendorManagementUserPermission::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT:   // Is project evaluator
                        // Count forms which are pending for evaluation
                        $count['project-evaluator'] = DsRole::join('ds_evaluation_form_user_roles', 'ds_roles.id', '=', 'ds_evaluation_form_user_roles.ds_role_id')
                            ->join('ds_evaluation_forms', 'ds_evaluation_forms.id', '=', 'ds_evaluation_form_user_roles.ds_evaluation_form_id')
                            ->join('ds_evaluations', 'ds_evaluations.id', '=', 'ds_evaluation_forms.ds_evaluation_id')
                            ->where('ds_evaluations.status_id', '=', $cycleInProgress)
                            ->where('ds_evaluations.start_date', '<=', 'now()')
                            ->where('ds_evaluations.ds_cycle_id', '=', $latestCycle->id)
                            ->where('ds_evaluation_form_user_roles.user_id', '=', $user->id)
                            ->where('ds_roles.slug', '=', 'project-evaluator')
                            ->where('ds_evaluation_forms.status_id', '=', DsEvaluationForm::STATUS_DRAFT)
                            ->whereNotNull('ds_evaluation_forms.project_id')
                            ->count();

                        // The project evaluator role is also used for processing the form
                        $count['project-processor'] = self::countPendingProcessing($user->id, $latestCycle->id, 'project-evaluator', true);
                        break;

                    case VendorManagementUserPermission::TYPE_DIGITAL_STAR_VERIFIER_COMPANY:
                        $count['company-verifier'] = self::countPendingApprovals($user->id, $latestCycle->id, false);
                        break;

                    case VendorManagementUserPermission::TYPE_DIGITAL_STAR_VERIFIER_PROJECT:
                        $count['project-verifier'] = self::countPendingApprovals($user->id, $latestCycle->id, true);
                        break;
                }
            }
        }

        return $count;
    }
}