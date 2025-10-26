<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropCountryIdColumnFromCostDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$columnAddMigration = new AddCountryIdColumnToCostDataTable;
		$columnAddMigration->down();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cost_data', function(Blueprint $table)
		{
			$columnAddMigration = new AddCountryIdColumnToCostDataTable;
			$columnAddMigration->up();
		});
	}

}
