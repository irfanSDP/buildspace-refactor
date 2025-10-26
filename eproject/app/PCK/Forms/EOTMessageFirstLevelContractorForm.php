<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class EOTMessageFirstLevelContractorForm extends FormValidator {

	const formTitle = 'Contractor Appeal To Architect\'s Decision On Submitted The Notice Of Claim EOT';

	protected $rules = [
		'subject' => 'required',
		'details' => 'required',
	];

}