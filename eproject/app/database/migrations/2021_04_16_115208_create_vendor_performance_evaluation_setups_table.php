<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPerformanceEvaluationSetupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_performance_evaluation_setups', function(Blueprint $table)
		{
			$table->increments('id');
        	$table->unsignedInteger('vendor_performance_evaluation_id');
        	// $table->unsignedInteger('contract_group_category_id');
        	$table->unsignedInteger('company_id');
        	$table->unsignedInteger('template_node_id');
			$table->timestamps();

			$table->foreign('vendor_performance_evaluation_id')->references('id')->on('vendor_performance_evaluations')->onDelete('cascade');
			// $table->foreign('contract_group_category_id')->references('id')->on('contract_group_categories')->onDelete('cascade');
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
			$table->foreign('template_node_id')->references('id')->on('weighted_nodes')->onDelete('cascade');

			$table->unique(array('vendor_performance_evaluation_id', 'company_id'), 'vendor_performance_evaluation_setups_unique');
			$table->index('company_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_performance_evaluation_setups');
	}

}
