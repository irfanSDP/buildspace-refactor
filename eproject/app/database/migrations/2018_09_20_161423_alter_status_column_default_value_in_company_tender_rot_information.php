<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;

class AlterStatusColumnDefaultValueInCompanyTenderRotInformation extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        DB::statement('ALTER TABLE company_tender_rot_information ALTER COLUMN status SET DEFAULT ' . ContractorCommitmentStatus::PENDING);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        DB::statement('ALTER TABLE company_tender_rot_information ALTER COLUMN status SET DEFAULT ' . ContractorCommitmentStatus::OK);
	}

}
