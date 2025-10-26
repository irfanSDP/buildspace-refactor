<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterVendorRegistrationAndPrequalificationModuleParametersTableAddVendorManagementGradeIdColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_registration_and_prequalification_module_parameters', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_management_grade_id')->nullable();

			$table->foreign('vendor_management_grade_id')->references('id')->on('vendor_management_grades')->onDelete('cascade');
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
			$table->dropColumn('vendor_management_grade_id');
		});
	}

}
