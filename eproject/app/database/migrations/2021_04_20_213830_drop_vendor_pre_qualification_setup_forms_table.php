<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropVendorPreQualificationSetupFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$migration = new CreateVendorPreQualificationSetupFormsTable;
		$migration->down();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$migration = new CreateVendorPreQualificationSetupFormsTable;
		$migration->up();
	}

}
