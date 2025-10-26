<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemarksColumnToWeightedNodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('weighted_nodes', function(Blueprint $table)
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
		Schema::table('weighted_nodes', function(Blueprint $table)
		{
			$table->dropColumn('remarks');
		});
	}

}
