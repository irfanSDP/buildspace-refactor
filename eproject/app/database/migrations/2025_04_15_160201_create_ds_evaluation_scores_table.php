<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsEvaluationScoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_evaluation_scores', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('ds_cycle_id')->unsigned();
            $table->integer('ds_evaluation_id')->unsigned();
            $table->integer('company_id')->unsigned();
            $table->decimal('company_score', 5, 2)->unsigned()->default(0);
            $table->decimal('project_score', 5, 2)->unsigned()->default(0); // Average of all project scores
			$table->timestamps();

            $table->foreign('ds_cycle_id')->references('id')->on('ds_cycles')->onDelete('cascade');
            $table->foreign('ds_evaluation_id')->references('id')->on('ds_evaluations')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_evaluation_scores');
	}

}
