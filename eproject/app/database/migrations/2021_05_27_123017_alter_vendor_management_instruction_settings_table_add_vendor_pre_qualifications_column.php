<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterVendorManagementInstructionSettingsTableAddVendorPreQualificationsColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_management_instruction_settings', function(Blueprint $table)
		{
			$table->text('vendor_pre_qualifications')->default('');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_management_instruction_settings', function(Blueprint $table)
		{
			$table->dropColumn('vendor_pre_qualifications');
		});
	}

}
