<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AEMessageFirstLevelArchitectForm extends FormValidator {

	const formTitle = 'Architect\'s Decision On The Notice of Intention To Claim AE Submitted By The Contractor';

	const accordianId = 's1-reply_%id%';

	protected $rules = [
		'subject'  => 'required',
		'details'  => 'required',
		'decision' => 'required|boolean',
	];

}