<?php namespace PCK\Forms\Buildspace;

use PCK\Forms\CustomFormValidator;

class AccountCodeForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['code'] = 'required|min:1|max:50';
        $this->rules['tax_code'] = 'required|min:1|max:50';
        $this->rules['type'] = 'required';

        $this->messages['code.required'] = 'Code is required';
    }
}