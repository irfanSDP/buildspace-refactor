<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorWorkSubcategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_work_subcategories', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_work_category_id');
			$table->string('name')->unique();
			$table->boolean('hidden')->default(false);
			$table->timestamps();

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
		Schema::drop('vendor_work_subcategories');
	}

}
