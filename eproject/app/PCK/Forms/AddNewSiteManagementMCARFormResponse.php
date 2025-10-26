<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class AddNewSiteManagementMCARFormResponse extends FormValidator{


	protected $rules = 
    [      
        'cause'  => 'required',
        'action' => 'required',
        'applicable' => 'required',
        'commitment_date' => 'required'

    ];


}