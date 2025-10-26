<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;

class ElementRejectionsForm extends FormValidator
{
    protected $rules = [
        'remarks' => 'required',
    ];

    protected $messages = [
        'remarks.required' => 'Remarks is required.',
    ];
}

