<?php namespace PCK\LossOrAndExpenseThirdLevelMessages; 

use Carbon\Carbon;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class LossOrAndExpenseThirdLevelMessage extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'loe_third_level_messages';

	public function lossOrAndExpense()
	{
		return $this->belongsTo('PCK\LossOrAndExpenses\LossOrAndExpense');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

	public function getDeadlineToComplyWithAttribute($value)
	{
		return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
	}

}