<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsCycleScoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_cycle_scores', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('ds_cycle_id')->unsigned();
            $table->integer('company_id')->unsigned();
            $table->integer('vendor_management_grade_level_id')->unsigned()->nullable();
            $table->decimal('company_score_weighted', 5, 2)->unsigned()->default(0);
            $table->decimal('project_score_weighted', 5, 2)->unsigned()->default(0);
            $table->decimal('total_score', 5, 2)->unsigned()->default(0);
			$table->timestamps();

            $table->foreign('ds_cycle_id')->references('id')->on('ds_cycles')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('vendor_management_grade_level_id')->references('id')->on('vendor_management_grade_levels');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_cycle_scores');
	}

}
