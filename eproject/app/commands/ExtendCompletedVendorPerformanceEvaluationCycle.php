<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use PCK\Companies\Company;
use Carbon\Carbon;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\Vendor\Vendor;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExtendCompletedVendorPerformanceEvaluationCycle extends Command {

    protected $cycle;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'vendor-management:extend-completed-vendor-performance-evaluation-cycle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extends the latest completed evaluation cycle. All scores for the cycle will be removed.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->validate();

        $transaction = new \PCK\Helpers\DBTransaction();
        $transaction->begin();

        try
        {
            $this->extend();

            $transaction->commit();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }

    public function validate()
    {
        if(Cycle::count() > 1) throw new Exception("There's more than one cycle.");

        $this->cycle = Cycle::orderBy('id', 'desc')->first();

        if(!$this->cycle->is_completed) throw new Exception("Cycle is still in progress.");
    }

    public function extend()
    {
        $endDate = \Carbon\Carbon::now()->addWeeks(2);

        \DB::statement("UPDATE vendor_performance_evaluation_cycles SET end_date = '{$endDate}', is_completed = FALSE WHERE id = {$this->cycle->id}");
        \DB::statement("UPDATE vendor_performance_evaluations SET end_date = '{$endDate}' WHERE vendor_performance_evaluation_cycle_id = {$this->cycle->id}");

        $this->processCompanies();
        $this->removeCycleScores();
        $this->removeEvaluationScores();
        $this->updateEvaluationStatus();
    }

    protected function processCompanies()
    {
        // Move all NWL vendors to AL.
        Vendor::where('type', '=', Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_EVALUATION)->whereNotNull('vendor_evaluation_cycle_score_id')->update(['type' => Vendor::TYPE_ACTIVE]);
        Vendor::where('type', '=', Vendor::TYPE_WATCH_LIST_NOMINEE_FROM_WATCH_LIST)->whereNotNull('vendor_evaluation_cycle_score_id')->update(['type' => Vendor::TYPE_WATCH_LIST]);

        \DB::statement("UPDATE vendors SET vendor_evaluation_cycle_score_id = NULL");
    }

    protected function removeCycleScores()
    {
        \DB::statement("DELETE FROM vendor_evaluation_cycle_scores");
    }

    protected function removeEvaluationScores()
    {
        \DB::statement("DELETE FROM vendor_evaluation_scores");
    }

    protected function updateEvaluationStatus()
    {
        \DB::statement("UPDATE vendor_performance_evaluations SET status_id = " . VendorPerformanceEvaluation::STATUS_IN_PROGRESS);
    }
}