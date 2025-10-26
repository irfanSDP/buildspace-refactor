<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSelectedContractorColumnIntoCompanyTenderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_tender', function (Blueprint $table)
		{
			$table->boolean('selected_contractor')->default(false)->index();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('company_tender', function (Blueprint $table)
		{
			$table->dropColumn(array( 'selected_contractor' ));
		});
	}

}