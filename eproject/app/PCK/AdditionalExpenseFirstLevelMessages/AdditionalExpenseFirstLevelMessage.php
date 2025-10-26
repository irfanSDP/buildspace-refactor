<?php namespace PCK\AdditionalExpenseFirstLevelMessages;

use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class AdditionalExpenseFirstLevelMessage extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'ae_first_level_messages';

	public function additionalExpense()
	{
		return $this->belongsTo('PCK\AdditionalExpenses\AdditionalExpense');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

}