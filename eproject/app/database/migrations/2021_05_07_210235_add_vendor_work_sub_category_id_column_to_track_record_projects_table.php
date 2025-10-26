<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVendorWorkSubCategoryIdColumnToTrackRecordProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_work_subcategory_id')->nullable();

			$table->foreign('vendor_work_subcategory_id')->references('id')->on('vendor_work_subcategories')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->dropColumn('vendor_work_subcategory_id');
		});
	}

}
