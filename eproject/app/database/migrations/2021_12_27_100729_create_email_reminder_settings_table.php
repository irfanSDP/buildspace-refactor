<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\EmailSettings\EmailReminderSetting;

class CreateEmailReminderSettingsTable extends Migration
{
	public function up()
	{
		Schema::create('email_reminder_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('tender_reminder_before_closing_date_value');
			$table->integer('tender_reminder_before_closing_date_unit');
			$table->timestamps();
		});

		// seeds data
		// there will only be 1 record
		$record = EmailReminderSetting::first();

        if(is_null($record))
        {
            $record = new EmailReminderSetting();

			$record->tender_reminder_before_closing_date_value = EmailReminderSetting::TENDER_REMINDER_BEFORE_CLOSING_DATE_DEFAULT_VALUE;
			$record->tender_reminder_before_closing_date_unit  = EmailReminderSetting::DAY;

			$record->save();
        }
	}

	public function down()
	{
		Schema::drop('email_reminder_settings');
	}
}
