<?php namespace PCK\TenderFormVerifierLogs;

use Carbon\Carbon;
use Laracasts\Presenter\Presenter;

class TenderFormVerifierLogPresenter extends Presenter {

	public function log_text_format($styleDate = true)
	{
		$createdBy = $this->user;
		$updatedAt = Carbon::parse($this->tender->project->getProjectTimeZoneTime($this->updated_at))->format(\Config::get('dates.created_and_updated_at_formatting'));
		$text      = TenderFormVerifierLog::getTextForVerificationStatus($this->type);
		$remark    = $this->verifier_remark ? "Remark : ".$this->verifier_remark : null;

		switch ($this->type)
		{
			case TenderFormVerifierLog::IN_PROGRESS:
				$text = 'Rejected';
				break;

			case TenderFormVerifierLog::SUBMISSION:
				$text = 'Approved';
				break;

			case TenderFormVerifierLog::NEED_VALIDATION:
				$text = 'Request Verification';
				break;
        }
        
        $date = $styleDate ? "<span class=\"dateSubmitted\">{$updatedAt}</span>" : $updatedAt;

		return "{$text} by {$createdBy->name} at {$date}. {$remark}";
	}
}