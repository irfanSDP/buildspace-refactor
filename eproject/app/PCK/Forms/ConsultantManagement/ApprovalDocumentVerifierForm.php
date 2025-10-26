<?php namespace PCK\Forms\ConsultantManagement;

use PCK\ConsultantManagement\ConsultantManagementOpenRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;

use PCK\Forms\CustomFormValidator;

class ApprovalDocumentVerifierForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$formData['open_rfp_id']);
        $vendorCategoryRfp = $openRfp->consultantManagementRfpRevision->consultantManagementVendorCategoryRfp;
        $latestRevision = $vendorCategoryRfp->getLatestRfpRevision();

        if(!$latestRevision || !$latestRevision->callingRfp || $latestRevision->callingRfp->status != ConsultantManagementCallingRfp::STATUS_APPROVED || $latestRevision->callingRfp->isCallingRFpStillOpen() || $openRfp->status != ConsultantManagementOpenRfp::STATUS_APPROVED)
        {
            $this->rules['document_invalid'] = 'required';
            $this->messages['document_invalid.required'] = 'Cannot create Approval Document. Please check the latest List of Consultant or Calling RFP status';
        }
        
        $verifierIds = array_filter($formData['verifiers']);

        if(empty($verifierIds))
        {
            $this->rules['verifiers'] = 'required|integer';
            $this->messages['verifiers.integer'] = 'At least one verifier is required';
        }
    }
}