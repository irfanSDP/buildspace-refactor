<?php namespace PCK\AdditionalExpenseContractorConfirmDelays;

use Carbon\Carbon;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class AdditionalExpenseContractorConfirmDelay extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $table = 'ae_contractor_confirm_delays';

	public function additionalExpense()
	{
		return $this->belongsTo('PCK\AdditionalExpenses\AdditionalExpense');
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