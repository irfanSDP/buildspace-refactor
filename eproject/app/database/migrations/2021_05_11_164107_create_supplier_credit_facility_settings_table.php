<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\SupplierCreditFacility\SupplierCreditFacilitySetting;

class CreateSupplierCreditFacilitySettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supplier_credit_facility_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('has_attachments')->default(false);
			$table->timestamps();
		});

		if(is_null(SupplierCreditFacilitySetting::first()))
		{
			$record = new SupplierCreditFacilitySetting();
			$record->save();
		}
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('supplier_credit_facility_settings');
	}

}
