<?php

use Illuminate\Database\Migrations\Migration;

class AlterFormOfTendersTableChangeTableName extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::rename('form_of_tender_details', 'form_of_tenders');
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::rename('form_of_tenders', 'form_of_tender_details');
	}

}
