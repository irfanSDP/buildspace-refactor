<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectReportNotificationContents extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_report_notification_contents', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('project_report_notification_id')->unsigned();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
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
		Schema::drop('project_report_notification_contents');
	}

}
