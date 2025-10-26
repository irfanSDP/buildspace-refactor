<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;

class CreateVendorPerformanceEvaluationModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_performance_evaluation_module_parameters', function(Blueprint $table)
		{
			$table->increments('id');

			$table->decimal('default_time_frame_for_vpe_cycle_value', 19, 2);
			$table->integer('default_time_frame_for_vpe_cycle_unit');

			$table->decimal('default_time_frame_for_vpe_submission_value', 19, 2);
			$table->integer('default_time_frame_for_vpe_submission_unit');

			$table->decimal('day_before_send_reminder_to_evaluator_for_vpe_submission_value', 19, 2);
			$table->integer('day_before_send_reminder_to_evaluator_for_vpe_submission_unit');

			$table->timestamps();
		});

		// seeds data
		// there will only be 1 record
		$record = VendorPerformanceEvaluationModuleParameter::first();

		if(is_null($record))
        {
            $record = new VendorPerformanceEvaluationModuleParameter();

			$record->default_time_frame_for_vpe_cycle_value = VendorPerformanceEvaluationModuleParameter::DEFAULT_TIME_FRAME_FOR_VPE_CYCLE_VALUE_DEFAULT_VALUE;
			$record->default_time_frame_for_vpe_cycle_unit  = VendorPerformanceEvaluationModuleParameter::MONTH;
	
			$record->default_time_frame_for_vpe_submission_value = VendorPerformanceEvaluationModuleParameter::DEFAULT_TIME_FRAME_FOR_VPE_SUBMISSION_VALUE_DEFAULT_VALUE;
			$record->default_time_frame_for_vpe_submission_unit  = VendorPerformanceEvaluationModuleParameter::WEEK;
	
			$record->day_before_send_reminder_to_evaluator_for_vpe_submission_value = 4;
			$record->day_before_send_reminder_to_evaluator_for_vpe_submission_unit  = VendorPerformanceEvaluationModuleParameter::DAY;

			$record->save();
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_performance_evaluation_module_parameters');
	}

}
