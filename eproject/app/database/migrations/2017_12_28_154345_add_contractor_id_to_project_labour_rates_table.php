<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractorIdToProjectLabourRatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('project_labour_rates', function (Blueprint $table)
		{
			$table->integer('contractor_id')->nullable();
			$table->foreign('contractor_id')->references('id')->on('companies');
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
			$table->dropColumn('contractor_id');
		});
	}

}
