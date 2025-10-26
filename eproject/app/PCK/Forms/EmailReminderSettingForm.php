<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;

class EmailReminderSettingForm extends CustomFormValidator
{
    protected $messages = [
        'tender_reminder_before_closing_date_value.required'             => 'Value is required',
        'tender_reminder_before_closing_date_value.integer'              => 'Value must be a valid integer',
        'tender_reminder_before_closing_date_value.min'                  => 'Value must not be less than :min',
    ];

    protected function setRules($formData)
    {
        $this->rules['tender_reminder_before_closing_date_value'] = 'required|integer|min:1';
    }
}

