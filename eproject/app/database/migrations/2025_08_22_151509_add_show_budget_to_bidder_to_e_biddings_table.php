<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowBudgetToBidderToEBiddingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (! Schema::hasColumn('e_biddings', 'show_budget_to_bidder'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->boolean('show_budget_to_bidder')->default(false)->after('set_budget')->comment('Show budget to bidder');
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
		if (Schema::hasColumn('e_biddings', 'show_budget_to_bidder'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->dropColumn('show_budget_to_bidder');
            });
        }
	}

}
