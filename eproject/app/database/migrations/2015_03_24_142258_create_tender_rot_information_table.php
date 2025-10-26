<?php

use PCK\Helpers\CustomBlueprint;
use PCK\Helpers\CustomMigration;

class CreateTenderRotInformationTable extends CustomMigration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$this->schema->create('tender_rot_information', function (CustomBlueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_id')->index();
			$table->dateTime('proposed_date_of_calling_tender');
			$table->dateTime('proposed_date_of_closing_tender');
			$table->dateTime('target_date_of_site_possession');
			$table->decimal('budget', 19, 2);
			$table->decimal('consultant_estimates', 19, 2);
			$table->smallInteger('status', false, true);
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
		$this->schema->drop('tender_rot_information');
	}

}