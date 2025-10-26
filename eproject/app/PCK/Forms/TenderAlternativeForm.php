<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class TenderAlternativeForm extends FormValidator {

    protected $rules = [
        'tender_alternative' => 'required|array',
    ];

    public function getValidationMessages()
    {
        $messages = parent::getValidationMessages();

        $messages['tender_alternative.required'] = trans('formOfTender.tenderAlternativeRequired');

        return $messages;
    }
}