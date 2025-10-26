<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\Projects\Project;

class AddCurrentTenderStatusColumnIntoProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('projects', function (Blueprint $table)
		{
			$table->unsignedInteger('current_tender_status')
				->default(Project::STATUS_TYPE_LIST_OF_TENDERER)
				->index();
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
			$table->dropColumn('current_tender_status');
		});
	}

}