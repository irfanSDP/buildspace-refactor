<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunSeedFormOfTenderTenderAlternativesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$seeder = new FormOfTenderAllTenderAlternativesTableSeeder;
        $seeder->run();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// no rollback for seeder required.
	}

}
