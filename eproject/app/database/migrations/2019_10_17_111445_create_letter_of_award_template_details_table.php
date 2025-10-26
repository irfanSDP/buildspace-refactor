<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLetterOfAwardTemplateDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('letter_of_award_template_details', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('letter_of_award_id');
			$table->string('name');
			$table->timestamps();

			$table->index('letter_of_award_id');
			$table->foreign('letter_of_award_id')->references('id')->on('letter_of_awards')->onDelete('cascade');
		});

		$letterOfAwardTemplateDetailsTableSeeder = new LetterOfAwardTemplateDetailsTableSeeder();
		$letterOfAwardTemplateDetailsTableSeeder->run();
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('letter_of_award_template_details');
	}

}
