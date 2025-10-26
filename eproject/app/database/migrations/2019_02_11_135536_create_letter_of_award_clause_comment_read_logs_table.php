<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLetterOfAwardClauseCommentReadLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('letter_of_award_clause_comment_read_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('clause_comment_id');
			$table->timestamps();

			$table->index('user_id');
			$table->index('clause_comment_id');
			
			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('clause_comment_id')->references('id')->on('letter_of_award_clause_comments')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('letter_of_award_clause_comment_read_logs');
	}

}
