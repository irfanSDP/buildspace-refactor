<?php

use PCK\Projects\Project;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValueForCurrentTenderStatusInProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('projects', function (Blueprint $table)
		{
			$value = Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER;

			DB::statement("ALTER TABLE projects ALTER COLUMN current_tender_status SET DEFAULT '{$value}';");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('projects', function (Blueprint $table)
		{
			$value = Project::STATUS_TYPE_LIST_OF_TENDERER;

			DB::statement("ALTER TABLE projects ALTER COLUMN current_tender_status SET DEFAULT '{$value}';");
		});
	}

}