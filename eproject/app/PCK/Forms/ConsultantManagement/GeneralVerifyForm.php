<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class GeneralVerifyForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        if(array_key_exists('reject', $formData))
        {
            $this->rules['remarks'] = 'required';
            $this->messages['remarks.required'] = 'Remarks is required';
        }
    }
}