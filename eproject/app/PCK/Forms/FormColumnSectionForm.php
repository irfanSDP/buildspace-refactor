<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;

class FormColumnSectionForm extends FormValidator
{
    protected $rules = [
        'name' => 'required',
    ];

    protected $messages = [
        'name.required' => 'Sub Section name is required.',
    ];
}

