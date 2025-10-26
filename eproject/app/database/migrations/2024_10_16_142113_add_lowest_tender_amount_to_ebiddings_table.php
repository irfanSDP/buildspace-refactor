<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use PCK\EBiddings\EBiddingConsoleRepository;

class AddLowestTenderAmountToEbiddingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_biddings', 'lowest_tender_amount'))
        {
            Schema::table('e_biddings', function (Blueprint $table) {
                $table->decimal('lowest_tender_amount', 19, 2)->nullable()->after('budget');
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
        if (Schema::hasColumn('e_biddings', 'lowest_tender_amount'))
        {
            Schema::table('e_biddings', function (Blueprint $table) {
                $table->dropColumn('lowest_tender_amount');
            });
        }
	}

}
