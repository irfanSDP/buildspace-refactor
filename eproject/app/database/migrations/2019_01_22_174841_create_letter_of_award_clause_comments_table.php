<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLetterOfAwardClauseCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('letter_of_award_clause_comments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('clause_id');
			$table->text('comments');
			$table->unsignedInteger('user_id');
			$table->timestamps();

			$table->index('clause_id');

			$table->foreign('clause_id')->references('id')->on('letter_of_award_clauses')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('letter_of_award_clause_comments');
	}

}
