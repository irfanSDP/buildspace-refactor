<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\VendorRegistration\VendorRegistration;

class ChangeIsRenewalColumnToSubmissionTypeInVendorRegistrationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_registrations', function(Blueprint $table)
		{
			$table->unsignedInteger('submission_type')->default(VendorRegistration::SUBMISSION_TYPE_NEW);
		});

		foreach(VendorRegistration::withTrashed()->get() as $vendorRegistration)
		{
			if($vendorRegistration->isFirst())
			{
				continue;
			}
			elseif($vendorRegistration->is_renewal)
			{
				$vendorRegistration->submission_type = VendorRegistration::SUBMISSION_TYPE_RENEWAL;
			}
			else
			{
				$vendorRegistration->submission_type = VendorRegistration::SUBMISSION_TYPE_UPDATE;
			}

			$vendorRegistration->save();
		}

		Schema::table('vendor_registrations', function(Blueprint $table)
		{
			$table->dropColumn('is_renewal');
		});
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
			$table->boolean('is_renewal')->default(false);
		});

		VendorRegistration::withTrashed()->where('submission_type', '=', VendorRegistration::SUBMISSION_TYPE_RENEWAL)->update(array('is_renewal' => true));

		Schema::table('vendor_registrations', function(Blueprint $table)
		{
			$table->dropColumn('submission_type');
		});
	}

}
