<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSingleEntryToProjectReportColumns extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('project_report_columns', 'single_entry'))
        {
            Schema::table('project_report_columns', function (Blueprint $table) {
                $table->boolean('single_entry')->default(false)->after('type');
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasColumn('project_report_columns', 'single_entry'))
        {
            Schema::table('project_report_columns', function (Blueprint $table) {
                $table->dropColumn('single_entry');
            });
        }
	}

}
