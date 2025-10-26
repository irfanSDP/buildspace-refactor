<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailNotificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('email_notifications', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id');
			$table->string('subject', 255);
			$table->text('message');
			$table->integer('status');
			$table->unsignedInteger('created_by');
			$table->timestamps();

			$table->index('project_id');

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
		Schema::drop('email_notifications');
	}

}
