<?php namespace PCK\Forms;

use PCK\Forms\CustomFormValidator;

class AccountCodeSettingsVendorCategoryForm extends CustomFormValidator {

    protected $throwException = false;
    protected $companyId;

    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    protected function setMessages()
    {
        $this->messages['vendor_category_id.exists'] = trans('forms.invalidInput');
    }

    protected function setRules($formData)
    {
        $this->rules['vendor_category_id'] = 'required|integer|exists:company_vendor_category,vendor_category_id,company_id,'.$this->companyId;
    }
}