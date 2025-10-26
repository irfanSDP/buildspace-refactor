<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWeatherRecordReportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('weather_record_reports', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('weather_record_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('from_time', 10);
			$table->string('to_time', 10);
			$table->smallInteger('weather_status', false, true);
			$table->timestamps();
			$table->softDeletes()->index();

			$table->foreign('weather_record_id')->references('id')->on('weather_records');
			$table->foreign('created_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('weather_record_reports');
	}

}