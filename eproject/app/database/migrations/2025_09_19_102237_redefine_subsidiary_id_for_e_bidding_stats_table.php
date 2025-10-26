<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RedefineSubsidiaryIdForEBiddingStatsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('e_bidding_stats', function(Blueprint $table)
		{
			$table->dropForeign(['subsidiary_id']);
			$table->dropColumn('subsidiary_id');
		});

		Schema::table('e_bidding_stats', function(Blueprint $table)
		{
			$table->integer('subsidiary_id')->unsigned()->nullable()->after('root_subsidiary_id');
			$table->foreign('subsidiary_id')->references('id')->on('subsidiaries')->onDelete('set null');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('e_bidding_stats', function(Blueprint $table)
		{
			$table->dropForeign(['subsidiary_id']);
			$table->dropColumn('subsidiary_id');
		});

		Schema::table('e_bidding_stats', function(Blueprint $table)
		{
			$table->integer('subsidiary_id')->unsigned()->nullable()->after('root_subsidiary_id');
            $table->foreign('subsidiary_id')->references('id')->on('companies')->onDelete('set null');
		});
	}

}
