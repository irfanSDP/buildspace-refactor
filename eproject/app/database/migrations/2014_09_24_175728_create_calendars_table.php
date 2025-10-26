<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCalendarsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('calendars', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('country_id');
			$table->unsignedInteger('state_id')->nullable();
			$table->string('description');
			$table->string('name');
			$table->date('start_date')->nullable();
			$table->date('end_date')->nullable();
			$table->smallInteger('event_type', false, true)->nullable();
			$table->timestamps();

			$table->foreign('country_id')->references('id')->on('countries');
			$table->foreign('state_id')->references('id')->on('states');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('calendars');
	}

}