<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddProcessorRemarksToVendorPerformanceEvaluationCompanyFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_company_forms', function(Blueprint $table)
		{
			$table->text('processor_remarks')->nullable();
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
			$table->dropColumn('processor_remarks');
		});
	}

}
