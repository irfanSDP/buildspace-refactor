<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\Companies\Company;
use PCK\VendorRegistration\VendorProfile;

class CreateVendorProfileRecordsForMigratedVendors extends Migration
{
	/**
	 * to patch data for migrated vendors that don't already have vendor profile records
	 */
	public function up()
	{
		foreach(Company::whereNotNull('activation_date')->get() as $company)
		{
			VendorProfile::createIfNotExists($company);
		}
	}

	public function down()
	{
		// not applicable
	}
}
