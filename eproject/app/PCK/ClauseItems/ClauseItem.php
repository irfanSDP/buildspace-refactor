<?php namespace PCK\ClauseItems;

use Illuminate\Database\Eloquent\Model;

class ClauseItem extends Model {

	public function clause()
	{
		return $this->belongsTo('PCK\Clauses\Clause');
	}

	public function architectInstructions()
	{
		return $this->belongsToMany('PCK\ArchitectInstructions\ArchitectInstruction');
	}

	public function architectInstructionMessages()
	{
		return $this->belongsToMany('PCK\ArchitectInstructionMessages\ArchitectInstructionMessage');
	}

	public function extensionOfTimes()
	{
		return $this->belongsToMany('PCK\ExtensionOfTimes\ExtensionOfTime');
	}

	public function lossOrAndExpenses()
	{
		return $this->belongsToMany('PCK\LossOrAndExpenses\LossOrAndExpense');
	}

	public function additionalExpenses()
	{
		return $this->belongsToMany('PCK\AdditionalExpenses\AdditionalExpense');
	}

}