<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEotThirdLevelMessages extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('eot_third_level_messages', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('extension_of_time_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('subject');
			$table->text('message');
			$table->date('deadline_to_comply_with')->nullable();
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
		Schema::drop('eot_third_level_messages');
	}

}