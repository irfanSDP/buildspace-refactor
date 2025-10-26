<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectReportTypeMappingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_report_type_mappings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_report_type_id');
			$table->integer('project_type');
			$table->unsignedInteger('project_report_id')->nullable();
			$table->timestamps();
			
			$table->index('project_report_type_id');
			$table->index('project_report_id');

			$table->foreign('project_report_type_id')->references('id')->on('project_report_types')->onDelete('cascade');
			$table->foreign('project_report_id')->references('id')->on('project_reports')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('project_report_type_mappings');
	}

}
