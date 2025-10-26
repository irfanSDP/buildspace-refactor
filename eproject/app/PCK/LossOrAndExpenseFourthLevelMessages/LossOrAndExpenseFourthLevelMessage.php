<?php namespace PCK\LossOrAndExpenseFourthLevelMessages; 

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use PCK\ContractGroups\Types\Role;
use Illuminate\Database\Eloquent\Model;
use PCK\LossOrAndExpenses\LossOrAndExpense;

class LossOrAndExpenseFourthLevelMessage extends Model implements DecisionTypes {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'loe_fourth_level_messages';

	protected static function boot()
	{
		parent::boot();

		static::saved(function($model)
		{
			$loe                 = $model->lossOrAndExpense;
			$loe->amount_granted = 0;
			$status              = LossOrAndExpense::PENDING;

			if ( $model->decision and $model->type == Role::INSTRUCTION_ISSUER )
			{
				switch($model->decision)
				{
					case LossOrAndExpenseFourthLevelMessage::GRANT:
					case LossOrAndExpenseFourthLevelMessage::GRANT_DIFF_AMOUNT:
						$loe->amount_granted = $model->grant_different_amount;
						$status = LossOrAndExpense::GRANTED;
						break;

					case LossOrAndExpenseFourthLevelMessage::REJECT:
						$status = LossOrAndExpense::REJECTED;
						break;

					default:
						throw new \InvalidArgumentException('Invalid Decision');
				}
			}

			$loe->status = $status;

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