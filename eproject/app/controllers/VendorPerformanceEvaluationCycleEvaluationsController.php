<?php

use PCK\Projects\Project;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\Forms\VendorPerformanceEvaluationCycleForm;
use Carbon\Carbon;

class VendorPerformanceEvaluationCycleEvaluationsController extends \BaseController {

    public function index($cycleId)
    {
        $cycle = Cycle::find($cycleId);

        $evaluations = VendorPerformanceEvaluation::where('vendor_performance_evaluation_cycle_id', '=', $cycleId)->get();

        $data = [];

        foreach($evaluations as $evaluation)
        {
            $data[] = [
                'id'             => $evaluation->project->id,
                'title'          => $evaluation->project->title,
                'reference'      => $evaluation->project->reference,
                'business_unit'  => $evaluation->project->businessUnit->name,
                'statusText'     => VendorPerformanceEvaluation::getProjectStageName($evaluation->project_status_id),
                'start_date'     => Carbon::parse($evaluation->start_date)->format(\Config::get('dates.submitted_at')),
                'end_date'       => Carbon::parse($evaluation->end_date)->format(\Config::get('dates.submitted_at')),
                'status'         => VendorPerformanceEvaluation::getStatusText($evaluation->status_id),
                'route:vendors'  => route('vendorPerformanceEvaluation.cycles.evaluations.vendors.index', array($cycle->id, $evaluation->id)),
                'route:initiate' => $evaluation->isStatus(VendorPerformanceEvaluation::STATUS_DRAFT) ? route('vendorPerformanceEvaluation.cycles.evaluations.initiate.form', array($cycle->id, $evaluation->id)) : null,
                'route:delete'   => $evaluation->isStatus(VendorPerformanceEvaluation::STATUS_DRAFT) ? route('vendorPerformanceEvaluation.cycles.evaluations.destroy', array($cycle->id, $evaluation->id)) : null,
            ];
        }

        return View::make('vendor_performance_evaluation.cycles.evaluations.index', compact('cycle', 'data'));
    }

    public function destroy($cycleId, $evaluationId)
    {
        try
        {
            $evaluation = VendorPerformanceEvaluation::find($evaluationId);

            $evaluation->delete();

            \Flash::success(trans('vendorManagement.evaluationRemoved'));
        }
        catch(\Exception $e)
        {
            \Log::error($e->getMessage());

            \Flash::error(trans('general.somethingWentWrong'));
        }

        return Redirect::back();
    }
}