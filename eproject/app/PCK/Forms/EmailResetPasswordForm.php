<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class EmailResetPasswordForm extends FormValidator {

	protected $rules = [
		'password' => 'required|min:6|confirmed',
	];

}