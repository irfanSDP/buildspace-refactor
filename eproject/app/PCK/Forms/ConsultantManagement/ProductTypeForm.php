<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class ProductTypeForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['title'] = 'required|min:1|max:250|unique:product_types,title,'.$formData['id'].',id';
    }
}