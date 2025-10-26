<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubmittedAtColumnToVendorRegistrationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_registrations', function(Blueprint $table)
		{
			$table->timestamp('submitted_at')->nullable();
		});

		foreach(PCK\VendorRegistration\VendorRegistration::withTrashed()->get() as $vendorRegistration)
		{
			if(!$vendorRegistration->isDraft())
			{
				$vendorRegistration->submitted_at = $vendorRegistration->updated_at;
				$vendorRegistration->save();
			}
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_registrations', function(Blueprint $table)
		{
			$table->dropColumn('submitted_at');
		});
	}

}
