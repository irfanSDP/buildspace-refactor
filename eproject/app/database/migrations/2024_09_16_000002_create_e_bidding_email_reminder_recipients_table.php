<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEBiddingEmailReminderRecipientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('e_bidding_email_reminder_recipients', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('email_reminder_id');
			$table->unsignedInteger('user_id');
			$table->timestamps();

			$table->foreign('email_reminder_id')->references('id')->on('e_bidding_email_reminders')->onDelete('cascade');
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
		Schema::drop('e_bidding_email_reminder_recipients');
	}

}
