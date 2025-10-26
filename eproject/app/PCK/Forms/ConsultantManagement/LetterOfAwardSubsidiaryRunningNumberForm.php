<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

use PCK\ConsultantManagement\LetterOfAwardSubsidiaryRunningNumber;

class LetterOfAwardSubsidiaryRunningNumberForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $subsidiaryRunningNumber = LetterOfAwardSubsidiaryRunningNumber::find((int)$formData['id']);
        if($subsidiaryRunningNumber)
        {
            $this->rules['next_running_number'] = 'required|integer|min:'.($subsidiaryRunningNumber->getHighestRunningNumber() + 1);
        }
        else
        {
            $this->rules['subsidiary_id'] = 'required|exists:subsidiaries,id';
            $this->rules['next_running_number'] = 'required|integer|min:1';
        }
    }
}