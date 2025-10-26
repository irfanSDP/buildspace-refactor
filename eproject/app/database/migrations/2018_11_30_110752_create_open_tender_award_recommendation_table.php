<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationStatus;

class CreateOpenTenderAwardRecommendationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('open_tender_award_recommendation', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_id');
			$table->unsignedInteger('tenderer_id');
			$table->text('report_contents')->nullable();
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('submitted_for_verification_by')->nullable();
			$table->unsignedInteger('status')->default(OpenTenderAwardRecommendationStatus::EDITABLE);
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('open_tender_award_recommendation');
	}

}
