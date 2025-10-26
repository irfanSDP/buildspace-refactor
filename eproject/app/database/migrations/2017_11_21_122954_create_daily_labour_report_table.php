<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyLabourReportTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('daily_labour_reports', function(Blueprint $table)
		{
			$table->increments('id');
			$table->date('date');
			$table->unsignedInteger('weather_id')->index();
			$table->integer('bill_column_setting_id')->nullable();
			$table->unsignedInteger('unit')->nullable();
			$table->integer('project_structure_location_code_id');
			$table->integer('pre_defined_location_code_id');
			$table->integer('contractor_id');
			$table->string('work_description');
			$table->string('remark');
			$table->string('path_to_photo')->nullable(); 
			$table->unsignedInteger('submitted_by');
			$table->unsignedInteger('project_id')->index();
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('weather_id')->references('id')->on('weathers');
			$table->foreign('contractor_id')->references('id')->on('companies');
			$table->foreign('submitted_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('daily_labour_reports');
	}

}
