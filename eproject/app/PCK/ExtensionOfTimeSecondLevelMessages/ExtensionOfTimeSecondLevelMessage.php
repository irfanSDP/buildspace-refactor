<?php namespace PCK\ExtensionOfTimeSecondLevelMessages;

use Carbon\Carbon;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class ExtensionOfTimeSecondLevelMessage extends Model implements DecisionTypes {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'eot_second_level_messages';

	public function extensionOfTime()
	{
		return $this->belongsTo('PCK\ExtensionOfTimes\ExtensionOfTime');
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