<?php namespace PCK\AdditionalExpenseInterimClaims;

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class AdditionalExpenseInterimClaim extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	public function additionalExpense()
	{
		return $this->belongsTo('PCK\AdditionalExpenses\AdditionalExpense');
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