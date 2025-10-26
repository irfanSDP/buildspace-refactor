<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailNotificationSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('email_notification_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('setting_identifier');
			$table->boolean('activated')->default(true);
			$table->text('modifiable_contents')->nullable();
			$table->timestamps();

			$table->unique('setting_identifier');
		});

		$seeder = new EmailNotificationSettingsTableSeederTableSeeder();
		$seeder->run();
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('email_notification_settings');
	}

}
