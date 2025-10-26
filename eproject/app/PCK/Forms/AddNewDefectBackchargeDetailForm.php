<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class AddNewDefectBackchargeDetailForm extends FormValidator{

	protected $rules = 
    [      
        'machinery'        => 'required|numeric|min:0',
        'material'         => 'required|numeric|min:0',
        'labour'           => 'required|numeric|min:0'
    ];
}