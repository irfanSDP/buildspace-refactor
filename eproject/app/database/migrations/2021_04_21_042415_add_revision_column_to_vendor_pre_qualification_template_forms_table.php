<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRevisionColumnToVendorPreQualificationTemplateFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_pre_qualification_template_forms', function(Blueprint $table)
		{
			$table->unsignedInteger('revision')->default(0);
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
			$table->dropColumn('revision');
		});
	}

}
