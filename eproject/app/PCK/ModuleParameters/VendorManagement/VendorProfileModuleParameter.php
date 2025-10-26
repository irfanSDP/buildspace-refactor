<?php namespace PCK\ModuleParameters\VendorManagement;

use Illuminate\Database\Eloquent\Model;

class VendorProfileModuleParameter extends Model
{
    protected $table = 'vendor_profile_module_parameters';

    const MONTH = 1;
    const WEEK  = 2;
    const DAY   = 4;

    const VALIDITY_PERIOD_OF_ACTIVE_VENDOR_IN_AVL_VALUE_DEFAULT_VALUE             = 36;
    const GRACE_PERIOD_OF_EXPIRED_VENDOR_BEFORE_MOVING_TO_DVL_VALUE_DEFAULT_VALUE = 6;
    const VENDOR_RETAIN_PERIOD_IN_WL_VALUE_DEFAULT_VALUE                          = 12;
    const WATCH_LIST_NOMINEEE_TO_ACTIVE_VENDOR_LIST_THRESHOLD_SCORE_DEFAULT_VALUE = 70;
    const WATCH_LIST_NOMINEEE_TO_WATCH_LIST_THRESHOLD_SCORE_DEFAULT_VALUE         = 60;
    const RENEWAL_PERIOD_BEFORE_EXPIRY_IN_DAYS                                    = 60;

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