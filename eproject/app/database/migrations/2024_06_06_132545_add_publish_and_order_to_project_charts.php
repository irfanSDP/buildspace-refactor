<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPublishAndOrderToProjectCharts extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('project_report_charts', 'is_published'))
        {
            Schema::table('project_report_charts', function (Blueprint $table) {
                $table->boolean('is_published')->default(false)->after('is_locked');
            });
        }

        if (! Schema::hasColumn('project_report_charts', 'order'))
        {
            Schema::table('project_report_charts', function (Blueprint $table) {
                $table->integer('order')->unsigned()->default(1)->after('chart_type');
            });

            $charts = DB::table('project_report_charts')->orderBy('id')->get();
            $order = 1;
            foreach ($charts as $chart)
            {
                DB::table('project_report_charts')->where('id', $chart->id)->update(array('order' => $order));
                $order++;
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
        if (Schema::hasColumn('project_report_charts', 'is_published'))
        {
            Schema::table('project_report_charts', function (Blueprint $table) {
                $table->dropColumn('is_published');
            });
        }

        if (Schema::hasColumn('project_report_charts', 'order'))
        {
            Schema::table('project_report_charts', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
	}

}
