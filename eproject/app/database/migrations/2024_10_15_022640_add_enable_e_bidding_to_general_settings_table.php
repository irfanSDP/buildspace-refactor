<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnableEBiddingToGeneralSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('general_settings', function (Blueprint $table)
		{
			$table->boolean('enable_e_bidding')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('general_settings', function (Blueprint $table)
		{
			$table->dropColumn('enable_e_bidding');
		});
	}

}
