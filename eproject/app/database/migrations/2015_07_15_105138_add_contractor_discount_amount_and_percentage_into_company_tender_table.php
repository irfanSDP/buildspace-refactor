<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddContractorDiscountAmountAndPercentageIntoCompanyTenderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_tender', function (Blueprint $table)
		{
			$table->decimal('original_tender_amount', 19, 2)->default(0);
			$table->decimal('discounted_percentage')->default(0);
			$table->decimal('discounted_amount', 19, 2)->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('company_tender', function (Blueprint $table)
		{
			$table->dropColumn(array( 'original_tender_amount', 'discounted_percentage', 'discounted_amount' ));
		});
	}

}