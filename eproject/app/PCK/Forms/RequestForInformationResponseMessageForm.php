<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class RequestForInformationResponseMessageForm extends FormValidator {

    protected $rules = [
        'content'   => 'required|max:200',
        'verifiers' => 'array',
    ];

}