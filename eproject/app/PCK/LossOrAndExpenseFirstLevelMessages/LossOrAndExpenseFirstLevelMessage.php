<?php namespace PCK\LossOrAndExpenseFirstLevelMessages; 

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class LossOrAndExpenseFirstLevelMessage extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'loe_first_level_messages';

	public function lossOrAndExpense()
	{
		return $this->belongsTo('PCK\LossOrAndExpenses\LossOrAndExpense');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

}