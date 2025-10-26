<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeleteColumnToVendorEvaluationScoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_evaluation_scores', function(Blueprint $table)
		{
			$table->softDeletes();
			$table->dropUnique('vendor_evaluation_scores_unique');
		});

		\DB::statement('CREATE UNIQUE INDEX vendor_evaluation_scores_unique ON vendor_evaluation_scores(vendor_work_category_id, company_id, vendor_performance_evaluation_id) WHERE deleted_at IS NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_evaluation_scores', function(Blueprint $table)
		{
			$table->dropSoftDeletes();
		});

		Schema::table('vendor_evaluation_scores', function(Blueprint $table)
		{
			$table->unique(['vendor_work_category_id', 'company_id', 'vendor_performance_evaluation_id'], 'vendor_evaluation_scores_unique');
		});
	}

}
