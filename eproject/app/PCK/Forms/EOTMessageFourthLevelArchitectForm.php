<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\ExtensionOfTimeFourthLevelMessages\ExtensionOfTimeFourthLevelMessage;

class EOTMessageFourthLevelArchitectForm extends FormValidator {

	const formTitle = 'Architect\'s Decision On The Application Of EOT By The Contractor';

	const accordianId = 's4-reply_%id%';

	public function getValidationRules()
	{
		$decision = ExtensionOfTimeFourthLevelMessage::GRANT_DIFF_DEADLINE;

		return array(
			'subject'              => 'required',
			'message'              => 'required',
			'decision'             => 'required|integer',
			'grant_different_days' => 'required_if:decision,' . $decision . '|integer',
		);
	}

}