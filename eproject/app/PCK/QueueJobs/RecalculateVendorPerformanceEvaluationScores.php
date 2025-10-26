<?php namespace PCK\QueueJobs;

use Illuminate\Queue\Jobs\Job;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\CycleScore;
use PCK\VendorPerformanceEvaluation\EvaluationScore;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\Projects\Project;
use PCK\Vendor\Vendor;

class RecalculateVendorPerformanceEvaluationScores {

    public function fire(Job $job, array $data)
    {
        $workCategoryIds = $data['workCategoryIds'];

        sort($workCategoryIds);

        \Log::info("Recalculating Vendor Performance Evaluation Scores for Work Categories [ids:" . implode($workCategoryIds) . "]");

        try
        {
            $this->recalculateVendorEvaluationScores($workCategoryIds);

            $job->delete();

            \Log::info("Recalculated Vendor Performance Evaluation Scores for Work Categories [ids:" . implode($workCategoryIds) . "]");
        }
        catch(\Exception $e)
        {
            \Log::error("Recalculating Vendor Performance Evaluation Scores for Work Categories [ids:" . implode($workCategoryIds) . "] Message -> " . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }

        \Log::info("Ended update for Vendor Performance Evaluation Scores for Work Categories [ids:" . implode($workCategoryIds) . "]");
    }

    protected function recalculateVendorEvaluationScores($changedWorkCategoryIds)
    {
        $projectIdsOfAddedWorkCategories = Project::whereIn('work_category_id', $changedWorkCategoryIds)->lists('id');

        $affectedEvaluationCycleIds = VendorPerformanceEvaluation::whereIn('project_id', $projectIdsOfAddedWorkCategories)->lists('vendor_performance_evaluation_cycle_id');

        CycleScore::whereIn('vendor_performance_evaluation_cycle_id', $affectedEvaluationCycleIds)->delete();

        $affectedCompletedCycles = Cycle::whereIn('id', $affectedEvaluationCycleIds)->where('is_completed', '=', true)->orderBy('id')->get();

        foreach($affectedCompletedCycles as $completedCycle)
        {
            EvaluationScore::whereIn('vendor_performance_evaluation_id', $completedCycle->evaluations->lists('id'))->delete();

            foreach($completedCycle->evaluations as $evaluation)
            {
                $evaluation->generateScores();
            }

            $completedCycle->generateScores();
        }

        $latestCompletedCycle = $affectedCompletedCycles->last();

        if(!is_null($latestCompletedCycle)) $this->attachScoresToVendorRecords($latestCompletedCycle);

        $currentOngoingCycle = Cycle::whereIn('id', $affectedEvaluationCycleIds)->where('is_completed', '=', false)->first();

        if(!is_null($currentOngoingCycle))
        {
            EvaluationScore::whereIn('vendor_performance_evaluation_id', $currentOngoingCycle->evaluations->lists('id'))->delete();

            foreach($currentOngoingCycle->evaluations()->where('status_id', '=', VendorPerformanceEvaluation::STATUS_COMPLETED)->get() as $evaluation)
            {
                $evaluation->generateScores();
            }
        }
    }

    protected function attachScoresToVendorRecords($cycle)
    {
        $cycleScores = CycleScore::where('vendor_performance_evaluation_cycle_id', '=', $cycle->id)->get();

        foreach($cycleScores as $cycleScore)
        {
            Vendor::where('vendor_work_category_id', '=', $cycleScore->vendor_work_category_id)
                ->where('company_id', '=', $cycleScore->company_id)
                ->update(['vendor_evaluation_cycle_score_id' => $cycleScore->id]);
        }
    }
}