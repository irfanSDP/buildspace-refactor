<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Companies\Company;
use PCK\Vendor\Vendor;

class AddVendorStatusColumnToCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_status')->nullable();
		});

		foreach(Company::whereNotNull('activation_date')->get() as $company)
		{
			$company->updateVendorStatus();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->dropColumn('vendor_status');
		});
	}

}
