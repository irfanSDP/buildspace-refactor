<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsCompletedColumnToVendorPerformanceEvaluationCyclesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_cycles', function(Blueprint $table)
		{
			$table->boolean('is_completed')->default(false);
		});

		foreach(\PCK\VendorPerformanceEvaluation\Cycle::where('end_date', '<=', 'NOW()')->get() as $endedCycles)
		{
			$endedCycles->is_completed = true;
			$endedCycles->save();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluation_cycles', function(Blueprint $table)
		{
			$table->dropColumn('is_completed');
		});
	}

}
