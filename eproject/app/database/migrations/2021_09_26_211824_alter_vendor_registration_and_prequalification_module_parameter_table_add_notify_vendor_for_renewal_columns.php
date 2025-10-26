<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;

class AlterVendorRegistrationAndPrequalificationModuleParameterTableAddNotifyVendorForRenewalColumns extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_registration_and_prequalification_module_parameters', function(Blueprint $table)
		{
			$table->integer('notify_vendors_for_renewal_value')->default(VendorRegistrationAndPrequalificationModuleParameter::NOTIFY_VENDOR_FOR_RENEWAL_DEFAULT_VALUE);
			$table->integer('notify_vendors_for_renewal_unit')->default(VendorRegistrationAndPrequalificationModuleParameter::DAY);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_registration_and_prequalification_module_parameters', function(Blueprint $table)
		{
			$table->dropColumn('notify_vendors_for_renewal_value');
			$table->dropColumn('notify_vendors_for_renewal_unit');
		});
	}

}
