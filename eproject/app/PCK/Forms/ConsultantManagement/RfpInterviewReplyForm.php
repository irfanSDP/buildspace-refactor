<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;
use PCK\Users\User;
use PCK\ConsultantManagement\ConsultantManagementRfpInterviewConsultant;

class RfpInterviewReplyForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['status'] = 'required|integer';
        $this->rules['id'] = 'required|exists:consultant_management_rfp_interview_consultants,id';
        $this->rules['cid'] = 'required|exists:companies,id';
        $this->rules['token'] = 'required|exists:consultant_management_rfp_interview_tokens,token';

        $this->messages['status.required'] = 'Please select either Accept or Decline the interview';

        if(array_key_exists('status', $formData) && $formData['status'] == ConsultantManagementRfpInterviewConsultant::STATUS_DECLINED)
        {
            $this->rules['consultant_remarks'] = 'required';
            $this->messages['consultant_remarks.required'] = 'Please state at least a reason for declining the interview';
        }
    }
}
