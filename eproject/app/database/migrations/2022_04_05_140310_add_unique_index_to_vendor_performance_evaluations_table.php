<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexToVendorPerformanceEvaluationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluations', function(Blueprint $table)
		{
			\DB::statement('CREATE UNIQUE INDEX vendor_performance_evaluations_unique ON vendor_performance_evaluations(vendor_performance_evaluation_cycle_id, project_id) WHERE deleted_at IS NULL');
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
			$table->dropIndex('vendor_performance_evaluations_unique');
		});
	}

}
