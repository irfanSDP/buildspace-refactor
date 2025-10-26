<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEBiddingStatsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('e_bidding_stats', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('e_bidding_id')->unsigned();
            $table->integer('e_bidding_mode_id')->unsigned();
            $table->integer('root_subsidiary_id')->unsigned()->nullable();
            $table->integer('subsidiary_id')->unsigned()->nullable();
            $table->integer('project_id')->unsigned();
            $table->integer('duration')->unsigned()->comment('Seconds (without extension)');
            $table->integer('duration_extended')->unsigned()->default(0)->comment('Seconds');
            $table->integer('total_bids')->unsigned()->default(0);
            $table->integer('total_bidders')->unsigned()->default(0);
            $table->decimal('lowest_tender_amount', 19, 2);
            $table->decimal('budget_amount', 19, 2);
            $table->decimal('leading_bid_amount', 19, 2);
            $table->decimal('tender_amount_diff', 19, 2)->comment('Difference between lowest tender amount and leading bid amount');
            $table->decimal('budget_amount_diff', 19, 2)->comment('Difference between budget amount and leading bid amount');
            $table->string('currency_code', 3)->default('MYR');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
			$table->timestamps();

            $table->foreign('e_bidding_id')->references('id')->on('e_biddings')->onDelete('cascade');
            $table->foreign('e_bidding_mode_id')->references('id')->on('e_bidding_modes')->onDelete('cascade');
            $table->foreign('root_subsidiary_id')->references('id')->on('subsidiaries')->onDelete('set null');
            $table->foreign('subsidiary_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('e_bidding_stats');
	}

}
