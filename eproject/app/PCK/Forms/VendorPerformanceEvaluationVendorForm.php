<?php namespace PCK\Forms;

use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\Companies\Company;
use PCK\ContractGroups\Types\Role;

class VendorPerformanceEvaluationVendorForm extends CustomFormValidator {
    protected $evaluation;
    protected $company;

    protected function setRules($formData)
    {
        $isBuCompany = $this->company->hasProjectRole($this->evaluation->project, Role::PROJECT_OWNER);

        if( $this->evaluation->type == VendorPerformanceEvaluation::TYPE_180 )
        {
            if($isBuCompany)
            {
                $this->rules['evaluator_ids'] = 'required|array|min:1';
            }
        }
        else
        {
            $this->rules['evaluator_ids'] = 'required|array|min:1';
        }

        $this->messages['evaluator_ids.required'] = trans('vendorManagement.evaluatorRequiredErrorMessage');
    }

    public function setEvaluation(VendorPerformanceEvaluation $evaluation)
    {
        $this->evaluation = $evaluation;
    }

    public function setCompany(Company $company)
    {
        $this->company = $company;
    }
}