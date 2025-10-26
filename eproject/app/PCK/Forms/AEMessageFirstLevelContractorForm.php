<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AEMessageFirstLevelContractorForm extends FormValidator {

	const formTitle = 'Contractor Appeal To Architect\'s Decision On Submitted The Notice Of Claim AE';

	const accordianId = 's1-reply_%id%';

	protected $rules = [
		'subject' => 'required',
		'details' => 'required',
	];

}