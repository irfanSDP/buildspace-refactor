<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\TrackRecordProject\TrackRecordProject;
use PCK\TrackRecordProject\TrackRecordProjectVendorWorkSubcategory;

class CreateTrackRecordProjectVendorWorkSubcategoriesTable extends Migration
{
	public function up()
	{
		Schema::create('track_record_project_vendor_work_subcategories', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('track_record_project_id');
			$table->unsignedInteger('vendor_work_subcategory_id');
			$table->timestamps();

			$table->index('track_record_project_id');
			$table->index('vendor_work_subcategory_id');

			$table->foreign('track_record_project_id')->references('id')->on('track_record_projects')->onDelete('cascade');
			$table->foreign('vendor_work_subcategory_id')->references('id')->on('vendor_work_subcategories')->onDelete('cascade');
		});

		DB::statement('ALTER TABLE track_record_project_vendor_work_subcategories ADD CONSTRAINT trk_rec_proj_id_vendor_work_sub_cat_id_unique UNIQUE (track_record_project_id, vendor_work_subcategory_id);');

		// seed data
		foreach(TrackRecordProject::all() as $trackRecordProject)
		{
			if(is_null($trackRecordProject->vendor_work_subcategory_id)) continue;

			$record = TrackRecordProjectVendorWorkSubcategory::where('track_record_project_id', $trackRecordProject->id)->where('vendor_work_subcategory_id', $trackRecordProject->vendor_work_subcategory_id)->first();

			if(is_null($record))
			{
				$record = new TrackRecordProjectVendorWorkSubcategory();
				$record->track_record_project_id = $trackRecordProject->id;
				$record->vendor_work_subcategory_id = $trackRecordProject->vendor_work_subcategory_id;
				$record->save();
			}
		}
	}

	public function down()
	{
		Schema::drop('track_record_project_vendor_work_subcategories');
	}
}
