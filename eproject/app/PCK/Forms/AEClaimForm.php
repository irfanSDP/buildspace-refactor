<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AEClaimForm extends FormValidator {

	const formTitle = 'Final Claim for Additional Expense (AE)';

	const accordianId = 's3-finalClaim_%id%';

	protected $rules = [
		'subject'            => 'required',
		'message'            => 'required',
		'final_claim_amount' => 'required|numeric',
	];

}