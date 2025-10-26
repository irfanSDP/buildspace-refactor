<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusKeyToCompanyTenderCallingTenderInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_tender_calling_tender_information', function(Blueprint $table)
		{
            $table->string('status_key')
                ->nullable()
                ->unique()
                ->default(null);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('company_tender_calling_tender_information', function(Blueprint $table)
		{
            $table->dropColumn('status_key');
		});
	}

}
