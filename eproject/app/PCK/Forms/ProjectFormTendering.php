<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ProjectFormTendering extends FormValidator {

	/**
	 * Validation rules for creating or updating Project
	 *
	 * @var array
	 */
	protected $rules = [
		'project_id'   => 'required|integer',
		'publish_date' => 'required',
		'close_date'   => 'required',
		'close_time'   => 'required'
	];

}