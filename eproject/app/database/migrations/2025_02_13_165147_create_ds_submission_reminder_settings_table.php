<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsSubmissionReminderSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_submission_reminder_settings', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('ds_module_parameter_id')->unsigned()->default(1);
            $table->integer('number_of_days_before')->unsigned();
			$table->timestamps();

            $table->foreign('ds_module_parameter_id')->references('id')->on('ds_module_parameters')->onDelete('cascade');
            $table->unique(['ds_module_parameter_id', 'number_of_days_before'], 'ds_module_parameter_id_number_of_days_before_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_submission_reminder_settings');
	}

}
