<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToTrackRecordProjectsFirstTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->timestamp('project_awarded_at');
			$table->text('recognition_awards')->nullable();
			$table->timestamp('recognition_awards_date')->nullable();
			$table->text('remarks')->nullable();
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
			$table->dropColumn('project_awarded_at');
			$table->dropColumn('recognition_awards');
			$table->dropColumn('recognition_awards_date');
			$table->dropColumn('remarks');
		});
	}

}
