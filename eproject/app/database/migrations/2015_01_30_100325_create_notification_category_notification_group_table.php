<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNotificationCategoryNotificationGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notifications_categories_in_groups', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('category_id')->index();
			$table->unsignedInteger('group_id')->index();

			$table->foreign('category_id')->references('id')->on('notification_categories')->onDelete('cascade');
			$table->foreign('group_id')->references('id')->on('notification_groups')->onDelete('cascade');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('notifications_categories_in_groups');
	}

}
