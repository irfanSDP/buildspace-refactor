<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUniqueContraintFromVendorPerformanceEvaluationTemplateFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_template_forms', function(Blueprint $table)
		{
			$table->dropUnique('vendor_performance_evaluation_template_forms_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluation_template_forms', function(Blueprint $table)
		{
			$table->unique(array('contract_group_category_id', 'project_status_id', 'revision'), 'vendor_performance_evaluation_template_forms_unique');
		});
	}

}
