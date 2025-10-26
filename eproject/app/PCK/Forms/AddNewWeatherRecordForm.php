<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AddNewWeatherRecordForm extends FormValidator {

	/**
	 * Validation rules for creating Weather Record
	 *
	 * @var array
	 */
	protected $rules = [
		'date' => 'required|date',
		'note' => 'required',
	];

}