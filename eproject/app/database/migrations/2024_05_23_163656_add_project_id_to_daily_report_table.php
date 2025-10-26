<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectIdToDailyReportTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('daily_report', function (Blueprint $table)
		{
			$table->unsignedInteger('project_id')->nullable()->index();
			$table->foreign('project_id')->references('id')->on('projects');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('daily_report', function (Blueprint $table)
		{
			$table->dropColumn('project_id');
		});
	}

}
