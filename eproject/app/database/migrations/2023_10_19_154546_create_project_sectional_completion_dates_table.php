<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectSectionalCompletionDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_sectional_completion_dates', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('project_id');
            $table->date('sectional_completion_date');
			$table->timestamps();

            $table->index('project_id');

            $table->foreign('project_id')->references('id')->on('projects');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('project_sectional_completion_dates');
	}

}
