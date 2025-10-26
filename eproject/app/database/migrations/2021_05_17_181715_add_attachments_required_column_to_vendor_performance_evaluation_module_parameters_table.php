<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentsRequiredColumnToVendorPerformanceEvaluationModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_module_parameters', function(Blueprint $table)
		{
			$table->boolean('attachments_required')->default(false);
			$table->decimal('attachments_required_score_threshold', 5, 2)->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluation_module_parameters', function(Blueprint $table)
		{
			$table->dropColumn('attachments_required');
			$table->dropColumn('attachments_required_score_threshold');
		});
	}

}
