<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\Countries\CurrencySetting;

class CreateCurrencySettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('currency_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('country_id');
			$table->integer('rounding_type')->default(CurrencySetting::ROUNDING_TYPE_DISABLED);
			$table->timestamps();

			$table->index('country_id');
			$table->foreign('country_id')->references('id')->on('countries')->onDelele('cascase');
		});

		$seeder = new CurrencySettingsTableSeeder();
        $seeder->run();
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('currency_settings');
	}

}
