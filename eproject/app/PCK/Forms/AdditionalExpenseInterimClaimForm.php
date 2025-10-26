<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AdditionalExpenseInterimClaimForm extends FormValidator {

	const formTitle = 'Payment for the Additional Expense Claim';

	protected $rules = [
		'interim_claim_id' => 'required',
	];

}
