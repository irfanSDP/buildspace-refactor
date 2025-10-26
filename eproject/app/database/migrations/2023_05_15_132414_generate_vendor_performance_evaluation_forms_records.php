<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use PCK\Helpers\PathRegistry;
use PCK\VendorPerformanceEvaluation\Cycle;

class GenerateVendorPerformanceEvaluationFormsRecords extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$cycle = Cycle::getLatestCompletedCycle();

		if(!$cycle) return;

		$logPath = PathRegistry::vendorPerformanceEvaluationFormReportsProgressLog($cycle->id);
		$filepath = PathRegistry::vendorPerformanceEvaluationFormReports($cycle->id);

		if(!file_exists($filepath) && !file_exists($logPath))
		{
		    \Queue::push('PCK\QueueJobs\GenerateVendorEvaluationForms', array(
		        'cycle_id' => $cycle->id,
		    ),'default');
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Do nothing.
	}

}
