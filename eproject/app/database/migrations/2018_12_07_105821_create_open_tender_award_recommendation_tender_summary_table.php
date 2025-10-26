<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOpenTenderAwardRecommendationTenderSummaryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('open_tender_award_recommendation_tender_summary', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_id');
			$table->decimal('consultant_estimate', 19, 2)->nullable();
			$table->decimal('budget', 19, 2)->nullable();
			$table->unsignedInteger('updated_by');
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
		Schema::drop('open_tender_award_recommendation_tender_summary');
	}

}
