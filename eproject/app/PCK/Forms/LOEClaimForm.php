<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class LOEClaimForm extends FormValidator {

	const formTitle = 'Final Claim for Loss And/Or Expense (L &amp; E)';

	const accordianId = 's3-finalClaim_%id%';

	protected $rules = [
		'subject'            => 'required',
		'message'            => 'required',
		'final_claim_amount' => 'required|numeric',
	];

}