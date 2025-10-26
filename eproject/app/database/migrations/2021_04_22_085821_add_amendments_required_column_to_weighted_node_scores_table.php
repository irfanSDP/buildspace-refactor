<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmendmentsRequiredColumnToWeightedNodeScoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('weighted_node_scores', function(Blueprint $table)
		{
			$table->boolean('amendments_required')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('weighted_node_scores', function(Blueprint $table)
		{
			$table->dropColumn('amendments_required');
		});
	}

}
