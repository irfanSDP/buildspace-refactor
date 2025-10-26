<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;
use PCK\Users\User;

class RfpInterviewForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['title'] = 'required|min:1|max:250';
        $this->rules['interview_date'] = 'required|date';
        $this->rules['vendor_category_rfp_id'] = 'required|exists:consultant_management_vendor_categories_rfp,id';
        $this->rules['calling_rfp_id'] = 'required|exists:consultant_management_calling_rfp,id';

        if(array_key_exists('consultants', $formData) && is_array($formData['consultants']) && !empty($formData['consultants']))
        {
            foreach($formData['consultants'] as $idx => $fields)
            {
                $this->rules['consultants.'.$idx.'.interview_timestamp'] = 'required|date|after:interview_date';
                $this->rules['consultants.'.$idx.'.id'] = 'required|exists:companies,id';

                $this->messages['consultants.'.$idx.'.interview_timestamp.required'] = 'Time is required';
                $this->messages['consultants.'.$idx.'.interview_timestamp.after'] = 'Time cannot be before Interview Date';

                $adminUserCount = User::where('is_admin', '=', true)->where('company_id', '=', $fields['id'])->count();
                if(empty($adminUserCount))
                {
                    $this->rules['consultants.'.$idx.'.admin_user'] = 'required';
                    $this->messages['consultants.'.$idx.'.admin_user.required'] = 'Company does not have admin user';
                }
            }
        }
        else
        {
            $this->rules['consultants'] = 'required';
            $this->messages['consultants.required'] = 'At least one consultant is required';
        }
    }
}
