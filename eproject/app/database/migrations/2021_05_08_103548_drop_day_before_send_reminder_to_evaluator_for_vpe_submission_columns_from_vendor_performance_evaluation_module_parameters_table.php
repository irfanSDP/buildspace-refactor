<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;

class DropDayBeforeSendReminderToEvaluatorForVpeSubmissionColumnsFromVendorPerformanceEvaluationModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_module_parameters', function(Blueprint $table)
		{
			$table->dropColumn('day_before_send_reminder_to_evaluator_for_vpe_submission_value');
			$table->dropColumn('day_before_send_reminder_to_evaluator_for_vpe_submission_unit');
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
			$table->decimal('day_before_send_reminder_to_evaluator_for_vpe_submission_value', 19, 2)->default(4);
			$table->integer('day_before_send_reminder_to_evaluator_for_vpe_submission_unit')->default(VendorPerformanceEvaluationModuleParameter::DAY);
		});
	}

}
