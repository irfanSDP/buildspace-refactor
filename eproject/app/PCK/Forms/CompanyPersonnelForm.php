<?php namespace PCK\Forms;

use PCK\CompanyPersonnel\CompanyPersonnel;
use PCK\VendorRegistration\VendorRegistration;

class CompanyPersonnelForm extends CustomFormValidator {

    private $vendorRegistration;
    private $companyPersonnel;

    protected $rules = [
        'name'                  => 'required|max:250',
        'identification_number' => 'required|max:250',
        'email_address'         => 'required|max:250',
        'contact_number'        => 'required|max:250',
        'years_of_experience'   => 'required|numeric|max:100',
        'designation'           => 'required|max:250',
        'amount_of_share'       => 'required|numeric',
        'holding_percentage'    => 'required|numeric|max:100',
    ];

    public function setVendorRegistration(VendorRegistration $vendorRegistration)
    {
        $this->vendorRegistration = $vendorRegistration;
    }

    public function setCompanyPersonnel(CompanyPersonnel $companyPersonnel)
    {
        $this->companyPersonnel = $companyPersonnel;
    }

    protected function setRules($formData)
    {
        if($formData['type'] == CompanyPersonnel::TYPE_SHAREHOLDERS)
        {
            unset($this->rules['email_address'], $this->rules['contact_number'], $this->rules['years_of_experience']);
        }
        else
        {
            unset($this->rules['designation'], $this->rules['amount_of_share'], $this->rules['holding_percentage']);
        }
    }

    public function postParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();

        if($formData['type'] == CompanyPersonnel::TYPE_SHAREHOLDERS)
        {
            $query = CompanyPersonnel::where('vendor_registration_id', '=', $this->vendorRegistration->id)->where('type', '=', CompanyPersonnel::TYPE_SHAREHOLDERS);

            // only for updates
            if($this->companyPersonnel)
            {
                $query->whereNotIn('id', [$this->companyPersonnel->id]);
            }

            $totalHoldingPercentage = $query->sum('holding_percentage');

            $totalHoldingPercentage += $formData['holding_percentage'];

            if($totalHoldingPercentage > 100.0)
            {
                $messageBag->add('holding_percentage', trans('vendorManagement.totalHoldingPercentageExceededLimit'));
            }
        }

        return $messageBag;
    }
}
