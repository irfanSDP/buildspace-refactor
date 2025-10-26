<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractorAdjustmentAmountColumnToCompanyTenderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_tender', function(Blueprint $table)
		{
            $table->decimal('contractor_adjustment_amount', 19, 2)->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('company_tender', function(Blueprint $table)
		{
			$table->dropColumn('contractor_adjustment_amount');
		});
	}

}
