<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEBiddingEmailRemindersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('e_bidding_email_reminders', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('ebidding_id')->nullable();  // Allow null for default
			$table->string('subject', 255);
			$table->text('message');
			$table->integer('status_preview_start_time');
			$table->integer('status_bidding_start_time');
			$table->unsignedInteger('created_by')->nullable();  // Allow null for default
			$table->timestamps();

			$table->foreign('ebidding_id')->references('id')->on('e_biddings');
			$table->foreign('created_by')->references('id')->on('users');
			
			$table->unique(array( 'ebidding_id'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('e_bidding_email_reminders');
	}

}
