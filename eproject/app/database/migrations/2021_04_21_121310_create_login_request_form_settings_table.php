<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoginRequestFormSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('login_request_form_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('instructions')->default('');
			$table->boolean('include_instructions')->default(false);
			$table->text('disclaimer')->default('');
			$table->boolean('include_disclaimer')->default(false);
			$table->timestamps();
		});

		\PCK\LoginRequestFormSetting\LoginRequestFormSetting::create(array());
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('login_request_form_settings');
	}

}
