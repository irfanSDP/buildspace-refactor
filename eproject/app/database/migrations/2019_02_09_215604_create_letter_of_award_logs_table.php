<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLetterOfAwardLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('letter_of_award_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('letter_of_award_id');
			$table->unsignedInteger('type_identifier');
			$table->unsignedInteger('user_id');
			$table->timestamps();

			$table->index('letter_of_award_id');

			$table->foreign('letter_of_award_id')->references('id')->on('letter_of_awards')->onDelete('cascade');
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
		Schema::drop('letter_of_award_logs');
	}

}
