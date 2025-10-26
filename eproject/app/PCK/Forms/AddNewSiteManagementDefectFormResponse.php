<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class AddNewSiteManagementDefectFormResponse extends FormValidator{


	protected $rules = 
    [      
        'remark'        => 'required'
    ];

}