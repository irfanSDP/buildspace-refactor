<?php

use Carbon\Carbon;
use PCK\Projects\Project;
use PCK\CompanyProject\CompanyProject;
use PCK\Companies\Company;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluator;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationSetup;
use PCK\VendorPerformanceEvaluation\TemplateForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\WeightedNode\WeightedNode;
use PCK\WeightedNode\WeightedNodeScore;
use PCK\Forms\VendorPerformanceEvaluatorsForm;
use PCK\Notifications\EmailNotifier;
use PCK\Users\User;

class VendorPerformanceEvaluatorsController extends \BaseController {

    protected $vendorPerformanceEvaluatorsForm;

    public function __construct(VendorPerformanceEvaluatorsForm $vendorPerformanceEvaluatorsForm, EmailNotifier $emailNotifier)
    {
        $this->vendorPerformanceEvaluatorsForm = $vendorPerformanceEvaluatorsForm;
        $this->emailNotifier                   = $emailNotifier;
    }

    public function edit($evaluationId)
    {
        $user = \Confide::user();

        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $company = $user->getAssignedCompany($evaluation->project);

        $selectedEvaluatorIds = VendorPerformanceEvaluator::where('vendor_performance_evaluation_id', '=', $evaluation->id)
            ->where('company_id', '=', $company->id)
            ->lists('user_id');

        $data = [];

        foreach($company->getActiveUsers() as $user)
        {
            $data[] = [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ];

            $evaluatorIds[] = $user->id;
        }

        $selectedEvaluatorIds = Input::old('evaluator_ids') ?? $selectedEvaluatorIds;

        return View::make('vendor_performance_evaluation.evaluations.evaluators.edit', compact(
            'evaluation',
            'company',
            'data',
            'evaluatorIds',
            'selectedEvaluatorIds'
        ));
    }

    public function update($evaluationId)
    {
        $this->vendorPerformanceEvaluatorsForm->validate(Input::all());

        $user = \Confide::user();

        $evaluation = VendorPerformanceEvaluation::find($evaluationId);

        $company = $user->getAssignedCompany($evaluation->project);

        $evaluatorIds = VendorPerformanceEvaluator::where('vendor_performance_evaluation_id', '=', $evaluationId)
            ->where('company_id', '=', $company->id)
            ->lists('user_id');

        $idsToAssign   = array_diff(Input::get('evaluator_ids'), $evaluatorIds);
        $idsToUnassign = array_diff($evaluatorIds, Input::get('evaluator_ids'));

        foreach($idsToAssign as $userId)
        {
            VendorPerformanceEvaluator::create(array(
                'vendor_performance_evaluation_id' => $evaluationId,
                'company_id'                       => $company->id,
                'user_id'                          => $userId,
            ));
        }

        $newlyAssignedUsers = User::whereIn('id', $idsToAssign)->get();

        $this->emailNotifier->sendVpeUsersAssignedAsEvaluators($evaluation, $newlyAssignedUsers);

        VendorPerformanceEvaluator::where('vendor_performance_evaluation_id', '=', $evaluationId)
            ->where('company_id', '=', $company->id)
            ->whereIn('user_id', $idsToUnassign)
            ->delete();

        \Flash::success(trans('forms.saved'));

        return Redirect::back();
    }
}