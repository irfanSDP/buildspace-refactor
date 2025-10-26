<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class ConsultantManagementContractForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['subsidiary_id'] = 'required|exists:subsidiaries,id';
        $this->rules['reference_no'] = 'required|min:1|max:80|unique:consultant_management_contracts,reference_no,'.$formData['id'].',id';
        $this->rules['title'] = 'required';
        $this->rules['address'] = 'required';
        $this->rules['country_id'] = 'required|exists:countries,id';
        $this->rules['state_id'] = 'required|exists:states,id';

        $this->messages['subsidiary_id.required'] = trans('companies.referenceNo').' is required';

    }
}