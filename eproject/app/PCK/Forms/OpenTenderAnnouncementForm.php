<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class OpenTenderAnnouncementForm extends FormValidator{


	protected $rules = 
    [
        'date'        => 'required',
        'description'       => 'required'
    ];

    protected $messages = 
    [
        'date.required'   => 'The date field is required.',
        'description.required'  => 'The description field is required.'
    ];

}