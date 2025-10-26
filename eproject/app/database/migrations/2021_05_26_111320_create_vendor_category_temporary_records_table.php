<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVendorCategoryTemporaryRecordsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_category_temporary_records', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_registration_id');
			$table->unsignedInteger('vendor_category_id');
			$table->timestamps();

			$table->index('vendor_registration_id');
			$table->index('vendor_category_id');

			$table->foreign('vendor_category_id')->references('id')->on('vendor_categories')->onDelete('cascade');
			$table->foreign('vendor_registration_id')->references('id')->on('vendor_registrations')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_category_temporary_records');
	}

}
