<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectReportChartsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_report_charts', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('project_report_type_mapping_id')->unsigned();
            $table->tinyInteger('chart_type')->unsigned();  // Example: Graph, Pie, Table
            $table->boolean('is_locked')->default(false);
            $table->string('title')->nullable();
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
		Schema::drop('project_report_charts');
	}

}
