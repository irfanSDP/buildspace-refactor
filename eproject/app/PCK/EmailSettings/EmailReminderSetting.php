<?php namespace PCK\EmailSettings;

use Illuminate\Database\Eloquent\Model;

class EmailReminderSetting extends Model
{
    protected $table = 'email_reminder_settings';

    const MONTH = 1;
    const WEEK  = 2;
    const DAY   = 4;

    const TENDER_REMINDER_BEFORE_CLOSING_DATE_DEFAULT_VALUE = 3;

    public static function getValue($column)
    {
        $settings = self::first();

        return intval($settings->{$column});
    }

    public static function getUnitDescription($identifier = null)
    {
        $descriptions = [
            self::MONTH      => trans('vendorManagement.months'),
            self::WEEK       => trans('vendorManagement.weeks'),
            self::DAY        => trans('vendorManagement.days'),
        ];

        return is_null($identifier) ? $descriptions : $descriptions[$identifier];
    }
}