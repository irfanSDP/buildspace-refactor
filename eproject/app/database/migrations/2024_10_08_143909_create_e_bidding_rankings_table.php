<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use PCK\EBiddings\EBiddingConsoleRepository;

class CreateEBiddingRankingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('e_bidding_rankings', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('e_bidding_id')->unsigned();
            $table->integer('company_id')->unsigned();
            $table->decimal('bid_amount', 19, 2);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('e_bidding_rankings');
	}

}
