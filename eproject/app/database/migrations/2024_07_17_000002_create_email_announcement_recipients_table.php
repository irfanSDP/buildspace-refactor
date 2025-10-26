<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailAnnouncementRecipientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('email_announcement_recipients', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('email_announcement_id');
			$table->unsignedInteger('contract_group_category_id');
			$table->unsignedInteger('user_id');
			$table->timestamps();

			$table->index('email_announcement_id');
			$table->index('contract_group_category_id');

			$table->foreign('email_announcement_id')->references('id')->on('email_announcements')->onDelete('cascade');
			$table->foreign('contract_group_category_id')->references('id')->on('contract_group_categories')->onDelete('cascade');
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
		Schema::drop('email_announcement_recipients');
	}

}
