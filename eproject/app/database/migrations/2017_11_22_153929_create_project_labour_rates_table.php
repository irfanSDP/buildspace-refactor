<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectLabourRatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_labour_rates', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('labour_type');
			$table->unsignedInteger('normal_working_hours')->default(0);
			$table->decimal('normal_rate_per_hour')->default(0);
			$table->decimal('ot_rate_per_hour')->default(0);
			$table->unsignedInteger('project_id')->index();
			$table->timestamps();

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
		Schema::drop('project_labour_rates');
	}

}
