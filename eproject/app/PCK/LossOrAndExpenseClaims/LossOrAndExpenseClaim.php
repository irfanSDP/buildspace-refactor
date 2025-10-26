<?php namespace PCK\LossOrAndExpenseClaims; 

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class LossOrAndExpenseClaim extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected static function boot()
	{
		parent::boot();

		static::saved(function($model)
		{
			$loe                 = $model->lossOrAndExpense;
			$loe->amount_claimed = $model->final_claim_amount;

			$loe->save();
		});
	}

	public function lossOrAndExpense()
	{
		return $this->belongsTo('PCK\LossOrAndExpenses\LossOrAndExpense');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

}