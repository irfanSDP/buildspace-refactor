<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectReportNotificationPeriods extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_report_notification_periods', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('project_report_notification_id')->unsigned();
            $table->integer('period_value')->unsigned()->default(1);
            $table->integer('period_type')->unsigned()->default(1);
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
		Schema::drop('project_report_notification_periods');
	}

}
