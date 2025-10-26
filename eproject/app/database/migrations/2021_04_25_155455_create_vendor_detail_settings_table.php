<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorDetailSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_detail_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('name_instructions')->default('');
			$table->text('address_instructions')->default('');
			$table->text('contract_group_category_instructions')->default('');
			$table->text('vendor_category_instructions')->default('');
			$table->text('contact_person_instructions')->default('');
			$table->text('reference_number_instructions')->default('');
			$table->text('tax_registration_number_instructions')->default('');
			$table->text('email_instructions')->default('');
			$table->text('telephone_instructions')->default('');
			$table->text('fax_instructions')->default('');
			$table->text('country_instructions')->default('');
			$table->text('state_instructions')->default('');
			$table->timestamps();
		});

		\PCK\VendorDetailSetting\VendorDetailSetting::create(array());
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_detail_settings');
	}

}
