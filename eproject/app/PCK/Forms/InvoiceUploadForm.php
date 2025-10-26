<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class InvoiceUploadForm extends FormValidator {

	protected $rules = [
		'uploaded_files' => 'required|array',
	];

	public function validate($formData)
	{
		$this->messages = [
			'uploaded_files.required' => trans('files.uploadAtLeastOne'),
		];

		parent::validate($formData);
	}

}