<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\AdditionalExpenseSecondLevelMessages\AdditionalExpenseSecondLevelMessage;

class AEMessageSecondLevelArchitectForm extends FormValidator {

	const formTitle = 'Architect\'s Decision On Extension Of Deadline For Submitting Final Claim For AE To The Date Requested';

	const accordianId = 's2-reply_%id%';

	public function getValidationRules()
	{
		$decision = AdditionalExpenseSecondLevelMessage::GRANT_DIFF_DEADLINE;

		return array(
			'subject'                  => 'required',
			'message'                  => 'required',
			'decision'                 => 'required|integer',
			'grant_different_deadline' => 'required_if:decision,' . $decision . '|date',
		);
	}

}