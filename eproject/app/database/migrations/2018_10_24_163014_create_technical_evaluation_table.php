<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTechnicalEvaluationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('technical_evaluations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('tender_id');
			$table->date('targeted_date_of_award')->nullable();
			$table->integer('submitted_by')->nullable();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('technical_evaluations');
	}

}
