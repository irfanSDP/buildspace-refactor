<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class EmailAnnouncementForm extends FormValidator {

	/**
	 * Validation rules for sending email announcements
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
		'subject.required'   => 'The subject field is required.',
		'message.required'   => 'The message field is required.',
    );

}

