<?php namespace PCK\Forms\Buildspace;

use PCK\Forms\CustomFormValidator;

class AccountGroupForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['name'] = 'required|min:1|max:200';

        $this->messages['name.required'] = 'Name is required';
    }
}