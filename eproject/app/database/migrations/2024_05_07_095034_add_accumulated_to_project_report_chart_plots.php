<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccumulatedToProjectReportChartPlots extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('project_report_chart_plots', 'is_accumulated'))
        {
            Schema::table('project_report_chart_plots', function (Blueprint $table) {
                $table->boolean('is_accumulated')->default(false)->after('data_grouping');
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
        if (Schema::hasColumn('project_report_chart_plots', 'is_accumulated'))
        {
            Schema::table('project_report_chart_plots', function (Blueprint $table) {
                $table->dropColumn('is_accumulated');
            });
        }
	}

}
