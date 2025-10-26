<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUniqueConstraintFromVendorPerformanceEvaluationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluations', function(Blueprint $table)
		{
			$table->dropUnique('vendor_performance_evaluations_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluations', function(Blueprint $table)
		{
			$table->unique(array('project_id', 'project_status_id'), 'vendor_performance_evaluations_unique');
		});
	}

}
