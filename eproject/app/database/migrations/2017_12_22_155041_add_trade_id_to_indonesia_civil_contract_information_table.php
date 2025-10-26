<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTradeIdToIndonesiaCivilContractInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('indonesia_civil_contract_information', function (Blueprint $table)
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
		Schema::table('indonesia_civil_contract_information', function (Blueprint $table)
		{
			$table->dropColumn('pre_defined_location_code_id');
		});
	}

}
