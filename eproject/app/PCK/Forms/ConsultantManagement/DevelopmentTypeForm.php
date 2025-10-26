<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class DevelopmentTypeForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['title'] = 'required|min:1|max:250|unique:development_types,title,'.$formData['id'].',id';
        $this->rules['product_type_id'] = 'required|exists:product_types,id';
        
        $this->messages['product_type_id.required'] = trans('general.productTypes').' is required';

    }
}