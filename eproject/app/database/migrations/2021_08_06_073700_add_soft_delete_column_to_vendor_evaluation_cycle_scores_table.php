<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeleteColumnToVendorEvaluationCycleScoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_evaluation_cycle_scores', function(Blueprint $table)
		{
			$table->softDeletes();
			$table->dropUnique('vendor_evaluation_cycle_scores_unique');
		});

		\DB::statement('CREATE UNIQUE INDEX vendor_evaluation_cycle_scores_unique ON vendor_evaluation_cycle_scores(vendor_work_category_id, company_id, vendor_performance_evaluation_cycle_id) WHERE deleted_at IS NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_evaluation_cycle_scores', function(Blueprint $table)
		{
			$table->dropSoftDeletes();
		});

		Schema::table('vendor_evaluation_cycle_scores', function(Blueprint $table)
		{
			$table->unique(['vendor_work_category_id', 'company_id', 'vendor_performance_evaluation_cycle_id'], 'vendor_evaluation_cycle_scores_unique');
		});
	}

}
