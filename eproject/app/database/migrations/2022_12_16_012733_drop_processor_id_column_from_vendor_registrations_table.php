<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropProcessorIdColumnFromVendorRegistrationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$seeder = new AddProcessorIdColumnToVendorRegistrationsTable;
		$seeder->down();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$seeder = new AddProcessorIdColumnToVendorRegistrationsTable;
		$seeder->up();
	}

}
