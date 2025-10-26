<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class LossAndOrExpenseInterimClaimForm extends FormValidator {

	const formTitle = 'Payment for the Loss And/Or Expense Claim';

	protected $rules = [
		'interim_claim_id' => 'required',
	];

}