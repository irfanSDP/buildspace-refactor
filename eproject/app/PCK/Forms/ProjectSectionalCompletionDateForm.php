<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;

class ProjectSectionalCompletionDateForm extends FormValidator
{
    protected $rules = [
        'date'        => 'required',
        'description' => 'required',
    ];

    protected $messages = [
        'date.required'    => 'Date is required.',
        'date.description' => 'Description is required.',
    ];
}

