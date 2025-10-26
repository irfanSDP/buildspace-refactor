<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class LetterOfAwardTemplateForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['title'] = 'required|min:1|max:100|unique:consultant_management_letter_of_award_templates,title,'.$formData['id'].',id';
    }
}