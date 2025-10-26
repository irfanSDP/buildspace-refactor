<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AIMessageThirdLevelArchitectForm extends FormValidator {

	const formTitle = 'Architect To Confirm Contractor\'s Compliance Of AI';

	const accordianId = 's2-aiThirdMessage_%id%';

	/**
	 * Validation rules for creating AIMessageThirdLevelArchitect
	 *
	 * @var array
	 */
	protected $rules = [
		'subject'           => 'required',
		'reason'            => 'required',
		'compliance_status' => 'required|integer',
	];

}