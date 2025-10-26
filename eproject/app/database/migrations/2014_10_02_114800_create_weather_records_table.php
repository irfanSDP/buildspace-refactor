<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWeatherRecordsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('weather_records', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->unsignedInteger('verified_by')->nullable()->index();
			$table->date('date');
			$table->text('note')->nullable();
			$table->smallInteger('status', false, true)->index();
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('created_by')->references('id')->on('users');
			$table->foreign('verified_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('weather_records');
	}

}