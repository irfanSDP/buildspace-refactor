<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOpenTenderAwardRecommendationReportEditLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('open_tender_award_recommendation_report_edit_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('open_tender_award_recommendation_id');
			$table->unsignedInteger('user_id');
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
		Schema::drop('open_tender_award_recommendation_report_edit_logs');
	}

}
