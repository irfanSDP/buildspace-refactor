<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropVendorPreQualificationFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$migration = new CreateVendorPreQualificationFormsTable;
		$migration->down();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$migration = new CreateVendorPreQualificationFormsTable;
		$migration->up();
	}

}
