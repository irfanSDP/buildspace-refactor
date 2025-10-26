<?php namespace PCK\ExtensionOfTimes;

trait ReminderRelations {

	public function architectInstruction()
	{
		return $this->belongsTo('PCK\ArchitectInstructions\ArchitectInstruction');
	}

	public function firstLevelMessages()
	{
		return $this->hasMany('PCK\ExtensionOfTimeFirstLevelMessages\ExtensionOfTimeFirstLevelMessage')->orderBy('id', 'asc');
	}

	public function eotContractorConfirmDelay()
	{
		return $this->hasOne('PCK\ExtensionOfTimeContractorConfirmDelays\ExtensionOfTimeContractorConfirmDelay');
	}

	public function secondLevelMessages()
	{
		return $this->hasMany('PCK\ExtensionOfTimeSecondLevelMessages\ExtensionOfTimeSecondLevelMessage')->orderBy('id', 'asc');
	}

	public function extensionOfTimeClaim()
	{
		return $this->hasOne('PCK\ExtensionOfTimeClaims\ExtensionOfTimeClaim');
	}

	public function thirdLevelMessages()
	{
		return $this->hasMany('PCK\ExtensionOfTimeThirdLevelMessages\ExtensionOfTimeThirdLevelMessage')->orderBy('id', 'asc');
	}

	public function fourthLevelMessages()
	{
		return $this->hasMany('PCK\ExtensionOfTimeFourthLevelMessages\ExtensionOfTimeFourthLevelMessage')->orderBy('id', 'asc');
	}

}