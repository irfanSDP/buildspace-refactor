<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefreshEBiddingStatsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (Schema::hasTable('e_bidding_stats')) {
            DB::table('e_bidding_stats')->delete();
        }

        if (Schema::hasTable('e_biddings') && Schema::hasColumn('e_biddings', 'processed_at')) {
            // Set processed_at back to NULL for all rows
            DB::table('e_biddings')->update(['processed_at' => null]);
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Do nothing
	}

}
