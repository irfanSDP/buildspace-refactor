<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;

class ElementsForm extends FormValidator
{
    protected $rules = [
        'label' => 'required',
    ];

    protected $messages = [
        'label.required' => 'Label is required.',
    ];
}

