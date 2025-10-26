<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSubmittedForApprovalByColumnToVendorPerformanceEvaluationCompanyFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_company_forms', function(Blueprint $table)
		{
			$table->unsignedInteger('submitted_for_approval_by')->nullable();

			$table->index('submitted_for_approval_by');

			$table->foreign('submitted_for_approval_by')->references('id')->on('users')->onDelete('cascade');
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
			$table->dropColumn('submitted_for_approval_by');
		});
	}

}
