<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveNotNullConstraintFromTemplateFormIdColumnInVendorPreQualificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('ALTER TABLE vendor_pre_qualifications ALTER COLUMN template_form_id DROP NOT NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('ALTER TABLE vendor_pre_qualifications ALTER COLUMN template_form_id SET NOT NULL');
	}

}
