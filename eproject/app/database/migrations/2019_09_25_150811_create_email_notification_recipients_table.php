<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailNotificationRecipientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('email_notification_recipients', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('email_notification_id');
			$table->unsignedInteger('user_id');
			$table->timestamps();

			$table->index('email_notification_id');

			$table->foreign('email_notification_id')->references('id')->on('email_notifications')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('email_notification_recipients');
	}

}
