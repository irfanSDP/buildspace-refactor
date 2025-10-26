<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\ProjectReport\ProjectReport;

class ModifyProjectReportColumnsTableAddDepthColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('project_report_columns', function(Blueprint $table)
		{
			$table->integer('depth')->default(0);
		});

		$this->migrateData();
	}

	private function migrateData()
	{
		foreach(ProjectReport::withTrashed()->get() as $projectReport)
		{
			foreach($projectReport->columns as $column)
			{
				$column->depth = is_null($column->parent_id) ? 0 : 1;
				$column->save();
			}
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('project_report_columns', function(Blueprint $table)
		{
			$table->dropColumn('depth');
		});
	}

}
