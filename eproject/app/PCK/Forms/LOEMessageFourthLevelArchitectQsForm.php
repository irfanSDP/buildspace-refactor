<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\LossOrAndExpenseFourthLevelMessages\LossOrAndExpenseFourthLevelMessage;

class LOEMessageFourthLevelArchitectQsForm extends FormValidator {

	const formTitleOne = 'Architect\'s Decision On The Application Of Loss And/Or Expense Claim Submitted By The Contractor';

	const formTitleTwo = 'QS\'s Evaluation on the Loss And/Or Expense Claim Submitted By The Contractor';

	const accordianId = 's4-reply_%id%';

	public function getValidationRules()
	{
		$decision = LossOrAndExpenseFourthLevelMessage::GRANT_DIFF_AMOUNT;

		return array(
			'subject'                => 'required',
			'message'                => 'required',
			'decision'               => 'required|integer',
			'grant_different_amount' => 'required_if:decision,' . $decision . '|numeric',
		);
	}

}