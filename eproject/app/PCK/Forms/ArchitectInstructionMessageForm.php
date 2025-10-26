<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ArchitectInstructionMessageForm extends FormValidator {

	const formTitleOne = 'Architect To Specify The Provision of the Conditions That Empowers The Issuance Of The AI';

	const formTitleTwo = 'Contractor to Request Architect To Specify The Clause That Empowers The Issuance Of The AI';

	const accordianId = 's1-aiFirstMessage_%id%';

	/**
	 * Validation rules for creating Company
	 *
	 * @var array
	 */
	protected $rules = [
		'subject' => 'required',
		'reason'  => 'required',
	];

}