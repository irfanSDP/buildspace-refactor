<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class EOTMessageFirstLevelArchitectForm extends FormValidator {

	const formTitle = 'Architect\'s Decision On The Notice of Intention To Claim EOT Submitted By The Contractor';

	const accordianId = 's1-eotFirstMessage_%id%';

	protected $rules = [
		'subject'  => 'required',
		'details'  => 'required',
		'decision' => 'required|boolean',
	];

}