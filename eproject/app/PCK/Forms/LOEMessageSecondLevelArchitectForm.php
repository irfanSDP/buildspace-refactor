<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\LossOrAndExpenseSecondLevelMessages\LossOrAndExpenseSecondLevelMessage;

class LOEMessageSecondLevelArchitectForm extends FormValidator {

	const formTitle = 'Architect\'s Decision On Extension Of Deadline For Submitting Final Claim For L &amp; E To The Date Requested';

	const accordianId = 's2-reply_%id%';

	public function getValidationRules()
	{
		$decision = LossOrAndExpenseSecondLevelMessage::GRANT_DIFF_DEADLINE;

		return array(
			'subject'                  => 'required',
			'message'                  => 'required',
			'decision'                 => 'required|integer',
			'grant_different_deadline' => 'required_if:decision,' . $decision . '|date',
		);
	}

}