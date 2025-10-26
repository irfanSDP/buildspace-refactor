<?php

use PCK\Projects\Project;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluator;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorPerformanceEvaluation\RemovalRequest;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUser;
use Carbon\Carbon;

class VendorPerformanceEvaluationsController extends \BaseController {

    public function index()
    {
        $user = \Confide::user();

        $relevantProjectIds = ContractGroupProjectUser::where('user_id', '=', $user->id)
            ->lists('project_id');

        $evaluationIds = VendorPerformanceEvaluationCompanyForm::whereIn('evaluator_company_id', $user->getAllCompanies()->lists('id'))
            ->join('vendor_performance_evaluations', 'vendor_performance_evaluations.id', '=', 'vendor_performance_evaluation_company_forms.vendor_performance_evaluation_id')
            ->join('projects', 'projects.id', '=', 'vendor_performance_evaluations.project_id')
            ->join('vendor_performance_evaluation_cycles', 'vendor_performance_evaluation_cycles.id', '=', 'vendor_performance_evaluations.vendor_performance_evaluation_cycle_id')
            ->where(function($query) use ($relevantProjectIds, $user) {
                $query->whereIn('projects.id', $relevantProjectIds);
                $query->orWhereIn('vendor_performance_evaluation_id', VendorPerformanceEvaluator::where('user_id', '=', $user->id)->lists('vendor_performance_evaluation_id'));
            })
            ->where('vendor_performance_evaluation_cycles.is_completed', '=', false)
            ->lists('vendor_performance_evaluation_id');

        $evaluations = VendorPerformanceEvaluation::whereIn('id', $evaluationIds)
            ->has('project')
            ->where('status_id', '=', VendorPerformanceEvaluation::STATUS_IN_PROGRESS)
            ->where('start_date', '<=', 'now()')
            ->orderBy('start_date')->get();

        $evaluationsWithRemovalRequests = RemovalRequest::whereIn('vendor_performance_evaluation_id', $evaluationIds)->lists('vendor_performance_evaluation_id');

        $data = [];

        foreach($evaluations as $key => $evaluation)
        {
            $isEvaluator = VendorPerformanceEvaluator::where('user_id', '=', $user->id)->where('vendor_performance_evaluation_id', '=', $evaluation->id)->exists();

            $isProjectEditor = false;

            if($company = $user->getAssignedCompany($evaluation->project))
            {
                $isProjectEditor = $company->isProjectEditor($evaluation->project, $user);
            }

            $data[] = [
                'counter'            => $key + 1,
                'id'                 => $evaluation->id,
                'title'              => $evaluation->project->title,
                'reference'          => $evaluation->project->reference,
                'business_unit'      => $evaluation->project->businessUnit->name,
                'statusText'         => VendorPerformanceEvaluation::getProjectStageName($evaluation->project_status_id),
                'start_date'         => Carbon::parse($evaluation->start_date)->format(\Config::get('dates.submitted_at')),
                'end_date'           => Carbon::parse($evaluation->end_date)->format(\Config::get('dates.submitted_at')),
                'status'             => VendorPerformanceEvaluation::getStatusText($evaluation->status_id),
                'route:forms'        => $isEvaluator ? route('vendorPerformanceEvaluation.evaluations.forms', array($evaluation->id)) : null,
                'route:evaluators'   => $isProjectEditor ? route('vendorPerformanceEvaluation.evaluations.evaluators.edit', array($evaluation->id)) : null,
                'route:remove'       => ($isProjectEditor & $isEvaluator) ? route('vendorPerformanceEvaluation.evaluations.removalRequest.create', array($evaluation->id)) : null,
                'removalRequestSent' => in_array($evaluation->id, $evaluationsWithRemovalRequests),
            ];
        }

        return View::make('vendor_performance_evaluation.evaluations.index', compact('data'));
    }
}