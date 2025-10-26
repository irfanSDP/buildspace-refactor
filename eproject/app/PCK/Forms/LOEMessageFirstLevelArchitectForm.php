<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class LOEMessageFirstLevelArchitectForm extends FormValidator {

	const formTitle = 'Architect\'s Decision On The Notice of Intention To Claim L &amp; E Submitted By The Contractor';

	const accordianId = 's1-reply_%id%';

	protected $rules = [
		'subject'  => 'required',
		'details'  => 'required',
		'decision' => 'required|boolean',
	];

}