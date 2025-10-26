<?php

use PCK\Helpers\CustomBlueprint;
use PCK\Helpers\CustomMigration;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformation;

class CreateTenderCallingTenderInformationTable extends CustomMigration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$this->schema->create('tender_calling_tender_information', function (CustomBlueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_id')->index();
			$table->dateTime('date_of_calling_tender');
			$table->dateTime('date_of_closing_tender');
			$table->smallInteger('status', false, true)->default(TenderListOfTendererInformation::IN_PROGRESS);
			$table->signAbleColumns();
			$table->timestamps();

			$table->foreign('tender_id')->references('id')->on('tenders');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$this->schema->drop('tender_calling_tender_information');
	}

}