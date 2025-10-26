<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOpenTenderAwardRecommendationBillDetails extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('open_tender_award_recommendation_bill_details', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_id');
			$table->unsignedInteger('buildspace_bill_id')->unique();
			$table->decimal('consultant_pte', 19, 2)->default(0.0);
			$table->decimal('budget', 19, 2)->default(0.0);
			$table->decimal('bill_amount', 19, 2);
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
		Schema::drop('open_tender_award_recommendation_bill_details');
	}

}
