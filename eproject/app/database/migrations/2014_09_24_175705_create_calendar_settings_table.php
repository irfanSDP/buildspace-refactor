<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCalendarSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('calendar_settings', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('country_id')->nullable();
			$table->timestamps();

			$table->foreign('country_id')->references('id')->on('countries');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('calendar_settings');
	}

}
