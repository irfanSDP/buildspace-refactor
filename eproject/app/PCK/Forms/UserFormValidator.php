<?php namespace PCK\Forms;

use Zizaco\Confide\UserValidator;

class UserFormValidator extends UserValidator {

	/**
	 * Validation rules for this Validator.
	 *
	 * @var array
	 */
	public $rules = [
		'create' => [
			'name'           => 'required|min:4',
			'contact_number' => 'required',
			'email'          => 'required|email',
		],
		'update' => [
			'name'           => 'required|min:4',
			'contact_number' => 'required',
		]
	];

}