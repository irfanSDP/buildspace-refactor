<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropNominatedWatchListVendorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('nominated_watch_list_vendors', function(Blueprint $table)
		{
			$migration = new CreateNominatedWatchListVendorsTable;
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
		Schema::table('nominated_watch_list_vendors', function(Blueprint $table)
		{
			$migration = new CreateNominatedWatchListVendorsTable;
			$migration->up();
		});
	}

}
