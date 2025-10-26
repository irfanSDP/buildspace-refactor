<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;

class AddPassingScoreColumnToVendorPerformanceEvaluationModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_module_parameters', function(Blueprint $table)
		{
			$table->unsignedInteger('passing_score')->default(VendorPerformanceEvaluationModuleParameter::PASSING_SCORE_DEFAULT_VALUE);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluation_module_parameters', function(Blueprint $table)
		{
			$table->dropColumn('passing_score');
		});
	}

}
