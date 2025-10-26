<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPreQualificationSetupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_pre_qualification_setups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_category_id');
			$table->unsignedInteger('vendor_work_category_id');
			$table->boolean('pre_qualification_required')->default(true);
			$table->timestamps();

			$table->foreign('vendor_category_id')->references('id')->on('vendor_categories')->onDelete('cascade');
			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');

			$table->index(array('vendor_category_id', 'vendor_work_category_id'), 'vendor_pre_qualification_setups_idx');
			$table->unique(array('vendor_category_id', 'vendor_work_category_id'), 'vendor_pre_qualification_setups_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_pre_qualification_setups');
	}

}
