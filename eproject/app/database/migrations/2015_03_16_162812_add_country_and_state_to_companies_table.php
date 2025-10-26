<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountryAndStateToCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('companies', function (Blueprint $table)
		{
			$table->unsignedInteger('country_id')->default(1);
			$table->unsignedInteger('state_id')->default(1);

			$table->foreign('country_id')
				->references('id')
				->on('countries');
			$table->foreign('state_id')
				->references('id')
				->on('states');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('companies', function (Blueprint $table)
		{
			$table->dropColumn('country_id');
			$table->dropColumn('state_id');
		});
	}

}