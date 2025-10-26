<?php namespace PCK\Forms\ConsultantManagement;

use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementCompanyRole;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementOpenRfp;

use PCK\Forms\CustomFormValidator;

class OpenRfpResubmissionVerifyForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['id'] = 'required|exists:consultant_management_open_rfp,id';

        if(array_key_exists('reject', $formData))
        {
            $this->rules['remarks'] = 'required';
            $this->messages['remarks.required'] = 'Remarks is required';
        }
        else
        {
            $openRfp = ConsultantManagementOpenRfp::findOrFail($formData['id']);

            $rfpRevision = $openRfp->consultantManagementRfpRevision;
            $callingRfp = $rfpRevision->callingRfp;
            $vendorCategoryRfp = $rfpRevision->consultantManagementVendorCategoryRfp;
            $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

            $closingRfpDate = new Carbon($callingRfp->closing_rfp_date, $consultantManagementContract->timezone);
            $nowDate = Carbon::now($consultantManagementContract->timezone);
            
            if($nowDate->lt($closingRfpDate))
            {
                $this->rules['rfp_closing_date'] = 'required';
                $this->messages['rfp_closing_date.required'] = 'RFP is still open in calling RFP. Please update the calling RFP closing date before initiating RFP Resubmission';
            }
        }
    }
}