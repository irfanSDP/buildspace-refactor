<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLetterOfAwardsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('letter_of_awards', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->nullable();
			$table->boolean('is_template');
			$table->unsignedInteger('status')->nullable();
			$table->unsignedBigInteger('submitted_for_approval_by')->nullable();
			$table->timestamps();

			$table->index('project_id');

			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('submitted_for_approval_by')->references('id')->on('users');
		});

		$seeder = new LetterOfAwardTableSeeder;
        $seeder->run();
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('letter_of_awards');
	}

}
