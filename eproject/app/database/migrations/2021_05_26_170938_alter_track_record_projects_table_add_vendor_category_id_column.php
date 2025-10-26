<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\TrackRecordProject\TrackRecordProject;
use PCK\VendorWorkCategory\VendorWorkCategory;

class AlterTrackRecordProjectsTableAddVendorCategoryIdColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_category_id')->nullable();

			$table->index('vendor_category_id');

			$table->foreign('vendor_category_id')->references('id')->on('vendor_categories')->onDelete('cascade');
		});

		foreach(TrackRecordProject::all() as $projectTrackRecord)
		{
			$vendorWorkCategory = VendorWorkCategory::find($projectTrackRecord->vendor_work_category_id);
			
			$projectTrackRecord->vendor_category_id = $vendorWorkCategory->vendorCategories()->orderBy('id', 'ASC')->first()->id;
			$projectTrackRecord->save();
		}

		DB::statement('ALTER TABLE track_record_projects ALTER COLUMN vendor_category_id SET NOT NULL');
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
			$table->dropColumn('vendor_category_id');
		});
	}

}
