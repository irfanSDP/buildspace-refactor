<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllowContractorProposeOwnCompletionPeriodColumnToTenderCallingTenderInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tender_calling_tender_information', function(Blueprint $table)
		{
			$table->boolean('allow_contractor_propose_own_completion_period')->default(false);
		});

		foreach(\PCK\TenderCallingTenderInformation\TenderCallingTenderInformation::all() as $callingTenderInfo)
		{
			$callingTenderInfo->allow_contractor_propose_own_completion_period = $callingTenderInfo->tender->listOfTendererInformation->allow_contractor_propose_own_completion_period;

			$callingTenderInfo->save();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tender_calling_tender_information', function(Blueprint $table)
		{
			$table->dropColumn('allow_contractor_propose_own_completion_period');
		});
	}

}
