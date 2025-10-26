<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnableNoTieBidToEBiddingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_biddings', 'enable_no_tie_bid'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->boolean('enable_no_tie_bid')->default(false)->after('enable_custom_bid_value');
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
        if (Schema::hasColumn('e_biddings', 'enable_no_tie_bid'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->dropColumn('enable_no_tie_bid');
            });
        }
	}

}
