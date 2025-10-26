<?php namespace PCK\Forms;

use PCK\VendorWorkSubcategory\VendorWorkSubcategory;

class VendorWorkSubcategoryForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['name'] = 'required|min:1|max:250|unique:vendor_work_subcategories,name,'.$formData['id'].',id,vendor_work_category_id,'.$formData['vendor_work_category_id'];
        $this->rules['code'] = 'required|min:1|max:50|unique:vendor_work_subcategories,code,'.$formData['id'].',id';
    }
}