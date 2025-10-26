<?php namespace PCK\OpenTenderAwardRecommendation;

use Illuminate\Database\Eloquent\Model;
use PCK\Helpers\Mailer;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationStatus;
use PCK\Users\User;

class OpenTenderAwardRecommendation extends Model implements Verifiable {

	protected $table = 'open_tender_award_recommendation';

	const AWARD_RECOMMENDATION_MODULE_NAME = 'Award Recommendation';

	public function tender()
	{
		return $this->belongsTo('PCK\Tenders\Tender');
	}

	public function editLogs()
	{
		return $this->hasMany('PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationReportEditLog')->orderBy('id', 'asc');
	}

	public function createdBy()
	{
	    return $this->belongsTo('PCK\Users\User', 'created_by');
	}

	public function submitter()
	{
		return $this->belongsTo('PCK\Users\User', 'submitted_for_verification_by');
	}

	public function isEditable()
	{
		return ($this->status == OpenTenderAwardRecommendationStatus::EDITABLE);
	}

	public function isPendingForApproval()
	{
		return ($this->status == OpenTenderAwardRecommendationStatus::SUBMITTED_FOR_APPROVAL);
	}

	public function isApproved()
	{
		return ($this->status == OpenTenderAwardRecommendationStatus::APPROVED);
	}

	public function getOnApprovedView() {
		return 'open_tender_award_recommendation.award_recommendation_approved';
	}

	public function getOnRejectedView() {
		return 'open_tender_award_recommendation.rejected';
	}

	public function getOnPendingView() {
		return 'open_tender_award_recommendation.pending_verification';
	}

	public function getRoute()
	{
		$receiver = Verifier::getCurrentVerifier($this);

		if(is_null($receiver)) return route('open_tender.award_recommendation.report.show', [$this->tender->project->id, $this->tender->id]);

        if($receiver->getAssignedCompany($this->tender->project))
        {
            return route('open_tender.award_recommendation.report.show', [$this->tender->project->id, $this->tender->id]);
        }
        else if($receiver->isTopManagementVerifier())
        {
            return route('topManagementVerifiers.open_tender.award_recommendation.report.show', [$this->tender->project->id, $this->tender->id]);
        }

        return null;
	}

	public function getViewData($locale)
	{
		return [
			'senderName'			=> \Confide::user()->name,
			'project_title' 		=> $this->tender->project->title,
			'current_tender_name'	=> $this->tender->getCurrentTenderNameByLocale($locale),
			'toRoute'				=> $this->getRoute(),
			'recipientLocale'		=> $locale,
		];
	}

	public function getOnApprovedNotifyList() {
		return $this->getEditorsOfProject($this->tender->project);
	}

	public function getOnRejectedNotifyList()
    {
        $users = array();

		$submitter = User::find($this->submitted_for_verification_by);
		
		array_push($users, $submitter);

		$verifierRecords = Verifier::where('object_id', $this->id)
							->where('object_type', get_class($this))
							->where('approved', true)
							->orderBy('sequence_number', 'ASC')
							->get();

		foreach($verifierRecords as $record)
		{
			$verifier = User::find($record->verifier_id);

			if($verifier->stillInSameAssignedCompany($this->tender->project, $this->created_at))
			{
				array_push($users, $verifier);
			}
		}

        return $users;
	}

	public function getOnApprovedFunction() {
		return function() {
			$this->status = OpenTenderAwardRecommendationStatus::APPROVED;
			$this->save();
		};
	}

	public function getOnRejectedFunction() {
		return function() {
			$this->status = OpenTenderAwardRecommendationStatus::EDITABLE;
			$this->save();
		};
	}

    public function onReview()
    {
        $sender       	 = User::find($this->submitted_for_verification_by);
        $senderName   	 = Verifier::getLog($this)->last()->verifier->name;
		$projectTitle 	 = $this->tender->project->title;
		$recipientLocale = $sender->settings->language->code;

        if( Verifier::isBeingVerified($this) )
        {
            $mailer = new Mailer(trans('email.eProjectNotification'), 'notifications.email.open_tender_award_recommendation.approved', array(
                'senderName'      	  => $senderName,
				'project_title'       => $projectTitle,
				'recipientLocale' 	  => $recipientLocale,
				'current_tender_name' => $this->tender->getCurrentTenderNameByLocale($recipientLocale),
            ));

            $mailer->setRecipients(array( $sender ));
            $mailer->send();
        }
    }

	private function getEditorsOfProject($project) {
		$projectEditors = array();

        foreach($project->getProjectEditors() as $editor) {
            array_push($projectEditors, $editor->user);
        }

		return $projectEditors;
	}

	public function getEmailSubject($locale)
	{
		return trans('openTenderAwardRecommendation.awardRecommendation', [], 'messages', $locale);
	}

	public function getSubmitterId()
    {
        return $this->submitted_for_verification_by;
    }

    public function getModuleName()
    {
    	return trans('modules.awardRecommendation');
    }

	public function getTopManagementVerifierBackRoute()
	{
		return \Redirect::route('home.index');
	}
}