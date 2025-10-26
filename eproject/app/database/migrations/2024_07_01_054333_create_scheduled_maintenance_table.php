<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledMaintenanceTable extends Migration {

	/**
	 * Run the migrations.
	 */
	public function up()
	{
		// Creates the scheduled maintenance table
		Schema::create('scheduled_maintenance', function (Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('is_under_maintenance')->default(false);
			$table->string('message')->nullable();
			$table->unsignedInteger('created_by')->index();
			$table->dateTime('start_time')->nullable();
			$table->dateTime('end_time')->nullable();
			$table->string('image')->nullable();
			$table->timestamps();

			$table->foreign('created_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down()
	{
		Schema::drop('scheduled_maintenance');
	}

}