<?php namespace PCK\ModuleParameters\VendorManagement;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\Helpers;

class VendorRegistrationAndPrequalificationModuleParameter extends Model
{
    protected $table = 'vendor_registration_and_prequalification_module_parameters';

    const MONTH      = 1;
    const WEEK       = 2;
    const DAY        = 4;

    const OPTION_YES = 1;
    const OPTION_NO  = 0;

    const REQUEST_RESUBMISSION_IS_SENT                = 1;
    const VENDOR_LAST_LOGIN_DAY_IN_RESUBMISSION_STAGE = 2;

    const VALID_SUBMISSION_DAYS_DEFAULT_VALUE     = 60;
    const NOTIFY_VENDOR_FOR_RENEWAL_DEFAULT_VALUE = 14;

    public function vendorManagementGrade()
    {
        return $this->belongsTo('PCK\ModuleParameters\VendorManagement\VendorManagementGrade', 'vendor_management_grade_id');
    }

    public static function getValue($column)
    {
        $settings = self::first();

        return $settings->{$column};
    }

    public static function getYesNoOptions($identifier = null)
    {
        $options = [
            self::OPTION_YES => trans('general.yes'),
            self::OPTION_NO  => trans('general.no'),
        ];

        return is_null($identifier) ? $options : $options[$identifier];
    }

    public static function getRetainInfoStartingPeriod($identifier = null)
    {
        $periods = [
            self::REQUEST_RESUBMISSION_IS_SENT                => trans('vendorManagement.fromThePeriodRequestForResubmissionIsSent'),
            self::VENDOR_LAST_LOGIN_DAY_IN_RESUBMISSION_STAGE => trans('vendorManagement.fromThePeriodVendorLastLogininResubmissionStage'),
        ];

        return is_null($identifier) ? $periods : $periods[$identifier];
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

    public static function getHelperClassUnit($identifier)
    {
        $unit = [
            self::MONTH      => 'months',
            self::WEEK       => 'weeks',
            self::DAY        => 'days',
        ];

        return $unit[$identifier];
    }

    public function getAvailableInformationToDisplay()
    {
        return [
            'retain_company_name' => [
                'description' => trans('vendorManagement.companyName'),
                'checked'     => $this->retain_company_name,
            ],
            'retain_roc_number' => [
                'description' => trans('vendorManagement.rocNumber'),
                'checked'     => $this->retain_roc_number,
            ],
            'retain_email' => [
                'description' => trans('vendorManagement.email'),
                'checked'     => $this->retain_email,
            ],
            'retain_contact_number' => [
                'description' => trans('vendorManagement.contactNumber'),
                'checked'     => $this->retain_contact_number,
            ],
            'retain_date_of_data_purging' => [
                'description' => trans('vendorManagement.dateOfDataBeingPurged'),
                'checked'     => $this->retain_date_of_data_purging,
            ],
        ];
    }

    public static function getTemporaryLoginAccountValidityPeriod()
    {
        switch(self::getValue('valid_period_of_temp_login_acc_to_unreg_vendor_unit'))
        {
            case self::DAY:
                $validityPeriodUnit = 'days';
                break;
            case self::WEEK:
                $validityPeriodUnit = 'weeks';
                break;
            case self::MONTH:
                $validityPeriodUnit = 'months';
                break;
            default:
                throw new \Exception("Invalid time unit");
        }

        return Helpers::getTimeFromNow(self::getValue('valid_period_of_temp_login_acc_to_unreg_vendor_value'), $validityPeriodUnit);
    }
}