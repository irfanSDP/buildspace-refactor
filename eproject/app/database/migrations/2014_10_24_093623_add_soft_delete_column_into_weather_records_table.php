<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSoftDeleteColumnIntoWeatherRecordsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('weather_records', function (Blueprint $table)
		{
			$table->softDeletes();

			$table->index('deleted_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('weather_records', function (Blueprint $table)
		{
			$table->dropSoftDeletes();
		});
	}

}