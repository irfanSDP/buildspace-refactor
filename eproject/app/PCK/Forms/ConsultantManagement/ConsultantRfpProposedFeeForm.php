<?php namespace PCK\Forms\ConsultantManagement;

use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;

use PCK\Forms\CustomFormValidator;

class ConsultantRfpProposedFeeForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $callingRfp  = ConsultantManagementCallingRfp::findOrFail((int)$formData['id']);
        $vendorCategoryRfp = $callingRfp->consultantManagementRfpRevision->consultantManagementVendorCategoryRfp;

        if($vendorCategoryRfp->cost_type != ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST)
        {
            $this->rules['proposed_fee_percentage'] = 'required|numeric|min:0|max:100';
        }

        $this->rules['consultant_management_subsidiary_id'] = 'required|exists:consultant_management_subsidiaries,id';
        $this->rules['proposed_fee_amount'] = 'required|numeric|min:0';

    }
}