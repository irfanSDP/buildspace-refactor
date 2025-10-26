<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinBidAmountDiffToEbiddingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_biddings', 'min_bid_amount_diff'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->decimal('min_bid_amount_diff', 19, 2)->default(0.00)->after('decrement_value');
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasColumn('e_biddings', 'min_bid_amount_diff'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->dropColumn('min_bid_amount_diff');
            });
        }
	}

}
