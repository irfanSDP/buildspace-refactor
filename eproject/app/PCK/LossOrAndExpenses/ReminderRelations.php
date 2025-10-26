<?php namespace PCK\LossOrAndExpenses;

trait ReminderRelations {

	public function firstLevelMessages()
	{
		return $this->hasMany('PCK\LossOrAndExpenseFirstLevelMessages\LossOrAndExpenseFirstLevelMessage')->orderBy('id', 'asc');
	}

	public function contractorConfirmDelay()
	{
		return $this->hasOne('PCK\LossOrAndExpenseContractorConfirmDelays\LossOrAndExpenseContractorConfirmDelay');
	}

	public function secondLevelMessages()
	{
		return $this->hasMany('PCK\LossOrAndExpenseSecondLevelMessages\LossOrAndExpenseSecondLevelMessage')->orderBy('id', 'asc');
	}

	public function lossOrAndExpenseClaim()
	{
		return $this->hasOne('PCK\LossOrAndExpenseClaims\LossOrAndExpenseClaim');
	}

	public function thirdLevelMessages()
	{
		return $this->hasMany('PCK\LossOrAndExpenseThirdLevelMessages\LossOrAndExpenseThirdLevelMessage')->orderBy('id', 'asc');
	}

	public function fourthLevelMessages()
	{
		return $this->hasMany('PCK\LossOrAndExpenseFourthLevelMessages\LossOrAndExpenseFourthLevelMessage')->orderBy('id', 'asc');
	}

	public function lossOrAndExpenseInterimClaim()
	{
		return $this->hasOne('PCK\LossOrAndExpenseInterimClaims\LossOrAndExpenseInterimClaim');
	}

}