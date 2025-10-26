<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class OpenTenderTenderDocumentForm extends FormValidator{

	protected $rules = 
    [
        'description'  => 'required',
    ];

    protected $messages = 
    [
        'description.required'   => 'The description field is required.',
    ];

}