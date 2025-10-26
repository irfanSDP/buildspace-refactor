<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AEMessageThirdLevelContractorForm extends FormValidator {

	const formTitle = 'Contractor To Provide Further Particulars For The Final Claim for Additional Expense';

	const accordianId = 's3-reply_%id%';

	protected $rules = [
		'subject' => 'required',
		'message' => 'required',
	];

}