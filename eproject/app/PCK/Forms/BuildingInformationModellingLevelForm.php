<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;

class BuildingInformationModellingLevelForm extends FormValidator
{
    protected $rules = [
        'name'       => 'required',
    ];

    protected $messages = [
        'name.required' => 'BIM level name is required.',
    ];
}

