<?php namespace PCK\Forms;

class VendorChangeVendorGroupForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['contract_group_category_id'] = 'required|integer|exists:contract_group_categories,id';
        $this->rules['vendor_category_id'] = 'required|array';
    }
}