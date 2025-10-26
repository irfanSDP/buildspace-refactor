<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorRegistration\Section;

class CreateVendorRegistrationSectionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
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

		$records = \DB::select(\DB::raw("SELECT id FROM vendor_registrations;"));

		foreach($records as $record)
		{
			$vendorRegistration = VendorRegistration::find($record->id);

			if(is_null($vendorRegistration)) continue;

			Section::initiate($vendorRegistration);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('vendor_registration_sections');
	}

}
