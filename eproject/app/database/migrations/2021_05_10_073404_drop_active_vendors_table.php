<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropActiveVendorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$migration = new CreateActiveVendorsTable;
		$migration->down();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$migration = new CreateActiveVendorsTable;
		$migration->up();

		$migration = new AddVendorEvaluationCycleScoreIdColumnToActiveVendorsTable;
		$migration->up();
	}

}
