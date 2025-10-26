<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectWorkCategoryVendorWorkCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_work_category_vendor_work_category', function(Blueprint $table)
		{
			$table->unsignedInteger('work_category_id');
			$table->unsignedInteger('vendor_work_category_id');

			$table->foreign('work_category_id')->references('id')->on('work_categories')->onDelete('cascade');
			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('project_work_category_vendor_work_category');
	}

}
