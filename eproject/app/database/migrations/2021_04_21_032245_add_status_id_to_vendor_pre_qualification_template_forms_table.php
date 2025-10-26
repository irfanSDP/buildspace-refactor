<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusIdToVendorPreQualificationTemplateFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_pre_qualification_template_forms', function(Blueprint $table)
		{
			$table->unsignedInteger('status_id')->default(\PCK\VendorPreQualification\TemplateForm::STATUS_DRAFT);
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
			$table->dropColumn('status_id');
		});
	}

}
