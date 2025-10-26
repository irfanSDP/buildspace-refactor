<?php

use PCK\EmailNotificationSettings\EmailNotificationSetting;

class EmailNotificationSettingsTableSeederTableSeeder extends Seeder {

	public function run()
	{
		$identifiers = array_merge(EmailNotificationSetting::getEmailNotificationSettingsIdentifiersForExternalUsers(), EmailNotificationSetting::getEmailNotificationSettingsIdentifiersForInternalUsers());
	
		foreach($identifiers as $identifier)
		{
			$record = EmailNotificationSetting::where('setting_identifier', $identifier)->first();

			if(is_null($record))
			{
				$record = new EmailNotificationSetting();
				$record->setting_identifier = $identifier;
				$record->save();
			}
		}
	}

}