<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

use PCK\ConsultantManagement\ConsultantManagementRfpQuestionnaire;

class RfpQuestionnaireForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['vcrid'] = 'required|exists:consultant_management_vendor_categories_rfp,id';
        $this->rules['cid'] = 'required|exists:companies,id';
        $this->rules['question'] = 'required';
        $this->rules['type'] = 'required|numeric';

        if(in_array($formData['type'], [ConsultantManagementRfpQuestionnaire::TYPE_MULTI_SELECT, ConsultantManagementRfpQuestionnaire::TYPE_SINGLE_SELECT]) && array_key_exists('options', $formData) && is_array($formData['options']))
        {
            foreach($formData['options'] as $idx => $fields)
            {
                $this->rules['options.'.$idx.'.text'] = 'required|min:1|max:250';

                $this->messages['options.'.$idx.'.text.required'] = 'Text is required';
                
            }
        }
    }
}