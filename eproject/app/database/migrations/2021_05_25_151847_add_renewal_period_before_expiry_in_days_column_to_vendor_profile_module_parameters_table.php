<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRenewalPeriodBeforeExpiryInDaysColumnToVendorProfileModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_profile_module_parameters', function(Blueprint $table)
		{
			$table->unsignedInteger('renewal_period_before_expiry_in_days')->default(\PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter::RENEWAL_PERIOD_BEFORE_EXPIRY_IN_DAYS);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_profile_module_parameters', function(Blueprint $table)
		{
			$table->dropColumn('renewal_period_before_expiry_in_days');
		});
	}

}
