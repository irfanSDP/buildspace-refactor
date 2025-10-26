<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;

class AddStatusColumnToCompanyTenderLotInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_tender_lot_information', function(Blueprint $table) 
		{
			$table->integer('status')->default(ContractorCommitmentStatus::PENDING);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('company_tender_lot_information', function(Blueprint $table)
        {
            $table->dropColumn('status');
        });
	}

}
