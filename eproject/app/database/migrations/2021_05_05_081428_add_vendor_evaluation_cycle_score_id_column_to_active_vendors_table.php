<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVendorEvaluationCycleScoreIdColumnToActiveVendorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('active_vendors', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_evaluation_cycle_score_id')->nullable();

			$table->foreign('vendor_evaluation_cycle_score_id')->references('id')->on('vendor_evaluation_cycle_scores')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('active_vendors', function(Blueprint $table)
		{
			$table->dropColumn('vendor_evaluation_cycle_score_id');
		});
	}

}
