<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUniqueConstraintForVendorPreQualificationTemplateFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_pre_qualification_template_forms', function(Blueprint $table)
		{
			$table->dropUnique('vendor_pre_qualification_template_forms_unique');

			$table->unique(array('vendor_work_category_id', 'revision'), 'vendor_pre_qualification_template_forms_unique');
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
			$table->dropUnique('vendor_pre_qualification_template_forms_unique');

			$table->unique('vendor_work_category_id', 'vendor_pre_qualification_template_forms_unique');
		});
	}

}
