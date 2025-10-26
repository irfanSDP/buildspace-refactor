<?php namespace PCK\LossOrAndExpenseContractorConfirmDelays; 

use Carbon\Carbon;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class LossOrAndExpenseContractorConfirmDelay extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'loe_contractor_confirm_delays';

	public function lossOrAndExpense()
	{
		return $this->belongsTo('PCK\LossOrAndExpenses\LossOrAndExpense');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

	public function getDateOnWhichDelayIsOverAttribute($value)
	{
		return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
	}

	public function getDeadlineToSubmitFinalClaimAttribute($value)
	{
		return Carbon::parse($value)->format(\Config::get('dates.submission_date_formatting'));
	}

}