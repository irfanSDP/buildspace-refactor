<?php

use Illuminate\Database\Migrations\Migration;

class CreateCountriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('countries', function ($table)
		{
			$table->increments('id');
			$table->char('iso', 3);
			$table->char('iso3', 3);
			$table->char('fips', 3);
			$table->char('country', 255);
			$table->char('continent', 255);
			$table->string('currency_code', 3);
			$table->string('currency_name', 60);
			$table->char('phone_prefix', 60);
			$table->char('postal_code', 60);
			$table->char('languages', 50);
			$table->char('geonameid', 10);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('countries');
	}

}
