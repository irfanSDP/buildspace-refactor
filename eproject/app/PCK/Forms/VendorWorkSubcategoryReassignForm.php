<?php namespace PCK\Forms;

class VendorWorkSubcategoryReassignForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['vendor_work_category_id'] = 'required';
    }
}