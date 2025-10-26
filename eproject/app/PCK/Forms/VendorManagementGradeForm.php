<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;

class VendorManagementGradeForm extends FormValidator
{
    protected $rules = [
        'name'       => 'required',
    ];

    protected $messages = [
        'name.required' => 'Grade name is required.',
    ];
}

