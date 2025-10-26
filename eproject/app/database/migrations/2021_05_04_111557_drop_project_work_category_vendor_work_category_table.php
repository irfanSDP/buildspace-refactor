<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropProjectWorkCategoryVendorWorkCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$migration = new CreateProjectWorkCategoryVendorWorkCategoryTable();
		$migration->down();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$migration = new CreateProjectWorkCategoryVendorWorkCategoryTable();
		$migration->up();
	}

}
