<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class InterimClaimAdditionalInformationArchitectQsForm extends FormValidator {

	protected $rules = [
		'reference'              => 'required',
		'date'                   => 'required|date',
		'nett_addition_omission' => 'required|numeric',
		'date_of_certificate'    => 'required|date',
		'gross_values_of_works'  => 'required|numeric',
		'amount_in_word'         => 'required',
	];

}