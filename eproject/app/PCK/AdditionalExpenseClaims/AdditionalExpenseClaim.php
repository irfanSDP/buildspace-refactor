<?php namespace PCK\AdditionalExpenseClaims;

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class AdditionalExpenseClaim extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected static function boot()
	{
		parent::boot();

		static::saved(function($model)
		{
			$ae                 = $model->additionalExpense;
			$ae->amount_claimed = $model->final_claim_amount;

			$ae->save();
		});
	}

	public function additionalExpense()
	{
		return $this->belongsTo('PCK\AdditionalExpenses\AdditionalExpense');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

}