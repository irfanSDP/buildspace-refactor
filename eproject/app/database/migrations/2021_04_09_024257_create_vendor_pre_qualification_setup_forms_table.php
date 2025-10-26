<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPreQualificationSetupFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_pre_qualification_setup_forms', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_pre_qualification_setup_id');
			$table->unsignedInteger('weighted_node_id');
			$table->timestamps();

			$table->foreign('vendor_pre_qualification_setup_id')->references('id')->on('vendor_pre_qualification_setups')->onDelete('cascade');
			$table->foreign('weighted_node_id')->references('id')->on('weighted_nodes')->onDelete('cascade');

			$table->index(array('vendor_pre_qualification_setup_id', 'weighted_node_id'), 'vendor_pre_qualification_setup_forms_idx');
			$table->unique(array('vendor_pre_qualification_setup_id', 'weighted_node_id'), 'vendor_pre_qualification_setup_forms_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_pre_qualification_setup_forms');
	}

}
