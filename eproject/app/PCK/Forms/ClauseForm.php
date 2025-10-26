<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ClauseForm extends FormValidator {

	/**
	 * Validation rules for creating or updating Project
	 *
	 * @var array
	 */
	protected $rules = [
		'name' => 'required'
	];

}