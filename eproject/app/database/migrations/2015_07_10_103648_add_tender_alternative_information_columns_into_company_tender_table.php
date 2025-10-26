<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTenderAlternativeInformationColumnsIntoCompanyTenderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_tender', function (Blueprint $table)
		{
			$table->decimal('supply_of_material_amount', 19, 2)->default(0);
			$table->decimal('other_bill_type_amount_except_prime_cost_provisional', 19, 2)->default(0);
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
			$table->dropColumn(array(
				'supply_of_material_amount',
				'other_bill_type_amount_except_prime_cost_provisional'
			));
		});
	}

}