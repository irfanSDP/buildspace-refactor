<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\TrackRecordProject\TrackRecordProject;

class AlterTrackRecordProjectsTableDropVendorWorkSubcategoryIdColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->dropColumn('vendor_work_subcategory_id');
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
			$table->unsignedInteger('vendor_work_subcategory_id')->nullable();

			$table->foreign('vendor_work_subcategory_id')->references('id')->on('vendor_work_subcategories')->onDelete('cascade');
		});

		// seed data
		foreach(TrackRecordProject::all() as $trackRecordProject)
		{
			$record = $trackRecordProject->trackRecordProjectVendorWorkSubcategories()->orderBy('id', 'ASC')->first();

			if(is_null($record)) continue;

			$trackRecordProject 							= TrackRecordProject::find($record->track_record_project_id);
			$trackRecordProject->vendor_work_subcategory_id = $record->vendor_work_subcategory_id;
			$trackRecordProject->save();
		}
	}

}