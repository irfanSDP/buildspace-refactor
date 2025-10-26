<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEBiddingBidsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('e_bidding_bids', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('e_bidding_id')->unsigned();
            $table->integer('company_id')->unsigned();
            $table->integer('duration_extended')->default(0);
            $table->decimal('decrement_percent', 5, 2)->default(0.00);
            $table->decimal('decrement_value', 19, 2)->default(0.00);
            $table->decimal('decrement_amount', 19, 2)->default(0.00);
            $table->decimal('bid_amount', 19, 2)->default(0.00);
            $table->string('bid_type', 10);
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
		Schema::drop('e_bidding_bids');
	}

}
