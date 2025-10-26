<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ModifyOpenTenderAwardRecommendationBillDetailsTableRemoveUniqueConstraint extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('open_tender_award_recommendation_bill_details', function(Blueprint $table)
		{
			$table->dropUnique('open_tender_award_recommendation_bill_details_buildspace_bill_id_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('open_tender_award_recommendation_bill_details', function(Blueprint $table)
		{
			$table->unique('buildspace_bill_id');
		});
	}

}
