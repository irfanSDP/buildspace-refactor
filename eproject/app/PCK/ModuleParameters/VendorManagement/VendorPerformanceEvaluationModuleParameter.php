<?php namespace PCK\ModuleParameters\VendorManagement;

use Illuminate\Database\Eloquent\Model;

class VendorPerformanceEvaluationModuleParameter extends Model
{
    protected $table = 'vendor_performance_evaluation_module_parameters';

    const MONTH = 1;
    const WEEK  = 2;
    const DAY   = 4;

    const DEFAULT_TIME_FRAME_FOR_VPE_CYCLE_VALUE_DEFAULT_VALUE      = 6;
    const DEFAULT_TIME_FRAME_FOR_VPE_SUBMISSION_VALUE_DEFAULT_VALUE = 2;
    const PASSING_SCORE_DEFAULT_VALUE                               = 60;
    const EMAIL_REMINDER_BEFORE_CYCLE_END_DATE_DEFAULT_VALUE        = 3;

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

    public function vendorManagementGrade()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGrade', 'vendor_management_grade_id');
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