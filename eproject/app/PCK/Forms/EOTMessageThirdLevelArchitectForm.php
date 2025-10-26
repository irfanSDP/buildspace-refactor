<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class EOTMessageThirdLevelArchitectForm extends FormValidator {

	const formTitle = 'Architect To Request for Further Particulars from the Contractor';

	const accordianId = 's3-reply_%id%';

	protected $rules = [
		'subject'                 => 'required',
		'message'                 => 'required',
		'deadline_to_comply_with' => 'required|date',
	];

}