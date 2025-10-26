<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDirectionToEBiddingBidsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_bidding_bids', 'direction')) {
            Schema::table('e_bidding_bids', function(Blueprint $table)
            {
                $table->string('direction', 10)->default('DECREASE')->after('bid_type');
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
        if (Schema::hasColumn('e_bidding_bids', 'direction')) {
            Schema::table('e_bidding_bids', function(Blueprint $table)
            {
                $table->dropColumn('direction');
            });
        }
	}

}
