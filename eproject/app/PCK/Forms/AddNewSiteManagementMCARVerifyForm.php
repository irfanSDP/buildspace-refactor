<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class AddNewSiteManagementMCARVerifyForm extends FormValidator{


	protected $rules = 
    [
        'satisfactory'      => 'required',
        'comment'			=> 'required'

    ];

}