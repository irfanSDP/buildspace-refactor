<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVendorPerformanceEvaluationProcessorEditLogs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_performance_evaluation_processor_edit_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_performance_evaluation_company_form_id');
			$table->unsignedInteger('user_id');
			$table->timestamps();

			$table->index('user_id');

			$table->foreign('vendor_performance_evaluation_company_form_id')->references('id')->on('vendor_performance_evaluation_company_forms')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_performance_evaluation_processor_edit_logs');
	}

}
