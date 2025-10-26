<?php namespace PCK\Forms\ConsultantManagement;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;

use PCK\Forms\CustomFormValidator;

class ConsultantRfpCommonInfoForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['name_in_loa']    = 'required|min:1|max:250';
        $this->rules['contact_name']   = 'required|min:1|max:250';
        $this->rules['contact_number'] = 'required|min:1|max:50';
        $this->rules['contact_email']  = 'required|min:1|max:250';
    }
}