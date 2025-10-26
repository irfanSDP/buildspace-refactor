<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;

class CreateCompanyTenderCallingTenderInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_tender_calling_tender_information', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('tender_calling_tender_information_id');
			$table->smallInteger('status', false, true)->default(ContractorCommitmentStatus::TENDER_OK);
			$table->timestamps();

			$table->unique(array( 'company_id', 'tender_calling_tender_information_id' ));

			$table->foreign('company_id')->references('id')->on('companies');
			$table->foreign('tender_calling_tender_information_id')->references('id')->on('tender_calling_tender_information');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('company_tender_calling_tender_information');
	}

}