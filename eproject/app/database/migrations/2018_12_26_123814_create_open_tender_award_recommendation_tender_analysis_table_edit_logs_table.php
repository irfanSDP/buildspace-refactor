<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOpenTenderAwardRecommendationTenderAnalysisTableEditLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('open_tender_award_recommendation_tender_analysis_table_edit_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_id');
			$table->string('table_name');
			$table->string('type');
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
		Schema::drop('open_tender_award_recommendation_tender_analysis_table_edit_logs');
	}

}
