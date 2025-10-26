<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectReportActionLogsTable extends Migration
{
	public function up()
	{
		Schema::create('project_report_action_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_report_id');
			$table->integer('action_type');
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

            $table->index('project_report_id');
            $table->index('created_by');
            $table->index('updated_by');

            $table->foreign('project_report_id')->references('id')->on('project_reports')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});
	}

	public function down()
	{
		Schema::drop('project_report_action_logs');
	}
}
