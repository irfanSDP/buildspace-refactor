<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class EOTClaimForm extends FormValidator {

	const formTitle = 'Final Claim for Extension of Time (EOT)';

	const accordianId = 's3-submifFinalClaim_%id%';

	protected $rules = [
		'subject'      => 'required',
		'message'      => 'required',
		'days_claimed' => 'required|integer|max:365',
	];

}