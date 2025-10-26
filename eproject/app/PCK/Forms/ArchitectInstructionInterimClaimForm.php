<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ArchitectInstructionInterimClaimForm extends FormValidator {

	const formTitle = 'Architect to Mention in Which Interim Certification the Set-off is Made';

	protected $rules = [
		'interim_claim_id'     => 'required',
		'subject'              => 'required',
		'letter_to_contractor' => 'required',
	];

}