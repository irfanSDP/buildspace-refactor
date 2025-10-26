<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AEMessageThirdLevelArchitectQsForm extends FormValidator {

	const formTitleOne = 'Architect To Request for Further Particulars from the Contractor';

	const formTitleTwo = 'QS To Request for Further Particulars from the Contractor';

	const accordianId = 's3-reply_%id%';

	protected $rules = [
		'subject'                 => 'required',
		'message'                 => 'required',
		'deadline_to_comply_with' => 'required|date',
	];

}