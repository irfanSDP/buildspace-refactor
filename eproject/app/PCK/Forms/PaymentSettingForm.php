<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;

class PaymentSettingForm extends FormValidator
{
    protected $rules = [
        'name'          => 'required',
        'accountNumber' => 'required',
    ];

    protected $messages = [
        'name.required'          => 'Name is required.',
        'accountNumber.required' => 'Account number is required.',
    ];
}

