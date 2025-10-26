<?php namespace PCK\ExtensionOfTimeContractorConfirmDelays;

use Carbon\Carbon;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class ExtensionOfTimeContractorConfirmDelay extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'eot_contractor_confirm_delays';

	public function extensionOfTime()
	{
		return $this->belongsTo('PCK\ExtensionOfTimes\ExtensionOfTime');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

	public function getDateOnWhichDelayIsOverAttribute($value)
	{
		return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
	}

	public function getDeadlineToSubmitFinalEotClaimAttribute($value)
	{
		return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
	}

}