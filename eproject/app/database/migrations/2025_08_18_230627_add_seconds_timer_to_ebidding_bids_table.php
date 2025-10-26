<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSecondsTimerToEbiddingBidsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_bidding_bids', 'extended_seconds'))
        {
            Schema::table('e_bidding_bids', function(Blueprint $table)
            {
                $table->integer('extended_seconds')->default(0)->after('duration_extended')->comment('Duration extended in seconds');
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
        if (Schema::hasColumn('e_bidding_bids', 'extended_seconds'))
        {
            Schema::table('e_bidding_bids', function(Blueprint $table)
            {
                $table->dropColumn('extended_seconds');
            });
        }
	}

}
