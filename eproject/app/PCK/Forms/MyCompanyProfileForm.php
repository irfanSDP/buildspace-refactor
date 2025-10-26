<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class MyCompanyProfileForm extends FormValidator {

	protected $rules = [
		'name' => 'required',
	];

}