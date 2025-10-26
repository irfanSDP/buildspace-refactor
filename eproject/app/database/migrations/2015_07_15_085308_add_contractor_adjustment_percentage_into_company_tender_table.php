<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractorAdjustmentPercentageIntoCompanyTenderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_tender', function (Blueprint $table)
		{
			$table->decimal('contractor_adjustment_percentage')->default(0);
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
			$table->dropColumn(array( 'contractor_adjustment_percentage' ));
		});
	}

}