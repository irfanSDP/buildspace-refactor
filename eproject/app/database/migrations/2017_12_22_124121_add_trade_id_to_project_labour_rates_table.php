<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTradeIdToProjectLabourRatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('project_labour_rates', function (Blueprint $table)
		{
			$table->integer('pre_defined_location_code_id')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('project_labour_rates', function (Blueprint $table)
		{
			$table->dropColumn('pre_defined_location_code_id');
		});
	}

}
