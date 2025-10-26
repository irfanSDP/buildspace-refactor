<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class CalendarForm extends FormValidator {

	/**
	 * Validation rules for creating or updating Calendar
	 *
	 * @var array
	 */

	protected $rules = [
		'country_id'  => 'required|integer',
		'state_id'    => 'integer',
		'description' => 'required',
		'name'        => 'required',
		'start_date'  => 'required|date',
		'end_date'    => 'required|date',
		'event_type'  => 'required|integer'
	];

}