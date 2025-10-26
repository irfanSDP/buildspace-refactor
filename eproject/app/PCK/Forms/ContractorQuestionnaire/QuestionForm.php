<?php namespace PCK\Forms\ContractorQuestionnaire;

use PCK\Forms\CustomFormValidator;

use PCK\ContractorQuestionnaire\Question;

class QuestionForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['cid'] = 'required|exists:companies,id';
        $this->rules['question'] = 'required';
        $this->rules['type'] = 'required|numeric';

        if(in_array($formData['type'], [Question::TYPE_MULTI_SELECT, Question::TYPE_SINGLE_SELECT]) && array_key_exists('options', $formData) && is_array($formData['options']))
        {
            foreach($formData['options'] as $idx => $fields)
            {
                $this->rules['options.'.$idx.'.text'] = 'required|min:1|max:250';

                $this->messages['options.'.$idx.'.text.required'] = 'Text is required';
                
            }
        }
    }
}