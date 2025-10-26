<?php namespace PCK\DigitalStar\QueueJobs;

use Illuminate\Queue\Jobs\Job;
use PCK\DigitalStar\Evaluation\DsCycle;
use PCK\DigitalStar\Evaluation\DsEvaluation;
use PCK\Helpers\DBTransaction;
use PCK\Users\User;

class StartAndEndDigitalStarCycles {

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
        return DsEvaluation::where('status_id', '=', DsEvaluation::STATUS_DRAFT)
            ->where('start_date', '<=', 'NOW()')
            ->count();
    }

    protected function getEvaluationToEndCount()
    {
        return DsEvaluation::where('status_id', '=', DsEvaluation::STATUS_IN_PROGRESS)
            ->where('end_date', '<=', 'NOW()')
            ->count();
    }

    protected function processNextBatch()
    {
        $processesLeft = $this->getEvaluationToStartCount() + $this->getEvaluationToEndCount();

        if (($processesLeft) > 0)
        {
            \Log::info("Queueing another batch of evaluations for processing. Number of processes left: {$processesLeft}");
            \Queue::push('PCK\DigitalStar\QueueJobs\StartAndEndDigitalStarCycles', [], 'default');
        }
        else
        {
            \Log::info("No more processes left.");
        }
    }

    protected function startEvaluations()
    {
        $evaluationsToStart = DsEvaluation::where('status_id', '=', DsEvaluation::STATUS_DRAFT)
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

        $evaluationsToEnd = DsEvaluation::where('status_id', '=', DsEvaluation::STATUS_IN_PROGRESS)
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

        \Log::info('Ending Digital Star Cycle');

        $this->transaction->begin();

        $cyclesToEnd = DsCycle::where('is_completed', '=', false)
            ->where('end_date', '<=', 'NOW()')
            ->get();

        foreach($cyclesToEnd as $cycle)
        {
            $cycle->end();
        }

        $this->transaction->commit();

        \Log::info('Ended Digital Star Cycle');

        /*foreach($cyclesToEnd as $cycle)
        {
            \Queue::push('PCK\QueueJobs\GenerateVendorEvaluationForms', array(
                'cycle_id' => $cycle->id,
            ),'default');
        }*/
    }
}