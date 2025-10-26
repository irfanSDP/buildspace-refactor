<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class EOTMessageThirdLevelContractorForm extends FormValidator {

	const formTitle = 'Contractor To Provide Further Particulars For The Final Claim for EOT';

	const accordianId = 's3-reply_%id%';

	protected $rules = [
		'subject' => 'required',
		'message' => 'required',
	];

}