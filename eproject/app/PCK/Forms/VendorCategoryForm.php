<?php namespace PCK\Forms;

use PCK\VendorCategory\VendorCategory;

class VendorCategoryForm extends CustomFormValidator {
    public $mode = 'create';

    protected function setRules($formData)
    {
        $this->rules['name']   = 'required|min:1|max:250|unique:vendor_categories,name,'.$formData['id'].',id,contract_group_category_id,'.$formData['contract_group_category_id'];
        $this->rules['code']   = 'required|min:1|max:50|unique:vendor_categories,code,'.$formData['id'].',id';
        $this->rules['target'] = 'required|integer|min:0|max:9999999';
    }
}