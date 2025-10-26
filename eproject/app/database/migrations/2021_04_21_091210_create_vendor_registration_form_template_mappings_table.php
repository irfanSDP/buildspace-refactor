<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVendorRegistrationFormTemplateMappingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_registration_form_template_mappings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('contract_group_category_id');
			$table->unsignedInteger('business_entity_type_id')->nullable();
			$table->unsignedInteger('dynamic_form_id');
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

			$table->index('contract_group_category_id');
			$table->index('business_entity_type_id');
			$table->index('dynamic_form_id');
			$table->index('created_by');
			$table->index('updated_by');

			$table->foreign('contract_group_category_id')->references('id')->on('contract_group_categories')->onDelete('cascade');
			$table->foreign('business_entity_type_id')->references('id')->on('business_entity_types')->onDelete('cascade');
			$table->foreign('dynamic_form_id')->references('id')->on('dynamic_forms')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_registration_form_template_mappings');
	}

}
