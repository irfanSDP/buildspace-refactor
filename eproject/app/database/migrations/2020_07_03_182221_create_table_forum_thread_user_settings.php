<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableForumThreadUserSettings extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('forum_thread_user_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('forum_thread_user_id');
			$table->boolean('keep_me_posted')->default(true);
			$table->timestamps();

			$table->foreign('forum_thread_user_id')->references('id')->on('forum_thread_user')->onDelete('cascade');

			$table->index('forum_thread_user_id');
			$table->unique('forum_thread_user_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('forum_thread_user_settings');
	}

}
