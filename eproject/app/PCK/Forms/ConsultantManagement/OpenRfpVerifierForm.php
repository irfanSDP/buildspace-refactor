<?php namespace PCK\Forms\ConsultantManagement;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;

use PCK\Forms\CustomFormValidator;

class OpenRfpVerifierForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $verifierIds = array_filter($formData['verifiers']);

        if(empty($verifierIds))
        {
            $this->rules['verifiers'] = 'required|integer';
            $this->messages['verifiers.integer'] = 'At least one verifier is required';
        }
    }
}