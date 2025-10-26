<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AddNewInterimClaimForm extends FormValidator {

	protected $rules = [
		'claim_no' => 'required',
		'month'    => 'required|integer|min:1|max:12',
		'year'     => 'required|integer',
		'note'     => 'required',
	];

}