<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\Companies\Company;
use PCK\VendorRegistration\VendorProfile;

class CreateVendorProfilesTable extends Migration
{
	public function up()
	{
		Schema::create('vendor_profiles', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->text('remarks')->nullable();
			$table->timestamps();

			$table->index('company_id');

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
		});

		foreach(Company::all() as $company)
		{
			if(is_null($company->activation_date)) continue;

			VendorProfile::createIfNotExists($company);
		}
	}

	public function down()
	{
		Schema::drop('vendor_profiles');
	}
}
