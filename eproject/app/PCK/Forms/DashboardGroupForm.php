<?php namespace PCK\Forms;

use PCK\Forms\CustomFormValidator;

class DashboardGroupForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['title'] = 'required|min:1|max:80';
    }
}