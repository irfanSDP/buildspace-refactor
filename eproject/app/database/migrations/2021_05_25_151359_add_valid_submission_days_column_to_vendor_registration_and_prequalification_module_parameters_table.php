<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValidSubmissionDaysColumnToVendorRegistrationAndPrequalificationModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_registration_and_prequalification_module_parameters', function(Blueprint $table)
		{
			$table->unsignedInteger('valid_submission_days')->default(\PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter::VALID_SUBMISSION_DAYS_DEFAULT_VALUE);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_registration_and_prequalification_module_parameters', function(Blueprint $table)
		{
			$table->dropColumn('valid_submission_days');
		});
	}

}
