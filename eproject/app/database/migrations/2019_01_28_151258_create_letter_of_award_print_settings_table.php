<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLetterOfAwardPrintSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('letter_of_award_print_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('letter_of_award_id');
			$table->unsignedInteger('header_font_size')->default(12);
			$table->unsignedInteger('clause_font_size')->default(12);			
			$table->timestamps();

			$table->index('letter_of_award_id');

			$table->foreign('letter_of_award_id')->references('id')->on('letter_of_awards')->onDelete('cascade');
		});

		$letterOfAwardPrintSettingsTableSeeder = new LetterOfAwardPrintSettingsTableSeeder;
		$letterOfAwardPrintSettingsTableSeeder->run();
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('letter_of_award_print_settings');
	}

}
