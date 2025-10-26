<?php namespace PCK\EmailNotificationSettings;

class EmailNotificationSettingRepository
{
    public function getExternalUserEmailNotificationSettings()
    {
        $identifiers = EmailNotificationSetting::getEmailNotificationSettingsIdentifiersForExternalUsers();
        $records     = EmailNotificationSetting::whereIn('setting_identifier', $identifiers)->orderBy('setting_identifier', 'ASC')->get();
        $data        = [];

        foreach($records as $record)
        {
            array_push($data, [
                'id'                     => $record->id,
                'description'            => EmailNotificationSetting::getEmailNotificationSettingDescriptions($record->setting_identifier),
                'activated'              => $record->activated,
                'emailSubject'           => $record->getEmailSubject(),
                'route_update_status'    => route('email.notification.setting.activation.status.update', [$record->id]),
                'route_get_content'      => route('email.notification.setting.modifiable.contents.get', [$record->id]),
                'route_update_content'   => route('email.notification.setting.modifiable.contents.update', [$record->id]),
                'route_contents_preview' => route('email.notification.setting.email.contents.preview', [$record->id]),
            ]);
        }

        return $data;
    }

    public function getInternalUserEmailNotificationSettings()
    {
        $identifiers = EmailNotificationSetting::getEmailNotificationSettingsIdentifiersForInternalUsers();
        $records     = EmailNotificationSetting::whereIn('setting_identifier', $identifiers)->orderBy('setting_identifier', 'ASC')->get();
        $data        = [];

        foreach($records as $record)
        {
            array_push($data, [
                'id'                  => $record->id,
                'description'         => EmailNotificationSetting::getEmailNotificationSettingDescriptions($record->setting_identifier),
                'activated'           => $record->activated,
                'route_update_status' => route('email.notification.setting.activation.status.update', [$record->id]),
            ]);
        }

        return $data;
    }

    public function updateActivationStatus(EmailNotificationSetting $setting)
    {
        $setting->activated = ( ! $setting->activated );
        $setting->save();

        return EmailNotificationSetting::find($setting->id);
    }

    public function updateModifiableContents(EmailNotificationSetting $setting, $inputs)
    {
        $setting->modifiable_contents = trim($inputs['contents']);
        $setting->save();

        return EmailNotificationSetting::find($setting->id);
    }
}