<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;

class AddEmailReminderBeforeCycleEndDateRelatedColumnsToVendorPerformanceEvaluationModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_module_parameters', function(Blueprint $table)
		{
			$table->boolean('email_reminder_before_cycle_end_date')->default(true);
			$table->unsignedInteger('email_reminder_before_cycle_end_date_value')->default(VendorPerformanceEvaluationModuleParameter::EMAIL_REMINDER_BEFORE_CYCLE_END_DATE_DEFAULT_VALUE);
			$table->unsignedInteger('email_reminder_before_cycle_end_date_unit')->default(VendorPerformanceEvaluationModuleParameter::DAY);
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
			$table->dropColumn('email_reminder_before_cycle_end_date');
			$table->dropColumn('email_reminder_before_cycle_end_date_value');
			$table->dropColumn('email_reminder_before_cycle_end_date_unit');
		});
	}

}
