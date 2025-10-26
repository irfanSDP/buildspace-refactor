<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPerformanceEvaluationTemplateFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_performance_evaluation_template_forms', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_status_id');
			$table->unsignedInteger('contract_group_category_id');
			$table->unsignedInteger('weighted_node_id');
			$table->unsignedInteger('revision')->default(0);
			$table->unsignedInteger('status_id');
			$table->timestamps();

			$table->foreign('contract_group_category_id')->references('id')->on('contract_group_categories')->onDelete('cascade');
			$table->foreign('weighted_node_id')->references('id')->on('weighted_nodes')->onDelete('cascade');

			$table->unique(array('contract_group_category_id', 'project_status_id', 'revision'), 'vendor_performance_evaluation_template_forms_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_performance_evaluation_template_forms');
	}

}
