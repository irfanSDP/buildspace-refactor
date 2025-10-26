<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;

class CreateVendorRegistrationAndPrequalificationModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_registration_and_prequalification_module_parameters', function(Blueprint $table)
		{
			$table->increments('id');

			$table->integer('valid_period_of_temp_login_acc_to_unreg_vendor_value');
			$table->integer('valid_period_of_temp_login_acc_to_unreg_vendor_unit');

			$table->boolean('allow_only_one_comp_to_reg_under_multi_vendor_group');

			$table->boolean('allow_only_one_comp_to_reg_under_multi_vendor_category');

			$table->boolean('vendor_reg_cert_generated_sent_to_successful_reg_vendor');

			$table->integer('notify_vendor_before_end_of_temp_acc_valid_period_value');
			$table->integer('notify_vendor_before_end_of_temp_acc_valid_period_unit');

			$table->integer('period_retain_unsuccessful_reg_and_preq_submission_value');
			$table->integer('period_retain_unsuccessful_reg_and_preq_submission_unit');
			$table->integer('start_period_retain_unsuccessful_reg_and_preq_submission_value');

			$table->integer('notify_purge_data_before_end_period_for_unsuccessful_sub_value');
			$table->integer('notify_purge_data_before_end_period_for_unsuccessful_sub_unit');

			$table->boolean('retain_info_of_unsuccessfully_reg_vendor_after_data_purge');
			$table->boolean('retain_company_name')->default(true);
			$table->boolean('retain_roc_number')->default(true);
			$table->boolean('retain_email')->default(true);
			$table->boolean('retain_contact_number')->default(true);
			$table->boolean('retain_date_of_data_purging')->default(true);

			$table->timestamps();
		});

		// seeds data
		// there will only be 1 record
		$record = VendorRegistrationAndPrequalificationModuleParameter::first();

		if(is_null($record))
		{
			$record = new VendorRegistrationAndPrequalificationModuleParameter();

			$record->valid_period_of_temp_login_acc_to_unreg_vendor_value = 7;
			$record->valid_period_of_temp_login_acc_to_unreg_vendor_unit  = VendorRegistrationAndPrequalificationModuleParameter::DAY;

			$record->allow_only_one_comp_to_reg_under_multi_vendor_group = true;

			$record->allow_only_one_comp_to_reg_under_multi_vendor_category = true;

			$record->vendor_reg_cert_generated_sent_to_successful_reg_vendor = true;

			$record->notify_vendor_before_end_of_temp_acc_valid_period_value = 2;
			$record->notify_vendor_before_end_of_temp_acc_valid_period_unit  = VendorRegistrationAndPrequalificationModuleParameter::DAY;

			$record->period_retain_unsuccessful_reg_and_preq_submission_value         = 90;
			$record->period_retain_unsuccessful_reg_and_preq_submission_unit          = VendorRegistrationAndPrequalificationModuleParameter::DAY;
			$record->start_period_retain_unsuccessful_reg_and_preq_submission_value   = VendorRegistrationAndPrequalificationModuleParameter::REQUEST_RESUBMISSION_IS_SENT;

			$record->notify_purge_data_before_end_period_for_unsuccessful_sub_value = 30;
			$record->notify_purge_data_before_end_period_for_unsuccessful_sub_unit  = VendorRegistrationAndPrequalificationModuleParameter::DAY;

			$record->retain_info_of_unsuccessfully_reg_vendor_after_data_purge = true;

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
		Schema::drop('vendor_registration_and_prequalification_module_parameters');
	}

}
