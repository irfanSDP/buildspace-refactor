<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropProjectIdFromSiteManagementMaintenanceTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('labours', function (Blueprint $table)
		{
			$table->dropColumn('project_id');
		});

		Schema::table('machinery', function (Blueprint $table)
		{
			$table->dropColumn('project_id');
		});

		Schema::table('rejected_materials', function (Blueprint $table)
		{
			$table->dropColumn('project_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('labours', function (Blueprint $table)
		{
			$table->unsignedInteger('project_id')->nullable()->index();
			$table->foreign('project_id')->references('id')->on('projects');
		});

		Schema::table('machinery', function (Blueprint $table)
		{
			$table->unsignedInteger('project_id')->nullable()->index();
			$table->foreign('project_id')->references('id')->on('projects');
		});

		Schema::table('rejected_materials', function (Blueprint $table)
		{
			$table->unsignedInteger('project_id')->nullable()->index();
			$table->foreign('project_id')->references('id')->on('projects');
		});
	}

}
