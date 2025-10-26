<?php namespace PCK\DigitalStar\Forms;

use PCK\Forms\CustomFormValidator;

class DsModuleParameterForm extends CustomFormValidator
{
    protected $messages = [
        'email_reminder_before_cycle_end_date_value.required' => 'Value is required',
        'email_reminder_before_cycle_end_date_value.integer'  => 'Value must be a valid integer',
        'email_reminder_before_cycle_end_date_value.min'      => 'Value must be :min or more',
    ];

    protected $rules = [
        'email_reminder_before_cycle_end_date_value' => 'integer|min:1',
    ];

    protected function setRules($formData)
    {
        // ...
    }

    /*protected function preParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();

        if(!empty($formData['number_of_days_ahead_of_submission']))
        {
            foreach($formData['number_of_days_ahead_of_submission'] as $key => $input)
            {
                if(filter_var($input, FILTER_VALIDATE_INT)) continue;

                $messageBag->add('number_of_days_ahead_of_submission', trans('forms.error:integers'));

                break;
            }
        }

        return $messageBag;
    }*/
}