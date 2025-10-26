<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Companies\Company;
use Carbon\Carbon;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;
use PCK\Base\Helpers;

class UpdateActivationDateColumnsInCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasColumn('companies', 'expire_at'))
        {
			Schema::table('companies', function(Blueprint $table)
			{
				$table->renameColumn('expire_at', 'purge_date');
			});
		}

		if (!Schema::hasColumn('companies', 'deactivation_date'))
        {
			Schema::table('companies', function(Blueprint $table)
			{
				$table->timestamp('deactivation_date')->nullable();
			});
		}

		if (!Schema::hasColumn('companies', 'deactivated_at'))
        {
			Schema::table('companies', function(Blueprint $table)
			{
				$table->timestamp('deactivated_at')->nullable();
			});
		}

		$gracePeriodValue = VendorProfileModuleParameter::getValue('grace_period_of_expired_vendor_before_moving_to_dvl_value');

		switch(VendorProfileModuleParameter::getValue('grace_period_of_expired_vendor_before_moving_to_dvl_unit'))
		{
		    case VendorProfileModuleParameter::DAY:
		        $gracePeriodUnit = 'days';
		        break;
		    case VendorProfileModuleParameter::WEEK:
		        $gracePeriodUnit = 'weeks';
		        break;
		    case VendorProfileModuleParameter::MONTH:
		        $gracePeriodUnit = 'months';
		        break;
		    default:
		        throw new \Exception("Invalid time unit");
		}

		foreach(Company::whereNotNull('deactivate_at')->get() as $company)
		{
			if(Carbon::parse($company->deactivate_at)->isPast())
			{
				$deactivationDate = Helpers::getTimeFrom(Carbon::parse($company->deactivate_at), $gracePeriodValue, $gracePeriodUnit);

				$company->deactivation_date = $deactivationDate;
			}

			$company->save();
		}

		if (Schema::hasColumn('companies', 'deactivate_at'))
        {
			Schema::table('companies', function(Blueprint $table)
			{
				$table->renameColumn('deactivate_at', 'expiry_date');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasColumn('companies', 'expiry_date'))
        {
			Schema::table('companies', function(Blueprint $table)
			{
				$table->renameColumn('expiry_date', 'deactivate_at');
			});
		}

		if (Schema::hasColumn('companies', 'purge_date'))
        {
			Schema::table('companies', function(Blueprint $table)
			{
				$table->renameColumn('purge_date', 'expire_at');
			});
		}

		if (Schema::hasColumn('companies', 'deactivation_date'))
        {
			Schema::table('companies', function(Blueprint $table)
			{
				$table->dropColumn('deactivation_date');
			});
		}

		if (Schema::hasColumn('companies', 'deactivated_at'))
        {
			Schema::table('companies', function(Blueprint $table)
			{
				$table->dropColumn('deactivated_at');
			});
		}
	}

}
