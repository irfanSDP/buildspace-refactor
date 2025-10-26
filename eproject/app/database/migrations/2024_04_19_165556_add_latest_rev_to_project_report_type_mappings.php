<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLatestRevToProjectReportTypeMappings extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('project_report_type_mappings', 'latest_rev'))
        {
            Schema::table('project_report_type_mappings', function (Blueprint $table) {
                $table->boolean('latest_rev')->default(true)->after('project_report_id');
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
        if (Schema::hasColumn('project_report_type_mappings', 'latest_rev'))
        {
            Schema::table('project_report_type_mappings', function (Blueprint $table) {
                $table->dropColumn('latest_rev');
            });
        }
	}
}
