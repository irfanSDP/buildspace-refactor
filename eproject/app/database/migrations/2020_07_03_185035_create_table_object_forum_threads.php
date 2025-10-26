<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableObjectForumThreads extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('object_forum_threads', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('thread_id');
			$table->morphs('object');
			$table->timestamps();

			$table->foreign('thread_id')->references('id')->on('forum_threads')->onDelete('cascade');

			$table->index('thread_id');
			$table->unique('thread_id');
			$table->unique(array('object_id', 'object_type'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('object_forum_threads');
	}

}
