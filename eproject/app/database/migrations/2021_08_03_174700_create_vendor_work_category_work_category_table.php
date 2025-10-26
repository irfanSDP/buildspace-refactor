<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\WorkCategories\WorkCategory;

class CreateVendorWorkCategoryWorkCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_work_category_work_category', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_work_category_id');
			$table->unsignedInteger('work_category_id');

			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');
			$table->foreign('work_category_id')->references('id')->on('work_categories')->onDelete('cascade');

			$table->index('vendor_work_category_id', 'vendor_work_category_work_category_vendor_work_category_id_idx');
			$table->index('work_category_id', 'vendor_work_category_work_category_work_category_id_idx');
		});

		$this->seed();
	}

	protected function seed()
	{
		$rows = [];

		foreach(WorkCategory::whereNotNull('vendor_work_category_id')->get() as $workCategory)
		{
			$rows[] = [
			    'vendor_work_category_id' => $workCategory->vendor_work_category_id,
			    'work_category_id'        => $workCategory->id,
			];
		}

		if(!empty($rows)) \DB::table('vendor_work_category_work_category')->insert($rows);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_work_category_work_category');
	}

}
