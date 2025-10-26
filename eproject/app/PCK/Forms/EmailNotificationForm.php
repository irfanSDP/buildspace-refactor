<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class EmailNotificationForm extends FormValidator {

	/**
	 * Validation rules for sending email notifications
	 *
	 * @var array
	 */
	protected $rules = [
		'to_viewer'         => 'required|array',
		'subject'           => 'required',
		'message'           => 'required',
	];

    protected $messages = array(
        'to_viewer.required' => 'At least one recipient must be selected.',
    );

}

