<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class EngineerInstructionArchitectUpdateForm extends FormValidator {

	protected $rules = [
		'ais' => 'required|array',
	];

}