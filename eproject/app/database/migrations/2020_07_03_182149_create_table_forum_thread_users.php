<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableForumThreadUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('forum_thread_user', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('thread_id');
			$table->unsignedInteger('user_id');
			$table->timestamps();

			$table->foreign('thread_id')->references('id')->on('forum_threads')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

			$table->index('thread_id');
			$table->index('user_id');
			$table->unique(array('thread_id', 'user_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('forum_thread_user');
	}

}
