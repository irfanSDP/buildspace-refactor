<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class EmailReminderForm extends FormValidator {

	/**
	 * Validation rules for sending email announcements
	 *
	 * @var array
	 */
	protected $rules = [
		'subject'           => 'required',
		'message'           => 'required',
        'subject2'           => 'required',
        'message2'           => 'required',
	];

    protected $messages = array(
		'subject.required'   => 'This field is required.',
		'message.required'   => 'This field is required.',
        'subject2.required'   => 'This field is required.',
        'message2.required'   => 'This field is required.',
    );

}

