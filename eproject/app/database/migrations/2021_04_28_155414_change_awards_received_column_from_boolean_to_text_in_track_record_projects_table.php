<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAwardsReceivedColumnFromBooleanToTextInTrackRecordProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->dropColumn('has_recognition_awards');
		});
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->string('awards_received')->nullable();
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
			$table->dropColumn('awards_received');
		});
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->boolean('has_recognition_awards')->default(false);
		});
	}

}
