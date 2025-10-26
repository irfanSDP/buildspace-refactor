<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class RolesForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['roles.1'] = 'required';
        $this->rules['group_categories.1'] = 'required';
        $this->messages['group_categories.1.required'] = trans('contractGroupCategories.userGroup').' is required';

        $this->rules['roles.2'] = 'required';
        $this->rules['group_categories.2'] = 'required';
        $this->messages['group_categories.2.required'] = trans('contractGroupCategories.userGroup').' is required';

        $this->rules['roles.4'] = 'required';
        $this->rules['group_categories.4'] = 'required';
        $this->messages['group_categories.4.required'] = trans('contractGroupCategories.userGroup').' is required';
    }
}