<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class TendererRateAttachmentsForm extends FormValidator {

	protected $rules = [
		'uploaded_files' => 'array',
	];

}