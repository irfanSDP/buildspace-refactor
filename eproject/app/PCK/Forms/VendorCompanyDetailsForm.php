<?php namespace PCK\Forms;

use PCK\Companies\Company;

class VendorCompanyDetailsForm extends CustomFormValidator {
    protected $company;

    public function setCompany(Company $company)
    {
        $this->company = $company;
    }

    protected function setRules($formData)
    {
        $this->rules['name'] = 'required|min:1|max:250';
        $this->rules['reference_no'] = 'required|min:1|max:50|unique:companies,reference_no,'.$formData['id'].',id';
        $this->rules['activation_date'] = 'date';
        $this->rules['expiry_date'] = 'date';
        $this->rules['address'] = 'required';
        $this->rules['main_contact'] = 'required|min:1|max:250';
        $this->rules['email'] = 'required|email';
        $this->rules['telephone_number'] = 'required|min:1|max:100';
        $this->rules['fax_number'] = 'max:100';
        $this->rules['country_id'] = 'required|integer|exists:countries,id';
        $this->rules['state_id'] = 'required|integer|exists:states,id';
        $this->rules['contract_group_category_id'] = 'required|integer|exists:contract_group_categories,id';
        $this->rules['vendor_category_id'] = 'required|array';
        $this->rules['tax_registration_no'] = 'max:50';
        $this->rules['company_status'] = 'required|integer';
        $this->rules['bumiputera_equity'] = 'numeric|min:0|max:100';
        $this->rules['non_bumiputera_equity'] = 'numeric|min:0|max:100';
        $this->rules['foreigner_equity'] = 'numeric|min:0|max:100';
        
        if($this->company && $this->company->isContractor())
        {
            $this->rules['cidb_grade'] = 'required|integer';
            $this->rules['cidb_code_id'] = 'required|array';
        }

        if($this->company && $this->company->isConsultant())
        {
            $this->rules['bim_level_id'] = 'required|integer';
        }
    }
}