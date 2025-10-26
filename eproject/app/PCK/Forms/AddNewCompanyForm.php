<?php namespace PCK\Forms;

use PCK\Companies\Company;
use Laracasts\Validation\FormValidator;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\SystemModules\SystemModuleConfiguration;

class AddNewCompanyForm extends CustomFormValidator {

    protected $ignoredId = null;

    /**
     * Validation rules for creating Company
     *
     * @var array
     */
    protected $rules = [
        'name'                       => 'required',
        'contract_group_category_id' => 'required|integer|exists:contract_group_categories,id',
        'main_contact'               => 'required',
        'email'                      => 'required|max:250|email',
        'telephone_number'           => 'required|max:20',
        'fax_number'                 => 'max:20',
        'country_id'                 => 'required|integer',
        'state_id'                   => 'required|integer|exists:states,id',
        'reference_no'               => array(
            'required' => 'required',
            'max'      => 'max:20',
            'regex'    => 'regex:/(^[0-9a-zA-Z]+$)+/',
        ),
        'tax_registration_no'        => array(
            'max'    => 'max:20',
        ),
        'tax_registration_id'        => array(
            'max'    => 'max:20',
        ),
    ];

    protected function setRules($formData)
    {
        $this->updateUniquenessValidation($formData);

        $vendorManagementModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT);

        if($vendorManagementModuleEnabled) $this->addVendorManagementModuleRules($formData);
    }

    protected function updateUniquenessValidation($formData)
    {
        if(is_null($this->ignoredId)) $this->ignoredId = 'NULL';

        if(isset($formData['contract_group_category_id']))
        {
            $this->rules['reference_no']['unique']        = "unique:companies,reference_no,{$this->ignoredId},id,contract_group_category_id,{$formData['contract_group_category_id']}";
            $this->rules['tax_registration_no']['unique'] = "unique:companies,tax_registration_no,{$this->ignoredId},id,contract_group_category_id,{$formData['contract_group_category_id']}";
            $this->rules['tax_registration_id']['unique'] = "unique:companies,tax_registration_id,{$this->ignoredId},id,contract_group_category_id,{$formData['contract_group_category_id']}";
        }
    }

    protected function addVendorManagementModuleRules($formData)
    {
        $isExternalType = false;

        if(array_key_exists('contract_group_category_id', $formData))
        {
            $contractGroupCategory = ContractGroupCategory::find($formData['contract_group_category_id']);

            if($contractGroupCategory && $contractGroupCategory->type == ContractGroupCategory::TYPE_EXTERNAL)
            {
                $isExternalType = true;
            }
        }

        if($isExternalType)
        {
            $this->rules['vendor_category_id']         = 'required|array';
            $this->rules['business_entity_type_id']    = 'required';
            $this->rules['business_entity_type_other'] = '';

            if(array_key_exists('business_entity_type_id', $formData) && $formData['business_entity_type_id'] == 'other')
            {
                $this->rules['business_entity_type_other'] = 'required';
            }

            if( ! VendorRegistrationAndPrequalificationModuleParameter::getValue('allow_only_one_comp_to_reg_under_multi_vendor_category') )
            {
                $this->rules['vendor_category_id'] .= '|max:1';
            }
        }
    }

    public function validate($formData)
    {
        $formData['tax_registration_id'] = Company::generateRawRegistrationIdentifier($formData['tax_registration_no']);

        return parent::validate($formData);
    }

    protected $messages = [
        'reference_no.required'               => 'The R.O.C. field is required.',
        'reference_no.unique'                 => 'This R.O.C. has already been taken.',
        'reference_no.max'                    => 'The R.O.C. must not be more than :max characters.',
        'reference_no.regex'                  => 'The R.O.C. must only contain numbers and alphabets.',
        'contract_group_category_id.required' => 'Please select a User Type.',
        'business_entity_type_id.required'    => 'Please select a Business Entity',
        'vendor_category_id.required'         => 'Please select a Vendor Category.',
        'vendor_category_id.max'              => 'Please select only one Vendor Category.',
        'state_id.required'                   => 'Please select a State.',
        'tax_registration_no.unique'          => 'This Tax Identification Number has already been taken.',
        'tax_registration_no.max'             => 'The Tax Identification Number must not be more than :max characters.',
        'tax_registration_id.unique'          => 'This Tax Identification Number has already been taken.',
    ];

    public function ignoreUnique($id)
    {
        $this->ignoredId = $id;
    }

}