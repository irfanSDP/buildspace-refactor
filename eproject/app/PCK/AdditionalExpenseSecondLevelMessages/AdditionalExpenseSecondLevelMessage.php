<?php namespace PCK\AdditionalExpenseSecondLevelMessages;

use Carbon\Carbon;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class AdditionalExpenseSecondLevelMessage extends Model implements DecisionTypes {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'ae_second_level_messages';

	public function additionalExpense()
	{
		return $this->belongsTo('PCK\AdditionalExpenses\AdditionalExpense');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

	public function getRequestedNewDeadlineAttribute($value)
	{
		return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
	}

	public function getGrantDifferentDeadlineAttribute($value)
	{
		return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
	}

}