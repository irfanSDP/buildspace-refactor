<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AIMessageThirdLevelContractorForm extends FormValidator {

	const formTitle = 'Contractor To Inform The Architect The AI Has Been Executed And Completed';

	/**
	 * Validation rules for creating AIMessageThirdLevelArchitect
	 *
	 * @var array
	 */
	protected $rules = [
		'subject'         => 'required',
		'reason'          => 'required',
		'compliance_date' => 'required|date',
	];

}