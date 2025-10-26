<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtColumnToVendorPerformanceEvaluationCompanyFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_company_forms', function(Blueprint $table)
		{
			$table->softDeletes();

			$table->dropUnique('vendor_performance_evaluation_company_forms_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluation_company_forms', function(Blueprint $table)
		{
			$table->dropSoftDeletes();

			$table->unique(array('vendor_performance_evaluation_id', 'company_id', 'evaluator_company_id'), 'vendor_performance_evaluation_company_forms_unique');
		});
	}

}
