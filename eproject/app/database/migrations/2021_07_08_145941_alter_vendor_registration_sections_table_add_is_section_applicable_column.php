<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorRegistration\Section;
use PCK\Verifier\Verifier;

class AlterVendorRegistrationSectionsTableAddIsSectionApplicableColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_registration_sections', function(Blueprint $table)
		{
			$table->boolean('is_section_applicable')->default(true);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_registration_sections', function(Blueprint $table)
		{
			$table->dropColumn('is_section_applicable');
		});
	}

}
