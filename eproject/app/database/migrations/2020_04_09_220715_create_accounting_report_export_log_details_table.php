<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccountingReportExportLogDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('TRUNCATE TABLE accounting_report_export_logs RESTART IDENTITY');

		Schema::create('accounting_report_export_log_details', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('accounting_report_export_log_id');
            $table->unsignedInteger('project_code_setting_id');
            $table->timestamps();

            $table->foreign('accounting_report_export_log_id')->references('id')->on('accounting_report_export_logs');

			$table->index('accounting_report_export_log_id');
			$table->index('project_code_setting_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('accounting_report_export_log_details');
	}

}