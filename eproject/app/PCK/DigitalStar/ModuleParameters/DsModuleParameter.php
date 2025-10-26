<?php namespace PCK\DigitalStar\ModuleParameters;

use Illuminate\Database\Eloquent\Model;

class DsModuleParameter extends Model
{
    protected $table = 'ds_module_parameters';

    const MONTH = 1;
    const WEEK  = 2;
    const DAY   = 4;

    const EMAIL_REMINDER_BEFORE_CYCLE_END_DATE_DEFAULT_VALUE = 3;

    public function vendorManagementGrade()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGrade', 'vendor_management_grade_id');
    }

    public function submissionReminders()
    {
        return $this->hasMany('PCK\DigitalStar\ModuleParameters\DsSubmissionReminderSetting', 'ds_module_parameter_id');
    }

    public static function getValue($column)
    {
        $settings = self::first();

        return intval($settings->{$column});
    }

    public static function getUnitDescription($identifier = null)
    {
        $descriptions = [
            self::MONTH => trans('vendorManagement.months'),
            self::WEEK  => trans('vendorManagement.weeks'),
            self::DAY   => trans('vendorManagement.days'),
        ];

        return is_null($identifier) ? $descriptions : $descriptions[$identifier];
    }

    public static function getHelperClassUnit($identifier)
    {
        $unit = [
            self::MONTH      => 'months',
            self::WEEK       => 'weeks',
            self::DAY        => 'days',
        ];

        return $unit[$identifier];
    }
}