<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorManagementInstructionSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_management_instruction_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('company_personnel')->default('');
			$table->text('project_track_record')->default('');
			$table->text('supplier_credit_facilities')->default('');
			$table->text('payment')->default('');
			$table->timestamps();
		});

		PCK\VendorManagement\InstructionSetting::create(array());
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_management_instruction_settings');
	}

}
