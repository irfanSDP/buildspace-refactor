<?php namespace PCK\ArchitectInstructions;

trait ReminderRelations {

	public function messages()
	{
		return $this->hasMany('PCK\ArchitectInstructionMessages\ArchitectInstructionMessage')->orderBy('id', 'asc');
	}

	public function thirdLevelMessages()
	{
		return $this->hasMany('PCK\ArchitectInstructionThirdLevelMessages\ArchitectInstructionThirdLevelMessage')->orderBy('id', 'asc');
	}

	public function extensionOfTimes()
	{
		return $this->hasMany('PCK\ExtensionOfTimes\ExtensionOfTime')->orderBy('id', 'desc');
	}

	public function latestExtensionOfTime()
	{
		return $this->hasOne('PCK\ExtensionOfTimes\ExtensionOfTime')->orderBy('id', 'asc');
	}

	public function additionalExpenses()
	{
		return $this->hasMany('PCK\AdditionalExpenses\AdditionalExpense')->orderBy('id', 'desc');
	}

	public function latestAdditionalExpense()
	{
		return $this->hasOne('PCK\AdditionalExpenses\AdditionalExpense')->orderBy('id', 'asc');
	}

	public function lossOrAndExpenses()
	{
		return $this->hasMany('PCK\LossOrAndExpenses\LossOrAndExpense')->orderBy('id', 'desc');
	}

	public function latestLossOrAndExpense()
	{
		return $this->hasOne('PCK\LossOrAndExpenses\LossOrAndExpense')->orderBy('id', 'asc');
	}

	public function engineerInstructions()
	{
		return $this->belongsToMany('PCK\EngineerInstructions\EngineerInstruction')->withTimestamps();
	}

	public function architectInstructionInterimClaim()
	{
		return $this->hasOne('PCK\ArchitectInstructionInterimClaims\ArchitectInstructionInterimClaim');
	}

}