<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class RequestForVariationUserPermissionGroupForm extends FormValidator
{
	protected $rules = [
        'name' => 'required|max:100'
    ];

    protected $messages = [
        'name.required' => 'Name field is required.',
        'name.max'      => 'Name cannot be longer than :max characters'
    ];
}