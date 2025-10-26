<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVendorDeclarationColumnToVendorRegistrationAndPrequalificationModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_registration_and_prequalification_module_parameters', function(Blueprint $table)
		{
			$table->text('vendor_declaration')->default('');
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
			$table->dropColumn('vendor_declaration');
		});
	}

}
