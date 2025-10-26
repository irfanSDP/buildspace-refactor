<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class AddNewSiteManagementSiteDiaryVisitorForm extends FormValidator{


	protected $rules = 
    [
        'visitor_name'               => 'required',
        'visitor_company_name'       => 'required'
    ];

    protected $messages = 
    [
        'visitor_name.required'          => 'The visitor_name field is required.',
        'visitor_company_name.required'  => 'The visitor_company_name field is required.'
    ];

}