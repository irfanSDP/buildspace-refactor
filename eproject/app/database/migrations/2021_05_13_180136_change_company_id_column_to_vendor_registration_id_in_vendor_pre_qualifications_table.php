<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCompanyIdColumnToVendorRegistrationIdInVendorPreQualificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_pre_qualifications', function(Blueprint $table)
		{
			$table->dropColumn('company_id');
			$table->unsignedInteger('vendor_registration_id');

			$table->foreign('vendor_registration_id')->references('id')->on('vendor_registrations')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_pre_qualifications', function(Blueprint $table)
		{
			$table->dropColumn('vendor_registration_id');
			$table->unsignedInteger('company_id');
		});
	}

}
