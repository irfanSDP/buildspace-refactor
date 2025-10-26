<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class EOTContractorConfirmDelayForm extends FormValidator {

	const formTitle = 'Contractor To Confirm That The Cause Of The Delay Is Over';

	const accordianId = 's2-delayIsOver_%id%';

	protected $rules = [
		'subject'                     => 'required',
		'message'                     => 'required',
		'date_on_which_delay_is_over' => 'required|date',
	];

}