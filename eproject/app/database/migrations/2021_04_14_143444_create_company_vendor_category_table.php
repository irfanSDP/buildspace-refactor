<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyVendorCategoryTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_vendor_category', function(Blueprint $table)
		{
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('vendor_category_id');

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
			$table->foreign('vendor_category_id')->references('id')->on('vendor_categories')->onDelete('cascade');

			$table->unique(array('company_id', 'vendor_category_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('company_vendor_category');
	}

}
