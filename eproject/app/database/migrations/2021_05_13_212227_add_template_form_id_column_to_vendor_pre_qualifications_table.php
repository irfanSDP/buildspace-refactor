<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTemplateFormIdColumnToVendorPreQualificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_pre_qualifications', function(Blueprint $table)
		{
			$table->unsignedInteger('template_form_id');

			$table->foreign('template_form_id')->references('id')->on('vendor_pre_qualification_template_forms')->onDelete('cascade');
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
			$table->dropColumn('template_form_id');
		});
	}

}
