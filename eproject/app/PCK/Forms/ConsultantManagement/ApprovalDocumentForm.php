<?php namespace PCK\Forms\ConsultantManagement;

use PCK\ConsultantManagement\ConsultantManagementOpenRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;

use PCK\Forms\CustomFormValidator;

class ApprovalDocumentForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['document_reference_no'] = 'required|min:1|max:100|iunique:consultant_management_approval_documents,document_reference_no,'.$formData['id'].',id';
        $this->rules['open_rfp_id'] = 'required|exists:consultant_management_open_rfp,id';

        $openRfp = ConsultantManagementOpenRfp::findOrFail((int)$formData['open_rfp_id']);
        $vendorCategoryRfp = $openRfp->consultantManagementRfpRevision->consultantManagementVendorCategoryRfp;
        $latestRevision = $vendorCategoryRfp->getLatestRfpRevision();

        if(!$latestRevision || !$latestRevision->callingRfp || $latestRevision->callingRfp->status != ConsultantManagementCallingRfp::STATUS_APPROVED || $latestRevision->callingRfp->isCallingRFpStillOpen() || $openRfp->status != ConsultantManagementOpenRfp::STATUS_APPROVED)
        {
            $this->rules['document_invalid'] = 'required';
            $this->messages['document_invalid.required'] = 'Cannot create Approval Document. Please check the latest List of Consultant or Calling RFP status';
        }

        $this->messages['document_reference_no.iunique'] = "Document Reference No. has already been taken";
    }
}