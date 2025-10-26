<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPreQualificationTemplateFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_pre_qualification_template_forms', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_work_category_id');
			$table->unsignedInteger('weighted_node_id');
			$table->timestamps();

			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');
			$table->foreign('weighted_node_id')->references('id')->on('weighted_nodes')->onDelete('cascade');

			$table->unique('vendor_work_category_id', 'vendor_pre_qualification_template_forms_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_pre_qualification_template_forms');
	}

}
