<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddStartEndDateIndexingToCalendarsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('calendars', function (Blueprint $table)
		{
			$table->index(array( 'start_date', 'end_date' ));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('calendars', function (Blueprint $table)
		{
			$table->dropIndex('calendars_start_date_end_date_index');
		});
	}

}