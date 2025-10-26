<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPerformanceEvaluatorsTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_performance_evaluators', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_performance_evaluation_id');
			// $table->unsignedInteger('contract_group_category_id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('user_id');
			$table->timestamps();

			$table->foreign('vendor_performance_evaluation_id')->references('id')->on('vendor_performance_evaluations')->onDelete('cascade');
			// $table->foreign('contract_group_category_id')->references('id')->on('contract_group_categories')->onDelete('cascade');
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

			$table->index(array('vendor_performance_evaluation_id', 'company_id'), 'vendor_performance_evaluators_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_performance_evaluators');
	}

}
