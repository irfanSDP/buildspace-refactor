<?php namespace PCK\Forms\ConsultantManagement;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;
use PCK\ConsultantManagement\ConsultantManagementOpenRfp;
use PCK\ConsultantManagement\ApprovalDocument;

use PCK\Forms\CustomFormValidator;

class OpenRfpResubmissionVerifierForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$formData['id']);
        $vendorCategoryRfp = $openRfp->consultantManagementRfpRevision->consultantManagementVendorCategoryRfp;
        $approvalDocument = $vendorCategoryRfp->approvalDocument;

        if($approvalDocument && $approvalDocument->status != ApprovalDocument::STATUS_DRAFT)
        {
            $this->rules['document_invalid'] = 'required';
            $this->messages['document_invalid.required'] = ($approvalDocument->status == ApprovalDocument::STATUS_APPROVED) ? 'Cannot tender Resubmission because Approval Document already approved' : 'Cannot tender Resubmission because Approval Document is pending for verification';
        }

        $verifierIds = array_filter($formData['verifiers']);

        if(empty($verifierIds))
        {
            $this->rules['verifiers'] = 'required|integer';
            $this->messages['verifiers.integer'] = 'At least one verifier is required';
        }
    }
}