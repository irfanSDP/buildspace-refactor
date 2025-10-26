<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropStatusKeyColumnFromCompanyTenderCallingTenderInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_tender_calling_tender_information', function($table)
		{
			$table->dropColumn('status_key');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('company_tender_calling_tender_information', function($table)
		{
			$table->string('status_key', 255);
		});
	}

}
