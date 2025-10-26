<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEotSecondLevelMessages extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('eot_second_level_messages', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('extension_of_time_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('subject');
			$table->text('message');
			$table->date('requested_new_deadline');
			$table->date('grant_different_deadline')->nullable();
			$table->smallInteger('decision', false, true)->nullable();
			$table->smallInteger('type', false, true)->index();
			$table->timestamps();

			$table->foreign('extension_of_time_id')->references('id')->on('extension_of_times');
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
		Schema::drop('eot_second_level_messages');
	}

}