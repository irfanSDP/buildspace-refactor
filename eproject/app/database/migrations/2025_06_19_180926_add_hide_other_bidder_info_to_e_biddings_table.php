<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHideOtherBidderInfoToEBiddingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_biddings', 'hide_other_bidder_info')) {
            Schema::table('e_biddings', function(Blueprint $table) {
                $table->boolean('hide_other_bidder_info')->default(false)->after('enable_custom_bid_value');
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
        if (Schema::hasColumn('e_biddings', 'hide_other_bidder_info')) {
            Schema::table('e_biddings', function(Blueprint $table) {
                $table->dropColumn('hide_other_bidder_info');
            });
        }
	}

}
