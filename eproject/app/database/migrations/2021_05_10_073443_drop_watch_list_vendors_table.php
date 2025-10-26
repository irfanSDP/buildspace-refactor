<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropWatchListVendorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('watch_list_vendors', function(Blueprint $table)
		{
			$migration = new CreateWatchListVendorsTable;
			$migration->down();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('watch_list_vendors', function(Blueprint $table)
		{
			$migration = new CreateWatchListVendorsTable;
			$migration->up();
		});
	}

}
