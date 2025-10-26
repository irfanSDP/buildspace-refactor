<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

use PCK\ConsultantManagement\ConsultantManagementQuestionnaire;

class QuestionnaireForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['cid'] = 'required|exists:consultant_management_contracts,id';
        $this->rules['question'] = 'required';
        $this->rules['type'] = 'required|numeric';

        if(in_array($formData['type'], [ConsultantManagementQuestionnaire::TYPE_MULTI_SELECT, ConsultantManagementQuestionnaire::TYPE_SINGLE_SELECT]) && array_key_exists('options', $formData) && is_array($formData['options']))
        {
            foreach($formData['options'] as $idx => $fields)
            {
                $this->rules['options.'.$idx.'.text'] = 'required|min:1|max:250';

                $this->messages['options.'.$idx.'.text.required'] = 'Text is required';
                
            }
        }
    }
}