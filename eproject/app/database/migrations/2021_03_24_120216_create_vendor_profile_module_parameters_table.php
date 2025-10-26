<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;

class CreateVendorProfileModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_profile_module_parameters', function(Blueprint $table)
		{
			$table->increments('id');

			$table->decimal('validity_period_of_active_vendor_in_avl_value', 19, 2);
			$table->integer('validity_period_of_active_vendor_in_avl_unit');

			$table->decimal('grade_period_of_expired_vendor_before_moving_to_dvl_value', 19, 2);
			$table->integer('grade_period_of_expired_vendor_before_moving_to_dvl_unit');

			$table->decimal('vendor_retrain_period_in_wl_value', 19, 2);
			$table->integer('vendor_retrain_period_in_wl_unit');

			$table->decimal('minimum_vpe_score_to_exit_wl_value', 19, 2);
			$table->integer('minimum_vpe_score_to_exit_wl_unit');

			$table->timestamps();
		});

		// seeds data
		// there will only be 1 record
		$record = VendorProfileModuleParameter::first();

        if(is_null($record))
        {
            $record = new VendorProfileModuleParameter();

			$record->validity_period_of_active_vendor_in_avl_value = VendorProfileModuleParameter::VALIDITY_PERIOD_OF_ACTIVE_VENDOR_IN_AVL_VALUE_DEFAULT_VALUE;
			$record->validity_period_of_active_vendor_in_avl_unit  = VendorProfileModuleParameter::MONTH;
	
			$record->grade_period_of_expired_vendor_before_moving_to_dvl_value = VendorProfileModuleParameter::GRACE_PERIOD_OF_EXPIRED_VENDOR_BEFORE_MOVING_TO_DVL_VALUE_DEFAULT_VALUE;
			$record->grade_period_of_expired_vendor_before_moving_to_dvl_unit  = VendorProfileModuleParameter::MONTH;
	
			$record->vendor_retrain_period_in_wl_value = VendorProfileModuleParameter::VENDOR_RETAIN_PERIOD_IN_WL_VALUE_DEFAULT_VALUE;
			$record->vendor_retrain_period_in_wl_unit  = VendorProfileModuleParameter::MONTH;
	
			$record->minimum_vpe_score_to_exit_wl_value = 70;
			$record->minimum_vpe_score_to_exit_wl_unit  = 1;

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
		Schema::drop('vendor_profile_module_parameters');
	}

}
