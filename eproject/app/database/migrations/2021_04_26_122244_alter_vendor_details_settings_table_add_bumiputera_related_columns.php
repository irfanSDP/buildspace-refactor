<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterVendorDetailsSettingsTableAddBumiputeraRelatedColumns extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_detail_settings', function(Blueprint $table)
		{
			$table->text('is_bumiputera_instructions')->default('');
			$table->text('bumiputera_equity_instructions')->default('');
			$table->text('non_bumiputera_equity_instructions')->default('');
			$table->text('foreigner_equity_instructions')->default('');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_detail_settings', function(Blueprint $table)
		{
			$table->dropColumn('is_bumiputera_instructions');
			$table->dropColumn('bumiputera_equity_instructions');
			$table->dropColumn('non_bumiputera_equity_instructions');
			$table->dropColumn('foreigner_equity_instructions');
		});
	}

}
