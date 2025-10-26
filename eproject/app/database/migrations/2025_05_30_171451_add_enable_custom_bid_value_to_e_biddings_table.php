<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnableCustomBidValueToEBiddingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (! Schema::hasColumn('e_biddings', 'enable_custom_bid_value'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->boolean('enable_custom_bid_value')->default(false)->after('decrement_value');
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
		if (Schema::hasColumn('e_biddings', 'enable_custom_bid_value'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->dropColumn('enable_custom_bid_value');
            });
        }
	}

}
