<?php namespace PCK\LossOrAndExpenseInterimClaims; 

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class LossOrAndExpenseInterimClaim extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	public function lossOrAndExpense()
	{
		return $this->belongsTo('PCK\LossOrAndExpenses\LossOrAndExpense');
	}

	public function interimClaim()
	{
		return $this->belongsTo('PCK\InterimClaims\InterimClaim');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

}