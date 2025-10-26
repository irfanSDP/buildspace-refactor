<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AddNewEngineerInstructionForm extends FormValidator {

	/**
	 * Validation rules for creating Company
	 *
	 * @var array
	 */
	protected $rules = [
		'subject'                 => 'required',
		'detailed_elaborations'   => 'required',
		'deadline_to_comply_with' => 'required|date',
	];

}