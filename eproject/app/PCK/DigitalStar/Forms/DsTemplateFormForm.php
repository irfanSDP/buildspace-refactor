<?php namespace PCK\DigitalStar\Forms;

use PCK\Forms\CustomFormValidator;

class DsTemplateFormForm extends CustomFormValidator {
    public $templateForm;

    protected function setRules($formData)
    {
        $this->rules['name'] = 'required|min:1|max:250';
    }
}