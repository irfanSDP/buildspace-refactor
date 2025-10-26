<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class LetterOfAwardVerifierForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['id'] = 'required|exists:consultant_management_letter_of_awards,id';

        $verifierIds = array_filter($formData['verifiers']);

        if(empty($verifierIds))
        {
            $this->rules['verifiers'] = 'required|integer';
            $this->messages['verifiers.integer'] = 'At least one verifier is required';
        }
    }
}