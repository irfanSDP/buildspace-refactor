<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccountingReportExportLogItemCodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('accounting_report_export_log_item_codes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('accounting_report_export_log_detail_id');
			$table->unsignedInteger('item_code_setting_id');
			$table->timestamps();

			$table->foreign('accounting_report_export_log_detail_id')->references('id')->on('accounting_report_export_log_details');
			$table->index('accounting_report_export_log_detail_id');
			$table->index('item_code_setting_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('accounting_report_export_log_item_codes');
	}
}
