<?php namespace PCK\AdditionalExpenseFourthLevelMessages;

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use PCK\ContractGroups\Types\Role;
use Illuminate\Database\Eloquent\Model;
use PCK\AdditionalExpenses\AdditionalExpense;

class AdditionalExpenseFourthLevelMessage extends Model implements DecisionTypes {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'ae_fourth_level_messages';

	protected static function boot()
	{
		parent::boot();

		static::saved(function($model)
		{
			$ae                 = $model->additionalExpense;
			$ae->amount_granted = 0;
			$status              = AdditionalExpense::PENDING;

			if ( $model->decision and $model->type == Role::INSTRUCTION_ISSUER )
			{
				switch($model->decision)
				{
					case self::GRANT:
					case self::GRANT_DIFF_AMOUNT:
					$ae->amount_granted = $model->grant_different_amount;
					$status             = AdditionalExpense::GRANTED;
						break;

					case self::REJECT:
						$status = AdditionalExpense::REJECTED;
						break;

					default:
						throw new \InvalidArgumentException('Invalid Decision');
				}
			}

			$ae->status = $status;

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