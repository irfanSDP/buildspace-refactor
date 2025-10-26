<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AEContractorConfirmDelayForm extends FormValidator {

	const formTitle = 'Contractor To Confirm That The Matters Referred to in the Additional Expense Have Ended';

	const accordianId = 's2-contractorConfirmDelayOver_%id%';

	protected $rules = [
		'subject'                     => 'required',
		'message'                     => 'required',
		'date_on_which_delay_is_over' => 'required|date',
	];

}