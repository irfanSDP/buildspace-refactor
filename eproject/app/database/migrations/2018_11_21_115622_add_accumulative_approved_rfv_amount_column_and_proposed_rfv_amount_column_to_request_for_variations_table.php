<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAccumulativeApprovedRfvAmountColumnAndProposedRfvAmountColumnToRequestForVariationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request_for_variations', function(Blueprint $table)
		{
			$table->decimal('accumulative_approved_rfv_amount', 19, 2)->nullable();
			$table->decimal('proposed_rfv_amount', 19, 2)->nullable();
		});
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
			$table->dropColumn('accumulative_approved_rfv_amount');
			$table->dropColumn('proposed_rfv_amount');
		});
	}

}
