<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class AddNewSiteManagementSiteDiaryWeatherForm extends FormValidator{


	protected $rules = 
    [
        'weather_id' => 'required',
    ];

    protected $messages = 
    [
        'weather_id.required' => 'The weather field is required.',

    ];

}