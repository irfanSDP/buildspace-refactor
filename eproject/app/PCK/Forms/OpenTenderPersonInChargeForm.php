<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class OpenTenderPersonInChargeForm extends FormValidator{


	protected $rules = 
    [
        'name'        => 'required',
        'email'       => 'required'
    ];

    protected $messages = 
    [
        'name.required'   => 'The name field is required.',
        'email.required'  => 'The email field is required.'
    ];

}