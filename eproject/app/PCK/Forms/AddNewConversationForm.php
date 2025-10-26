<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AddNewConversationForm extends FormValidator {

	/**
	 * Validation rules for creating Conversation
	 *
	 * @var array
	 */
	protected $rules = [
		'to_viewer'         => 'required|array',
		'subject'           => 'required',
		'purpose_of_issued' => 'required',
		'message'           => 'required',
		'deadline_to_reply' => 'date|after:yesterday',
	];

    protected $messages = array(
        'to_viewer.required' => 'At least one group must be selected.',
    );

}