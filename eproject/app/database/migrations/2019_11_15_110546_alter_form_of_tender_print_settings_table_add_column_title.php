<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\FormOfTender\PrintSettings;

class AlterFormOfTenderPrintSettingsTableAddColumnTitle extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('form_of_tender_print_settings', function(Blueprint $table)
		{
			$table->string('title_text')->default(PrintSettings::DEFAULT_TITLE);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('form_of_tender_print_settings', function(Blueprint $table)
		{
			$table->dropColumn('title_text');
		});
	}

}
