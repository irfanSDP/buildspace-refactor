<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPerformanceEvaluationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_performance_evaluations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_performance_evaluation_cycle_id');
			$table->unsignedInteger('project_id');
			$table->unsignedInteger('project_status_id');
			$table->unsignedInteger('status_id');
			$table->unsignedInteger('person_in_charge_id')->nullable();
			$table->timestamp('start_date');
			$table->timestamp('end_date');
			$table->timestamps();

			$table->foreign('vendor_performance_evaluation_cycle_id')->references('id')->on('vendor_performance_evaluation_cycles')->onDelete('cascade');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->foreign('person_in_charge_id')->references('id')->on('users')->onDelete('cascade');

			$table->unique(array('project_id', 'project_status_id'), 'vendor_performance_evaluations_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_performance_evaluations');
	}

}
