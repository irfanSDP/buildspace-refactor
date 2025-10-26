<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\AdditionalExpenseFourthLevelMessages\AdditionalExpenseFourthLevelMessage;

class AEMessageFourthLevelArchitectQsForm extends FormValidator {

	const formTitleOne = 'Architect\'s Decision On The Application Of Additional Expense Claim Submitted By The Contractor';

	const formTitleTwo = 'QS\'s Evaluation on the Additional Expense Claim Submitted By The Contractor';

	const accordianId = 's4-reply_%id%';

	public function getValidationRules()
	{
		$decision = AdditionalExpenseFourthLevelMessage::GRANT_DIFF_AMOUNT;

		return array(
			'subject'                => 'required',
			'message'                => 'required',
			'decision'               => 'required|integer',
			'grant_different_amount' => 'required_if:decision,' . $decision . '|numeric',
		);
	}

}