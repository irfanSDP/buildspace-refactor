<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class VendorCategoryRfpForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['vendor_category_id'] = 'required|exists:vendor_categories,id|unique:consultant_management_vendor_categories_rfp,vendor_category_id,'.$formData['id'].',id,consultant_management_contract_id,'.$formData['consultant_management_contract_id'];
        $this->rules['cost_type'] = 'required';
    }
}
