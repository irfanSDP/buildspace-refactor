<?php namespace PCK\Forms;

use PCK\ContractGroupCategory\ContractGroupCategory;

class VendorGroupForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['name']        = 'required|min:1|max:250|unique:contract_group_categories,name,'.$formData['id'].',id';
        $this->rules['code']        = 'required|min:1|max:50|unique:contract_group_categories,code,'.$formData['id'].',id';

        if($formData['vendor_type'] == ContractGroupCategory::TYPE_EXTERNAL)
        {
            $this->rules['vendor_type'] = 'required';
        }
    }
}