<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsExcludedColumnToWeightedNodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('weighted_nodes', function(Blueprint $table)
		{
			$table->boolean('is_excluded')->default(false);
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
			$table->dropColumn('is_excluded');
		});
	}

}
