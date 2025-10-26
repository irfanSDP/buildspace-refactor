<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;
use PCK\Helpers\NumberHelper;

class VendorProfileModuleParameterForm extends CustomFormValidator
{
    protected $messages = [
        'validity_period_of_active_vendor_in_avl_value.required'             => 'Value is required',
        'validity_period_of_active_vendor_in_avl_value.integer'              => 'Value must be a valid integer',
        'validity_period_of_active_vendor_in_avl_value.min'                  => 'Value must not be less than :min',
        'validity_period_of_active_vendor_in_avl_value.max'                  => 'Value must not be more than :max',

        'grace_period_of_expired_vendor_before_moving_to_dvl_value.required' => 'Value is required.',
        'grace_period_of_expired_vendor_before_moving_to_dvl_value.integer'  => 'Value must be a valid integer.',
        'grace_period_of_expired_vendor_before_moving_to_dvl_value.min'      => 'Value must not be less than :min',
        'grace_period_of_expired_vendor_before_moving_to_dvl_value.max'      => 'Value must not be more than :max',

        'vendor_retain_period_in_wl_value.required' => 'Value is required.',
        'vendor_retain_period_in_wl_value.integer'  => 'Value must be a valid integer.',
        'vendor_retain_period_in_wl_value.min'      => 'Value must not be less than :min',
        'vendor_retain_period_in_wl_value.max'      => 'Value must not be more than :max',

        'watch_list_nomineee_to_active_vendor_list_threshold_score.required' => 'Value is required.',
        'watch_list_nomineee_to_active_vendor_list_threshold_score.integer'  => 'Value must be a valid integer.',
        'watch_list_nomineee_to_active_vendor_list_threshold_score.min'      => 'Value must not be less than :min',
        'watch_list_nomineee_to_active_vendor_list_threshold_score.max'      => 'Value must not be more than :max',

        'watch_list_nomineee_to_watch_list_threshold_score.required' => 'Value is required.',
        'watch_list_nomineee_to_watch_list_threshold_score.integer'  => 'Value must be a valid integer.',
        'watch_list_nomineee_to_watch_list_threshold_score.min'      => 'Value must not be less than :min',
        'watch_list_nomineee_to_watch_list_threshold_score.max'      => 'Value must not be more than :max',

        'renewal_period_before_expiry_in_days.required' => 'Value is required.',
        'renewal_period_before_expiry_in_days.integer'  => 'Value must be a valid integer.',
        'renewal_period_before_expiry_in_days.min'      => 'Value must not be less than :min',
        'renewal_period_before_expiry_in_days.max'      => 'Value must not be more than :max',

        'registration_price.required' => 'Value is required.',
        'registration_price.numeric'  => 'Value must be a valid number.',
        'registration_price.min'      => 'Value must not be less than :min',
        'registration_price.max'      => 'Value must not be more than :max',

        'renewal_price.required' => 'Value is required.',
        'renewal_price.numeric'  => 'Value must be a valid number.',
        'renewal_price.min'      => 'Value must not be less than :min',
        'renewal_price.max'      => 'Value must not be more than :max',
    ];

    protected function setRules($formData)
    {
        $this->rules['watch_list_nomineee_to_active_vendor_list_threshold_score'] = 'required|integer|min:0|max:100';
        $this->rules['watch_list_nomineee_to_watch_list_threshold_score']         = 'required|integer|min:0|max:100';
        $this->rules['validity_period_of_active_vendor_in_avl_value']             = 'required|integer|min:1|max:1000';
        $this->rules['grace_period_of_expired_vendor_before_moving_to_dvl_value'] = 'required|integer|min:1|max:1000';
        $this->rules['vendor_retain_period_in_wl_value']                          = 'required|integer|min:1|max:1000';
        $this->rules['renewal_period_before_expiry_in_days']                      = 'required|integer|min:1|max:1000';
        $this->rules['registration_price']                                        = 'required|numeric|min:0|max:'.NumberHelper::maxDecimalValue(24,2);
        $this->rules['renewal_price']                                             = 'required|numeric|min:0|max:'.NumberHelper::maxDecimalValue(24,2);
    }
}

