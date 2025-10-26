<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveNotNullConstraintsFromContractSumColumnInIndonesiaCivilContractInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        DB::statement('ALTER TABLE indonesia_civil_contract_information ALTER COLUMN contract_sum DROP NOT NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        \PCK\ProjectDetails\IndonesiaCivilContractInformation::whereNull('contract_sum')->update(array( 'contract_sum' => 0 ));

        DB::statement('ALTER TABLE indonesia_civil_contract_information ALTER COLUMN contract_sum SET NOT NULL');
	}

}
