<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\TrackRecordProject\ProjectTrackRecordSetting;

class CreateProjectTrackRecordSettingsTable extends Migration
{
	public function up()
	{
		Schema::create('project_track_record_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('project_detail_attachments')->default(false);
			$table->boolean('project_quality_achievement_attachments')->default(false);
			$table->boolean('project_award_recognition_attachments')->default(false);
			$table->timestamps();
		});

		if(is_null(ProjectTrackRecordSetting::first()))
		{
			$record = new ProjectTrackRecordSetting();
			$record->save();
		}
	}

	public function down()
	{
		Schema::drop('project_track_record_settings');
	}
}
