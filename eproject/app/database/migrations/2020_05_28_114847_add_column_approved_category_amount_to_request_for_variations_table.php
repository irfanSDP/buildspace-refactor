<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\RequestForVariation\RequestForVariation;

class AddColumnApprovedCategoryAmountToRequestForVariationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request_for_variations', function(Blueprint $table)
		{
			$table->decimal('approved_category_amount', 19, 2)->nullable();
		});

		// patch existing data
		// all records' approved_category_amount columns get updated to 0.0 except approved records (remain NULL)
		DB::statement("UPDATE request_for_variations SET approved_category_amount = 0.0 WHERE status != " . RequestForVariation::STATUS_APPROVED);
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('request_for_variations', function(Blueprint $table)
		{
			$table->dropColumn('approved_category_amount');
		});
	}

}
