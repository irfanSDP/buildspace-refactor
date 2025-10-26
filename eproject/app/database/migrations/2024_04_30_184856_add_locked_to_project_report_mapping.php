<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLockedToProjectReportMapping extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('project_report_type_mappings', 'is_locked'))
        {
            Schema::table('project_report_type_mappings', function (Blueprint $table) {
                $table->boolean('is_locked')->default(false)->after('latest_rev');
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
        if (Schema::hasColumn('project_report_type_mappings', 'is_locked'))
        {
            Schema::table('project_report_type_mappings', function (Blueprint $table) {
                $table->dropColumn('is_locked');
            });
        }
	}

}
