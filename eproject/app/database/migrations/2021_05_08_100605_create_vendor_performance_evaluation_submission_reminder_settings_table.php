<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPerformanceEvaluationSubmissionReminderSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_performance_evaluation_submission_reminder_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('number_of_days_before');

			$table->unique('number_of_days_before');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_performance_evaluation_submission_reminder_settings');
	}

}
