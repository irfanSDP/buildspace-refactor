<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemarksColumnToWeightedNodeScoressTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('weighted_node_scores', function(Blueprint $table)
		{
			$table->string('remarks')->nullable();
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
			$table->dropColumn('remarks');
		});
	}

}
