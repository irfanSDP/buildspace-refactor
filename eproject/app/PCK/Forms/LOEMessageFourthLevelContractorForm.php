<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class LOEMessageFourthLevelContractorForm extends FormValidator {

	const formTitle = 'Contractor\'s Appeal To The Architect On His Decision Of The Loss And/Or Expense Claim';

	const accordianId = 's4-reply_%id%';

	protected $rules = [
		'subject' => 'required',
		'message' => 'required',
	];

}