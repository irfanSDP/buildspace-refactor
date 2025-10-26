<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSubmittedAtColumnIntoCompanyTenderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_tender', function (Blueprint $table)
		{
			$table->dateTime('submitted_at')->nullable()->index();
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
			$table->dropColumn('submitted_at');
		});
	}

}