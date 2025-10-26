<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddContractSumIncludesContingencySumColumnToRequestForVariationContractAndContingencySumTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request_for_variation_contract_and_contingency_sum', function(Blueprint $table)
		{
			$table->boolean('contract_sum_includes_contingency_sum')->default(true);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('request_for_variation_contract_and_contingency_sum', function(Blueprint $table)
		{
			$table->dropColumn('contract_sum_includes_contingency_sum');
		});
	}

}
