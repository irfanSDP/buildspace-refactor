<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorRegistrationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		/* 
		 * I have to refactor this migration because it throws exception when running artisan migrate against fresh DB
		 * without any migration related to VM. It is because in this script after creating the vendor_registrations table
		 * it calls VendorRegistration::create() which in VendorRegistration::boot() will initiates Section::initiate()
		 * in created() callback. This will throws an exception because vendor_registration_sections table is not created
		 * yet. Without needing to temper soo much on the migration and the migrated data by deleting the 
		 * 2021_04_29_222211_create_vendor_registration_sections_table.php, This script will just create vendor_registration_sections
		 * table if the table does not exists. And the same check is added in 2021_04_29_222211_create_vendor_registration_sections_table.php
		 * to only create the vendor_registration_sections if it does not exists. Then the script can continue whatever sequence processes
		 * that it has.
		 */

		Schema::create('vendor_registrations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('status');
			$table->timestamps();

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
		});

		if (!Schema::hasTable('vendor_registration_sections')) {
			Schema::create('vendor_registration_sections', function(Blueprint $table)
			{
				$table->increments('id');
				$table->unsignedInteger('vendor_registration_id');
				$table->unsignedInteger('section');
				$table->unsignedInteger('status_id');
				$table->unsignedInteger('amendment_status');
				$table->text('amendment_remarks')->nullable();
				$table->timestamps();

				$table->foreign('vendor_registration_id')->references('id')->on('vendor_registrations')->onDelete('cascade');

				$table->index('vendor_registration_id');
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
		Schema::dropIfExists('vendor_registrations');
		Schema::dropIfExists('vendor_registration_sections');
	}

}
