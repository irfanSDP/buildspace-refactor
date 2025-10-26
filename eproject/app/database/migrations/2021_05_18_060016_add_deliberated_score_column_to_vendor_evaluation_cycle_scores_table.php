<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliberatedScoreColumnToVendorEvaluationCycleScoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_evaluation_cycle_scores', function(Blueprint $table)
		{
			$table->decimal('deliberated_score', 5, 2)->default(0);
		});

		\DB::statement('UPDATE vendor_evaluation_cycle_scores set deliberated_score = score;');
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
			$table->dropColumn('deliberated_score');
		});
	}

}
