<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterVendorPreQualificationTemplateFormsTableAddVendorManagementGradeIdColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_pre_qualification_template_forms', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_management_grade_id')->nullable();

			$table->index('vendor_management_grade_id');

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
		Schema::table('vendor_pre_qualification_template_forms', function(Blueprint $table)
		{
			$table->dropColumn('vendor_management_grade_id');
		});
	}

}
