<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;

class FormOfTenderTemplateForm extends FormValidator
{
    protected $rules = [
        'name'       => 'required',
    ];

    protected $messages = [
        'name.required' => 'Template name field is required.',
    ];
}

