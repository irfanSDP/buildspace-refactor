<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBidModeToEBiddingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_biddings', 'bid_mode')) {
            Schema::table('e_biddings', function(Blueprint $table) {
                $table->integer('e_bidding_mode_id')->unsigned()->default(1)->after('overtime_period');

                $table->foreign('e_bidding_mode_id')
                    ->references('id')->on('e_bidding_modes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
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
        if (Schema::hasColumn('e_biddings', 'e_bidding_mode_id')) {
            Schema::table('e_biddings', function(Blueprint $table) {
                $table->dropColumn('e_bidding_mode_id');
            });
        }
	}

}
