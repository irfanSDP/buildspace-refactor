<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropTenderSummaryColumnFromTendererRatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tenderer_rates', function (Blueprint $table)
		{
			$table->dropColumn('tender_summary');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tenderer_rates', function (Blueprint $table)
		{
			$table->string('tender_summary')->nullable()->default(null);
		});
	}

}
