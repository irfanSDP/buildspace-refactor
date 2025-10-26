<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectReportColumnsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_report_columns', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_report_id');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->integer('type')->nullable();
            $table->integer('parent_id')->nullable();
			$table->integer('priority');
			$table->timestamps();

			$table->index('project_report_id');
			$table->index('parent_id');

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
		Schema::drop('project_report_columns');
	}

}
