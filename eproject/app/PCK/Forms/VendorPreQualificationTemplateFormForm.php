<?php namespace PCK\Forms;

class VendorPreQualificationTemplateFormForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['name'] = 'required|min:1|max:250';
    }
}