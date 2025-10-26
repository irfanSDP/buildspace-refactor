<?php namespace PCK\AdditionalExpenses;

trait ReminderRelations {

	public function architectInstruction()
	{
		return $this->belongsTo('PCK\ArchitectInstructions\ArchitectInstruction');
	}

	public function firstLevelMessages()
	{
		return $this->hasMany('PCK\AdditionalExpenseFirstLevelMessages\AdditionalExpenseFirstLevelMessage')->orderBy('id', 'asc');
	}

	public function contractorConfirmDelay()
	{
		return $this->hasOne('PCK\AdditionalExpenseContractorConfirmDelays\AdditionalExpenseContractorConfirmDelay');
	}

	public function secondLevelMessages()
	{
		return $this->hasMany('PCK\AdditionalExpenseSecondLevelMessages\AdditionalExpenseSecondLevelMessage')->orderBy('id', 'asc');
	}

	public function additionalExpenseClaim()
	{
		return $this->hasOne('PCK\AdditionalExpenseClaims\AdditionalExpenseClaim');
	}

	public function thirdLevelMessages()
	{
		return $this->hasMany('PCK\AdditionalExpenseThirdLevelMessages\AdditionalExpenseThirdLevelMessage')->orderBy('id', 'asc');
	}

	public function fourthLevelMessages()
	{
		return $this->hasMany('PCK\AdditionalExpenseFourthLevelMessages\AdditionalExpenseFourthLevelMessage')->orderBy('id', 'asc');
	}

	public function additionalExpenseInterimClaim()
	{
		return $this->hasOne('PCK\AdditionalExpenseInterimClaims\AdditionalExpenseInterimClaim');
	}

}