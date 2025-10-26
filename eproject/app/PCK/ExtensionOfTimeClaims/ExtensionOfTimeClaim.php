<?php namespace PCK\ExtensionOfTimeClaims;

use PCK\Projects\Project;
use PCK\Base\ModuleAttachmentTrait;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class ExtensionOfTimeClaim extends Model {

	use TimestampFormatterTrait, ModuleAttachmentTrait;

	protected $appends = array( 'deadline_to_request_further_particulars', 'deadline_to_process_eot_application' );

	protected static function boot()
	{
		parent::boot();

		static::saved(function ($model)
		{
			$eot               = $model->extensionOfTime;
			$eot->days_claimed = $model->days_claimed;

			$eot->save();
		});
	}

	public function extensionOfTime()
	{
		return $this->belongsTo('PCK\ExtensionOfTimes\ExtensionOfTime');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

	public function getDeadlineToRequestFurtherParticularsAttribute()
	{
		return self::calculateDeadlineToSubmitNoticeToClaim($this->extensionOfTime->project, $this->created_at, $this->extensionOfTime->project->pam2006Detail->deadline_architect_request_info_from_contractor_eot_claim);
	}

	public function getDeadlineToProcessEotApplicationAttribute()
	{
		// multiply by 7 due to week's input
		return self::calculateDeadlineToSubmitNoticeToClaim($this->extensionOfTime->project, $this->created_at, $this->extensionOfTime->project->pam2006Detail->deadline_architect_decide_on_contractor_eot_claim * 7);
	}

	public static function calculateDeadlineToSubmitNoticeToClaim(Project $project, $startDate, $claimDuration)
	{
		$calendarRepo = \App::make('PCK\Calendars\CalendarRepository');

		return $calendarRepo->calculateFinalDate($project, $startDate, $claimDuration);
	}

}