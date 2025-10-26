<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class RequestForInformationRequestMessageForm extends FormValidator {

    protected $rules = [
        'content'         => 'required|max:200',
        'reply_deadline'  => 'required|date',
        'contract_groups' => 'required|array|arrayNotEmpty',
        'verifiers'       => 'array',
    ];

    protected $messages = [
        'contract_groups.required' => 'Please select at least one party to request information from.',
    ];

}