<?php namespace PCK\Tenders;

use Carbon\Carbon;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use Illuminate\Support\Facades\DB;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Tenders\Tender;

trait OpenTenderTrait {

    public function allOpenTenderVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_user_verifier_open_tender', 'tender_id');
    }

    public function openTenderVerifiersApproved()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_user_verifier_open_tender', 'tender_id')
            ->wherePivot('status', '=', FormLevelStatus::USER_VERIFICATION_CONFIRMED);
    }

    public function openTenderVerifiers()
    {
        return $this->belongsToMany('PCK\Users\User', 'tender_user_verifier_open_tender', 'tender_id')
            ->wherePivot('status', '=', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
            ->withTimestamps();
    }

    public function openTenderVerifierLogs()
    {
        return $this->hasMany('PCK\OpenTenderVerifierLogs\OpenTenderVerifierLog')
            ->orderBy('id', 'ASC');
    }

    public function openTenderAwardRecommendtion()
    {
        return $this->hasOne('PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendation');
    }

    public function openTenderIsBeingValidated()
    {
        return $this->open_tender_verification_status === FormLevelStatus::NEED_VALIDATION;
    }

    public function openTenderIsSubmitted()
    {
        return $this->open_tender_verification_status === FormLevelStatus::SUBMISSION;
    }

    public function openTenderStillInProgress()
    {
        return $this->open_tender_verification_status === FormLevelStatus::IN_PROGRESS;
    }

    public function technicalEvaluationIsBeingValidated()
    {
        return $this->technical_evaluation_verification_status === FormLevelStatus::NEED_VALIDATION;
    }

    public function technicalEvaluationIsSubmitted()
    {
        return $this->technical_evaluation_verification_status === FormLevelStatus::SUBMISSION;
    }

    public function technicalEvaluationStillInProgress()
    {
        return $this->technical_evaluation_verification_status === FormLevelStatus::IN_PROGRESS;
    }

    public function isTenderOpen()
    {
        return $this->open_tender_status === self::OPEN_TENDER_STATUS_OPENED;
    }

    // is the current tender closed
    public function isTenderClosed()
    {
        $now = new Carbon();

        if (is_null($this->tender_closing_date)) {
            return false;
        }

        $closingDate = Carbon::createFromFormat(\Config::get('dates.created_and_updated_at_formatting'), $this->tender_closing_date);

        if( $now->gte($closingDate) ) return true;

        return false;
    }

    // if (is_null($this->tender_closing_date)) {
    //     return false;
    // }
    public function getOpenTenderStatusTextAttribute()
    {
        $text = null;

        switch($this->open_tender_status)
        {
            case self::OPEN_TENDER_STATUS_NOT_YET_OPEN:
                $text = self::OPEN_TENDER_STATUS_NOT_YET_OPEN_TEXT;
                break;

            case self::OPEN_TENDER_STATUS_OPENED:
                $text = self::OPEN_TENDER_STATUS_OPENED_TEXT;
                break;
        }

        return $text;
    }

    public function getOpenTenderVerifyingStatusAttribute()
    {
        if( $this->openTenderStillInProgress() ) return 'Assign';

        return 'Verifying';
    }

    public function getTechnicalEvaluationVerifyingStatusAttribute()
    {
        if( $this->technicalEvaluationStillInProgress() ) return 'Assign';

        return 'Verifying';
    }

    public function getPendingOpenTendersByUser(User $user, Project $project = null) {
        $listOfProjects = [];

        if($project)
        {
            $latestTender = $project->latestTender;

            foreach($latestTender->openTenderVerifiers as $verifier)
            {
                if(($verifier->id === $user->id) && $latestTender->openTenderIsBeingValidated())
                {
                    array_push($listOfProjects, [
                        'project_reference'        => $project->reference,
                        'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                        'project_id'               => $project->id,
                        'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                        'company_id'               => $project->business_unit_id,
                        'project_title'            => $project->title,
                        'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                        'module'                   => Tender::OPEN_TENDER_MODULE_NAME,
                        'days_pending'             => $this->getDaysPending($latestTender->id, $user->id),
                        'tender_id'                => $latestTender->id,
                        'route'                    => route('projects.openTender.accessToVerifierDecisionForm', array('projectId' => $project->id, 'tenderId' => $latestTender->id))
                    ]);
                }
            }
        }
        else
        {
            foreach($user->openTenders as $tender)
            {
                $project = $tender->project;

                if($project && $tender->openTenderIsBeingValidated())
                {
                    array_push($listOfProjects, [
                        'project_reference'        => $project->reference,
                        'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                        'project_id'               => $project->id,
                        'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                        'company_id'               => $project->business_unit_id,
                        'project_title'            => $project->title,
                        'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                        'module'                   => Tender::OPEN_TENDER_MODULE_NAME,
                        'days_pending'             => $this->getDaysPending($tender->id, $user->id),
                        'tender_id'                => $tender->id,
                        'route'                    => route('projects.openTender.accessToVerifierDecisionForm', array('projectId' => $project->id, 'tenderId' => $tender->id))
                    ]);
                }
            }
        }

        return $listOfProjects;
    }

    private function getDaysPending($tender_id, $user_id)
    {
        $updatedDate = DB::table('tender_user_verifier_open_tender')
                        ->select('updated_at')
                        ->where('tender_id', $tender_id)
                        ->where('user_id', $user_id)
                        ->where('status', FormLevelStatus::USER_VERIFICATION_IN_PROGRESS)
                        ->orderBy('id', 'ASC')
                        ->first();

        $now  = Carbon::now();
        $then = Carbon::parse($updatedDate->updated_at);

        return $then->diffInDays($now);
    }
}