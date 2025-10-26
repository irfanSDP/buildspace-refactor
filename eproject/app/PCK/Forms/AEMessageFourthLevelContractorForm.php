<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AEMessageFourthLevelContractorForm extends FormValidator {

	const formTitle = 'Contractor\'s Appeal To The Architect On His Decision Of The Additional Expense Claim';

	const accordianId = 's4-reply_%id%';

	protected $rules = [
		'subject' => 'required',
		'message' => 'required',
	];

}