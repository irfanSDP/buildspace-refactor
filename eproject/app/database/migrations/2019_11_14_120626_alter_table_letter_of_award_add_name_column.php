<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterTableLetterOfAwardAddNameColumn extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('letter_of_awards', function(Blueprint $table)
		{
			$table->string('name')->nullable();
		});

		$letterOfAwardTemplateNameTableSeeder = new LetterOfAwardTemplateNameTableSeeder();
		$letterOfAwardTemplateNameTableSeeder->run();

		Schema::drop('letter_of_award_template_details');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
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

		$letterOfAwardTemplateNameTableSeeder = new LetterOfAwardTemplateNameTableSeeder();
		$letterOfAwardTemplateNameTableSeeder->rollback();

		Schema::table('letter_of_awards', function(Blueprint $table)
		{
			$table->dropColumn('name');
		});
	}
}
