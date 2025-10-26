<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;

class RecalculateVendorPerformanceEvaluationScore extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vendor-management:recalculate-vendor-performance-evaluation-score';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Recalculate VPE score column in vendor_performance_evaluation_company_forms table.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		foreach(VendorPerformanceEvaluationCompanyForm::all() as $vpeCompanyForm)
		{
			$score = $vpeCompanyForm->weightedNode->getScore();

			if($score == 0)
			{
				$score = null;
			}

			if($vpeCompanyForm->score == $score) continue;
			
			DB::statement("UPDATE vendor_performance_evaluation_company_forms SET score = ? WHERE id = ?", [$score, $vpeCompanyForm->id]);

			$this->info("Record [{$vpeCompanyForm->id}] old score : {$vpeCompanyForm->score} recalculated to new score : {$score}");
		}
	}
}
