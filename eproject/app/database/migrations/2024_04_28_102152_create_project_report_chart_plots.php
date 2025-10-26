<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectReportChartPlots extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_report_chart_plots', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('project_report_chart_id')->unsigned();
            $table->integer('category_column_id')->unsigned();
            $table->integer('value_column_id')->unsigned();
            $table->tinyInteger('plot_type')->unsigned()->default(1);       // Example: Line, Bar
            $table->tinyInteger('data_grouping')->unsigned()->default(1);   // Example: Daily, Monthly, Quarterly, Yearly
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('project_report_chart_plots');
	}

}
