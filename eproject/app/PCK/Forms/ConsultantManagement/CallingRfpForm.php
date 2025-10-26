<?php namespace PCK\Forms\ConsultantManagement;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;

use PCK\Forms\CustomFormValidator;

class CallingRfpForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['calling_rfp_date']  = 'required|date|before:closing_rfp_date';
        $this->rules['closing_rfp_date']  = 'required|date|after:calling_rfp_date';

        if(array_key_exists('send_to_verify', $formData))
        {
            $verifierIds = array_filter($formData['verifiers']);

            if(empty($verifierIds))
            {
                $this->rules['verifiers'] = 'required|integer';
                $this->messages['verifiers.integer'] = 'At least one verifier is required';
            }
        }
    }
}