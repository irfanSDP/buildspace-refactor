<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableChangeIsPublicColumnToTypeColumnOnForumThreadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('forum_threads', function(Blueprint $table)
		{
			$table->smallInteger('type')->default(PCK\Forum\Thread::TYPE_PRIVATE);
		});

		DB::statement("UPDATE forum_threads SET type = " . PCK\Forum\Thread::TYPE_PUBLIC . " WHERE is_public = TRUE;");

		Schema::table('forum_threads', function(Blueprint $table)
		{
			$table->dropColumn('is_public');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('forum_threads', function(Blueprint $table)
		{
			$table->boolean('is_public')->default(false);
		});

		DB::statement("UPDATE forum_threads SET is_public = TRUE WHERE type = " . PCK\Forum\Thread::TYPE_PUBLIC . ";");

		Schema::table('forum_threads', function(Blueprint $table)
		{
			$table->dropColumn('type');
		});
	}

}
