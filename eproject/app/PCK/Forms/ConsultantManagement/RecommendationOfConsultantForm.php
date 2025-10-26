<?php namespace PCK\Forms\ConsultantManagement;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;

use PCK\Forms\CustomFormValidator;

class RecommendationOfConsultantForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['calling_rfp_proposed_date']  = 'required|date|before:closing_rfp_proposed_date';
        $this->rules['closing_rfp_proposed_date']  = 'required|date|after:calling_rfp_proposed_date';
        $this->rules['proposed_fee'] = 'required|numeric|min:0';

        if(array_key_exists('send_to_verify', $formData))
        {
            $contract = ConsultantManagementContract::find($formData['contract_id']);

            if(empty($contract->consultantManagementSubsidiaries->count()))
            {
                $this->rules['empty_phase'] = 'required';
                $this->messages['empty_phase.required'] = 'At least one Phase is required for RFP';
            }

            $verifierIds = array_filter($formData['verifiers']);

            if(empty($verifierIds))
            {
                $this->rules['verifiers'] = 'required|integer';
                $this->messages['verifiers.integer'] = 'At least one verifier is required';
            }

            $hasLisOfConsultantRoleCompany = ConsultantManagementCompanyRole::where('consultant_management_contract_id', '=', $contract->id)
            ->where('role', '=', ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT)
            ->count();

            if(empty($hasLisOfConsultantRoleCompany))
            {
                $this->rules['loc_company'] = 'required';
                $this->messages['loc_company.required'] = 'Company must be set to manage List of Consultant Role';
            }
        }
    }
}