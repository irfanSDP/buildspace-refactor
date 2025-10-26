<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\EmailSettings\EmailSetting;

class CreateEmailSettingsTable extends Migration
{
	public function up()
	{
		Schema::create('email_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('company_logo_alignment_identifier')->default(EmailSetting::COMPANY_LOGO_ALIGNMENT_LEFT_IDENTIFIER);
			$table->timestamps();
		});

		$emailSetting = EmailSetting::first();

		if(is_null($emailSetting))
		{
			$record = new EmailSetting();
			$record->save();
		}
	}

	public function down()
	{
		Schema::drop('email_settings');
	}
}
