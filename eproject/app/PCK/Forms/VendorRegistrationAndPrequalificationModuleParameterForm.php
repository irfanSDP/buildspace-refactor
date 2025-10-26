<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;

class VendorRegistrationAndPrequalificationModuleParameterForm extends CustomFormValidator
{
    protected $rules = [
        'valid_period_of_temp_login_acc_to_unreg_vendor_value'           => 'required|integer|min:1|max:1000',
        'notify_vendor_before_end_of_temp_acc_valid_period_value'        => 'required|integer|min:1|max:1000',
        'period_retain_unsuccessful_reg_and_preq_submission_value'       => 'required|integer|min:1|max:1000',
        'notify_purge_data_before_end_period_for_unsuccessful_sub_value' => 'required|integer|min:1|max:1000',
        'valid_submission_days'                                          => 'required|integer|min:1|max:1000',
        'notify_vendors_for_renewal_value'                               => 'required|integer|min:1|max:1000',
    ];

    protected $messages = [
        'valid_period_of_temp_login_acc_to_unreg_vendor_value.required' => 'Value is required',
        'valid_period_of_temp_login_acc_to_unreg_vendor_value.integer'  => 'Value must be a valid integer',
        'valid_period_of_temp_login_acc_to_unreg_vendor_value.min'      => 'Value must not be less than :min',

        'notify_vendor_before_end_of_temp_acc_valid_period_value.required' => 'Value is required',
        'notify_vendor_before_end_of_temp_acc_valid_period_value.integer'  => 'Value must be a valid integer',
        'notify_vendor_before_end_of_temp_acc_valid_period_value.min'      => 'Value must not be less than :min',

        'period_retain_unsuccessful_reg_and_preq_submission_value.required' => 'Value is required',
        'period_retain_unsuccessful_reg_and_preq_submission_value.integer'  => 'Value must be a valid integer',
        'period_retain_unsuccessful_reg_and_preq_submission_value.min'      => 'Value must not be less than :min',

        'notify_purge_data_before_end_period_for_unsuccessful_sub_value.required' => 'Value is required',
        'notify_purge_data_before_end_period_for_unsuccessful_sub_value.integer'  => 'Value must be a valid integer',
        'notify_purge_data_before_end_period_for_unsuccessful_sub_value.min'      => 'Value must not be less than :min',

        'valid_submission_days.required' => 'Value is required',
        'valid_submission_days.integer'  => 'Value must be a valid integer',
        'valid_submission_days.min'      => 'Value must not be less than :min',

        'notify_vendors_for_renewal_value.required' => 'Value is required',
        'notify_vendors_for_renewal_value.integer'  => 'Value must be a valid integer',
        'notify_vendors_for_renewal_value.min'      => 'Value must not be less than :min',
    ];
}

