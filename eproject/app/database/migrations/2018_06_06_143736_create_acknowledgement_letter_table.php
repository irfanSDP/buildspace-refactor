<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcknowledgementLetterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('acknowledgement_letters', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('tender_id')->unique();
			$table->string('content')->nullable();
			$table->boolean('enable')->default(true);
			$table->boolean('disable')->default(true);
			$table->timestamps();

			$table->foreign('tender_id')->references('id')->on('tenders');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('acknowledgement_letters');
	}

}
