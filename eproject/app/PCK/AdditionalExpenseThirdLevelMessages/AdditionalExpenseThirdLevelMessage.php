<?php namespace PCK\AdditionalExpenseThirdLevelMessages;

use Carbon\Carbon;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class AdditionalExpenseThirdLevelMessage extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'ae_third_level_messages';

	public function additionalExpense()
	{
		return $this->belongsTo('PCK\AdditionalExpenses\AdditionalExpense');
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