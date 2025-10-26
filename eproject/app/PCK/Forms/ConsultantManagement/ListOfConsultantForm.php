<?php namespace PCK\Forms\ConsultantManagement;

use PCK\ConsultantManagement\ConsultantManagementListOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultantCompany;
use PCK\Companies\Company;

use PCK\Forms\CustomFormValidator;

class ListOfConsultantForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['calling_rfp_date']  = 'required|date|before:closing_rfp_date';
        $this->rules['closing_rfp_date']  = 'required|date|after:calling_rfp_date';
        $this->rules['proposed_fee'] = 'required|numeric|min:0';

        if(array_key_exists('send_to_verify', $formData))
        {
            $verifierIds = array_filter($formData['verifiers']);

            if(empty($verifierIds))
            {
                $this->rules['verifiers'] = 'required|integer';
                $this->messages['verifiers.integer'] = 'At least one verifier is required';
            }

            $listOfConsultant = ConsultantManagementListOfConsultant::findOrFail($formData['id']);

            $consultantCount = Company::select("companies.id AS id", "companies.name AS name", "companies.reference_no", "consultant_management_list_of_consultant_companies.status")
            ->join('vendors', 'companies.id', '=', 'vendors.company_id')
            ->join('consultant_management_list_of_consultant_companies', 'consultant_management_list_of_consultant_companies.company_id', '=', 'vendors.company_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultant_companies.consultant_management_list_of_consultant_id', '=', 'consultant_management_list_of_consultants.id')
            ->where('companies.confirmed', '=', true)
            ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
            ->groupBy(\DB::raw('companies.id, consultant_management_list_of_consultant_companies.id'))
            ->count();

            if(empty($consultantCount))
            {
                $this->rules['empty_consultant'] = 'required';
                $this->messages['empty_consultant.required'] = 'Consultant list cannot be empty';
            }

            $participateCount = Company::select("companies.id AS id", "companies.name AS name", "companies.reference_no", "consultant_management_list_of_consultant_companies.status")
            ->join('vendors', 'companies.id', '=', 'vendors.company_id')
            ->join('consultant_management_list_of_consultant_companies', 'consultant_management_list_of_consultant_companies.company_id', '=', 'vendors.company_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultant_companies.consultant_management_list_of_consultant_id', '=', 'consultant_management_list_of_consultants.id')
            ->where('companies.confirmed', '=', true)
            ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
            ->where('consultant_management_list_of_consultant_companies.status', '=', ConsultantManagementListOfConsultantCompany::STATUS_YES)
            ->groupBy(\DB::raw('companies.id, consultant_management_list_of_consultant_companies.id'))
            ->count();

            if(!empty($consultantCount) && empty($participateCount))
            {
                $this->rules['empty_consultant'] = 'required';
                $this->messages['empty_consultant.required'] = 'At least one Consultant must be set as participant';
            }

            $pendingCount = Company::select("companies.id AS id", "companies.name AS name", "companies.reference_no", "consultant_management_list_of_consultant_companies.status")
            ->join('vendors', 'companies.id', '=', 'vendors.company_id')
            ->join('consultant_management_list_of_consultant_companies', 'consultant_management_list_of_consultant_companies.company_id', '=', 'vendors.company_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultant_companies.consultant_management_list_of_consultant_id', '=', 'consultant_management_list_of_consultants.id')
            ->where('companies.confirmed', '=', true)
            ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
            ->where('consultant_management_list_of_consultant_companies.status', '=', ConsultantManagementListOfConsultantCompany::STATUS_PENDING)
            ->groupBy(\DB::raw('companies.id, consultant_management_list_of_consultant_companies.id'))
            ->count();

            if(!empty($consultantCount) && !empty($participateCount) && !empty($pendingCount))
            {
                $this->rules['empty_consultant'] = 'required';
                $this->messages['empty_consultant.required'] = 'Please verify all Pending Consultant(s) either they are participating or not';
            }
        }
    }
}