<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class AddNewSiteManagementDefectForm extends FormValidator{


	protected $rules = 
    [
        'locationLevel_0'      => 'required',
        'trade'                => 'required',
        'category'             => 'required',
        'contractor'           => 'required',
        'remark'               => 'required'
    ];

    protected $messages = 
    [
        'locationLevel_0.required' => 'The location field is required.'
    ];

}