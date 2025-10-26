<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\VendorRegistration\CompanyTemporaryDetail;

class AddNameAndReferenceNoAndCountryIdAndStateIdColumnsInCompanyTemporaryDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_temporary_details', function(Blueprint $table)
		{
			$table->string('name')->nullable();
			$table->string('reference_no', 60)->nullable();
			$table->unsignedInteger('country_id')->nullable();
			$table->unsignedInteger('state_id')->nullable();
		});

		foreach(CompanyTemporaryDetail::all() as $companyTemporaryDetail)
		{
			if(is_null($companyTemporaryDetail->vendorRegistration)) continue;

			$company = $companyTemporaryDetail->vendorRegistration->company;

			$companyTemporaryDetail->name 		  = $company->name;
			$companyTemporaryDetail->reference_no = $company->reference_no;
			$companyTemporaryDetail->country_id	  = $company->country_id;
			$companyTemporaryDetail->state_id	  = $company->state_id;

			$companyTemporaryDetail->save();
		}
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('company_temporary_details', function(Blueprint $table)
		{
			$table->dropColumn('name');
			$table->dropColumn('reference_no');
			$table->dropColumn('country_id');
			$table->dropColumn('state_id');
		});
	}

}
