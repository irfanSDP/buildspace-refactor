<?php namespace PCK\Forms\ExternalApplications;

use PCK\Forms\CustomFormValidator;

class ClientForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['name'] = 'required|min:1|max:80|iunique:external_application_clients,name,'.$formData['id'].',id';

        $this->messages['name.required'] = 'Name is required';
        $this->messages['name.iunique'] = "Name has already been taken";
    }
}