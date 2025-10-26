<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class AddNewSiteManagementSiteDiaryMachineryForm extends FormValidator{


	protected $rules = 
    [
        'machinery_excavator'              => 'required',
        'machinery_backhoe'                => 'required',
        'machinery_crane'                  => 'required',
    ];

    protected $messages = 
    [
        'machinery_excavator.required' => 'The excavator field is required.',
        'machinery_backhoe.required'   => 'The backhoe field is required.',
        'machinery_crane.required'     => 'The crane field is required.'
    ];

}