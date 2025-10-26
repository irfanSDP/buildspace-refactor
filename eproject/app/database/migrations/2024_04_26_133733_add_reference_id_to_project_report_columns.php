<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferenceIdToProjectReportColumns extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('project_report_columns', 'reference_id'))
        {
            Schema::table('project_report_columns', function (Blueprint $table) {
                $table->integer('reference_id')->unsigned()->nullable()->index()->after('project_report_id');
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
        if (Schema::hasColumn('project_report_columns', 'reference_id'))
        {
            Schema::table('project_report_columns', function (Blueprint $table) {
                $table->dropColumn('reference_id');
            });
        }
	}

}
