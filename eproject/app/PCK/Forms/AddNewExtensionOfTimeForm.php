<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AddNewExtensionOfTimeForm extends FormValidator {

	/**
	 * Validation rules for creating Company
	 *
	 * @var array
	 */
	protected $rules = [
		'architect_instruction_id'   => 'required',
		'selected_clauses'           => 'required|array',
		'commencement_date_of_event' => 'required|date',
		'subject'                    => 'required',
		'detailed_elaborations'      => 'required',
		'initial_estimate_of_eot'    => 'required|integer|max:365',
	];

}