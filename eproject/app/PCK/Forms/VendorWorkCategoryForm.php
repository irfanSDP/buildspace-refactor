<?php namespace PCK\Forms;

use PCK\VendorWorkCategory\VendorWorkCategory;

class VendorWorkCategoryForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['name'] = 'required|min:1|max:250';
        $this->rules['code'] = 'required|min:1|max:50|unique:vendor_work_categories,code,'.$formData['id'].',id';
    }
}