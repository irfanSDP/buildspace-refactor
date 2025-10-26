<?php namespace PCK\QueueJobs;

use Illuminate\Queue\Jobs\Job;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\SystemModules\SystemModuleConfiguration;
use PCK\Helpers\DBTransaction;
use PCK\Users\User;

class StartAndEndVendorPerformanceEvaluations {

    protected $transaction;
    protected $batchSize = 10;

    public function fire(Job $job, array $data)
    {
        $this->transaction = new DBTransaction();

        try
        {
            $this->startEvaluations();
            $this->endEvaluations();
            $this->endCycle();
        }
        catch(\Exception $e)
        {
            $this->transaction->rollback();
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());
        }

        $this->processNextBatch();

        return $job->delete();
    }

    protected function getEvaluationToStartCount()
    {
        return VendorPerformanceEvaluation::where('status_id', '=', VendorPerformanceEvaluation::STATUS_DRAFT)
            ->where('start_date', '<=', 'NOW()')
            ->count();
    }

    protected function getEvaluationToEndCount()
    {
        return VendorPerformanceEvaluation::where('status_id', '=', VendorPerformanceEvaluation::STATUS_IN_PROGRESS)
            ->where('end_date', '<=', 'NOW()')
            ->count();
    }

    protected function processNextBatch()
    {
        $processesLeft = $this->getEvaluationToStartCount() + $this->getEvaluationToEndCount();

        if(($processesLeft) > 0)
        {
            \Log::info("Queueing another batch of evaluations for processing. Number of processes left: {$processesLeft}");
            \Queue::push('PCK\QueueJobs\StartAndEndVendorPerformanceEvaluations', [], 'default');
        }
        else
        {
            \Log::info("No more processes left.");
        }
    }

    protected function startEvaluations()
    {
        $evaluationsToStart = VendorPerformanceEvaluation::where('status_id', '=', VendorPerformanceEvaluation::STATUS_DRAFT)
            ->where('start_date', '<=', 'NOW()')
            ->limit($this->batchSize)
            ->get();

        foreach($evaluationsToStart as $evaluation)
        {
            \Log::info("Starting Evaluation [id:{$evaluation->id}]");

            $this->transaction->begin();

            $evaluation->start();

            $this->transaction->commit();
        }
    }

    protected function endEvaluations()
    {
        $superAdminId = User::getSuperAdminIds()[0];
        \Auth::loginUsingId($superAdminId);

        $evaluationsToEnd = VendorPerformanceEvaluation::where('status_id', '=', VendorPerformanceEvaluation::STATUS_IN_PROGRESS)
            ->where('end_date', '<=', 'NOW()')
            ->limit($this->batchSize)
            ->get();

        foreach($evaluationsToEnd as $evaluation)
        {
            \Log::info("Ending Evaluation [id:{$evaluation->id}]");

            $this->transaction->begin();

            $evaluation->end();

            $this->transaction->commit();
        }
    }

    protected function endCycle()
    {
        if( $this->getEvaluationToEndCount() > 0 ) return;

        \Log::info('Ending Vendor Performance Evaluation Cycle');

        $this->transaction->begin();

        $cyclesToEnd = Cycle::where('is_completed', '=', false)
            ->where('end_date', '<=', 'NOW()')
            ->get();

        foreach($cyclesToEnd as $cycle)
        {
            $cycle->end();
        }

        $this->transaction->commit();

        \Log::info('Ended Vendor Performance Evaluation Cycle');

        foreach($cyclesToEnd as $cycle)
        {
            \Queue::push('PCK\QueueJobs\GenerateVendorEvaluationForms', array(
                'cycle_id' => $cycle->id,
            ),'default');
        }
    }
}