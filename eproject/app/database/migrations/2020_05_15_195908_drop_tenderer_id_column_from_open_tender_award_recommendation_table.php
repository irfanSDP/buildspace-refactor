<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropTendererIdColumnFromOpenTenderAwardRecommendationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('open_tender_award_recommendation', function (Blueprint $table)
		{
			$table->dropColumn('tenderer_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('open_tender_award_recommendation', function (Blueprint $table)
		{
			$table->unsignedInteger('tenderer_id')->nullable()->default(null);
		});
	}
}