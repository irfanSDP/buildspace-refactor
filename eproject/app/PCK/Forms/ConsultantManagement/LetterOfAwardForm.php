<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class LetterOfAwardForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['id'] = 'required|exists:consultant_management_letter_of_awards,id';
    }
}