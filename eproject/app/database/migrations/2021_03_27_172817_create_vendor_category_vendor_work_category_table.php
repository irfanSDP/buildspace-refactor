<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorCategoryVendorWorkCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_category_vendor_work_category', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_category_id');
			$table->unsignedInteger('vendor_work_category_id');
			$table->timestamps();

			$table->foreign('vendor_category_id')->references('id')->on('vendor_categories')->onDelete('cascade');
			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');

			$table->unique(['vendor_category_id', 'vendor_work_category_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_category_vendor_work_category');
	}

}
