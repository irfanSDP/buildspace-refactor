<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class AddNewSiteManagementSiteDiaryGeneralForm extends FormValidator{


	protected $rules = 
    [
        'general_date' => 'required',
    ];

    protected $messages = 
    [
        'general_date.required' => 'The date field is required.',
    ];

}