<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\Companies\Company;
use PCK\VendorCategory\VendorCategory;

class VendorRegistationCompanyDetailsForm extends CustomFormValidator
{
    protected $company;
    protected $isVendor = false;
    protected $ignoredId = null;

    protected $rules = [
        'name'                    => 'required',
        'address'                 => 'required',
        'main_contact'            => 'required',
        'telephone_number'        => 'required|max:20',
        'company_status'          => 'required|integer',
        'vendor_category_id'      => 'required|array',
    ];

    protected $messages = [
        'name.required'         => 'Name is required.',
        'address.required'      => 'Address is required.',
        'main_contact.required' => 'Contact Person is required.',
        'telephone_number'      => 'Telephone Number is required.',
        'company_status'        => 'Company Status is required.',
        'cidb_grade'            => 'CIDB Grade is required.',
        'bim_level_id'          => 'BIM Level is required.',
        'country_id.required'   => 'Country is required',
        'state_id.required'     => 'State is required.',
        'reference_no.required' => 'R.O.C Number is required.',
    ];

    public function setCompany(Company $company)
    {
        $this->company = $company;
    }

    public function setContextAsVendor()
    {
        $this->isVendor = true;
    }

    public function ignoreUnique($id)
    {
        $this->ignoredId = $id;
    }

    public function setRules($formData)
    {
        if($this->company->isContractor())
        {
            $this->rules['cidb_grade']   = 'required|integer';
        }

        if($this->company->isConsultant())
        {
            $this->rules['bim_level_id'] = 'required|integer';
        }

        if($this->isVendor)
        {
            $this->rules['country_id']   = 'required|integer';
            $this->rules['state_id']     = 'required|integer';
            $this->rules['reference_no'] = array(
                'required' => 'required',
                'max'      => 'max:20',
                'regex'    => 'regex:/(^[0-9a-zA-Z]+$)+/',
            );

            $this->updateUniquenessValidation($formData);
        }
    }

    protected function updateUniquenessValidation($formData)
    {
        if(is_null($this->ignoredId)) $this->ignoredId = 'NULL';

        $this->rules['reference_no']['unique']        = "unique:companies,reference_no,{$this->ignoredId},id,contract_group_category_id,{$this->company->contract_group_category_id}";
        $this->rules['tax_registration_no']['unique'] = "unique:companies,tax_registration_no,{$this->ignoredId},id,contract_group_category_id,{$this->company->contract_group_category_id}";
        $this->rules['tax_registration_id']['unique'] = "unique:companies,tax_registration_id,{$this->ignoredId},id,contract_group_category_id,{$this->company->contract_group_category_id}";
      
    }

    public function postParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();
        $company    = Company::find($formData['company_id']);

        $removedVendorCategoryIds = array_diff($company->vendorCategories->lists('id'), $formData['vendor_category_id']);
        $vendorWorkCategoryIds    = [];

        $trackProjectRecordVendorCategoryIds = $company->vendorRegistration->trackRecordProjects->lists('vendor_category_id');

        $matches = [];

        foreach($removedVendorCategoryIds as $id)
        {
            if(in_array($id, $trackProjectRecordVendorCategoryIds))
            {
                array_push($matches, $id);
            }
        }

        if(count($matches) > 0)
        {
            $messageBag->add('vendor_category_id', trans('vendorManagement.vendorCategoryInUse'));
        }

        return $messageBag;
    }
}