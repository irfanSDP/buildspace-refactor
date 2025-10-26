<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ConversationReplyForm extends FormValidator {

	/**
	 * Validation rules for creating Conversation Reply
	 *
	 * @var array
	 */
	protected $rules = [
		'message' => 'required',
	];

}