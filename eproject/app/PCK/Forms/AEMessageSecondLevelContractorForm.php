<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AEMessageSecondLevelContractorForm extends FormValidator {

	const formTitleOne = 'Contractor To Request To Extend The Deadline For Submitting Final AE Claim';

	const formTitleTwo = 'Contractor\'s Appeal To The Architect To Extend The Deadline For Submitting Final Claim for AE To The Date Requested';

	const accordianId = 's2-reply_%id%';

	protected $rules = [
		'subject'                => 'required',
		'message'                => 'required',
		'requested_new_deadline' => 'required|date',
	];

}